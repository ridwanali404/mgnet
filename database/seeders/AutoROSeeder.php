<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use App\Models\DailyPoin;
use App\Traits\Helper;

class AutoROSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk generate Auto RO yang terlewat dari data existing
     * Auto RO dipicu ketika user mencapai 170 PV dalam masa aktif
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai seeder Auto RO untuk data existing yang terlewat...');
        
        // Ambil semua user yang memiliki pin Gold, Gold Upgrade Platinum, atau Platinum
        $users = User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            });
        })
        ->whereNotNull('active_until')
        ->where('is_active', true)
        ->get();
        
        $this->command->info("Ditemukan {$users->count()} user dengan pin Gold/Platinum yang aktif");
        
        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            try {
                $processed++;
                
                // Cek apakah user punya premiumUserPin
                $userPin = $user->premiumUserPin;
                if (!$userPin || !$userPin->pin) {
                    $skipped++;
                    continue;
                }
                
                $pin = $userPin->pin;
                
                // Hanya untuk base pin: Gold, Gold Upgrade Platinum, atau Platinum
                if (!in_array($pin->name, ['Gold', 'Gold Upgrade Platinum', 'Platinum'])) {
                    $skipped++;
                    continue;
                }
                
                // Hitung total PV yang terkumpul dalam masa aktif
                // Periode aktif: dari activeFrom sampai activeUntil
                // Tapi untuk perhitungan PV, kita hitung semua transaksi yang dibuat sebelum active_until
                // (bukan hanya yang dalam periode aktif, karena transaksi bisa dibuat sebelum periode aktif dimulai)
                $activeFrom = Carbon::parse($user->active_until)->subDays($user->active_days_initial ?? 45);
                $activeUntil = Carbon::parse($user->active_until);
                
                // Hitung PV dari transaksi umum
                // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
                $transactionPoinTotal = Transaction::where('user_id', $user->id)
                    ->where('type', 'general')
                    ->where('poin', '>', 0)
                    ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                    ->where('created_at', '<=', $activeUntil)
                    ->sum('poin');
                
                // Hitung PV dari official transaction
                // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
                $officialPoin = OfficialTransaction::where('user_id', $user->id)
                    ->where('poin', '>', 0)
                    ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                    ->where('created_at', '<=', $activeUntil)
                    ->sum('poin');
                
                // Hitung PV dari daily poin dalam masa aktif
                $dailyPoinPV = $user->dailyPoins()
                    ->where('pv', '>', 0)
                    ->whereBetween('date', [$activeFrom->format('Y-m-d'), $activeUntil->format('Y-m-d')])
                    ->sum('pv');
                
                $totalPVInActive = $transactionPoinTotal + $officialPoin + $dailyPoinPV;
                
                // Cek apakah sudah mencapai 170 PV dalam masa aktif
                if ($totalPVInActive >= 170) {
                    // Hitung berapa kali Auto RO seharusnya sudah dibuat berdasarkan kelipatan 170 PV
                    // Setiap kelipatan 170 PV = 1 Auto RO (170, 340, 510, 680, dst)
                    $expectedAutoROCount = floor($totalPVInActive / 170);
                    
                    // Hitung berapa Auto RO yang sudah ada
                    // Hitung semua Auto RO yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
                    $existingAutoROCount = $user->userPins()
                        ->whereHas('pin', function($q) use ($pin) {
                            $q->where('name', $pin->name);
                        })
                        ->where('is_ro', true)
                        ->where('is_used', true)
                        ->where('created_at', '<=', $activeUntil)
                        ->count();
                    
                    // Jika masih ada Auto RO yang belum dibuat, buat yang terlewat
                    if ($expectedAutoROCount > $existingAutoROCount) {
                        $missingAutoROCount = $expectedAutoROCount - $existingAutoROCount;
                        
                        DB::beginTransaction();
                        try {
                            // Cari semua transaksi dan official transaction dalam masa aktif, urutkan berdasarkan tanggal
                            $allTransactions = collect();
                            
                            // Ambil transaksi umum
                            // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
                            $transactions = Transaction::where('user_id', $user->id)
                                ->where('type', 'general')
                                ->where('poin', '>', 0)
                                ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                                ->where('created_at', '<=', $activeUntil)
                                ->orderBy('created_at', 'asc')
                                ->get();
                            
                            foreach ($transactions as $t) {
                                $allTransactions->push([
                                    'date' => Carbon::parse($t->created_at),
                                    'poin' => $t->poin,
                                ]);
                            }
                            
                            // Ambil official transaction
                            // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
                            $officialTransactions = OfficialTransaction::where('user_id', $user->id)
                                ->where('poin', '>', 0)
                                ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                                ->where('created_at', '<=', $activeUntil)
                                ->orderBy('created_at', 'asc')
                                ->get();
                            
                            foreach ($officialTransactions as $ot) {
                                $allTransactions->push([
                                    'date' => Carbon::parse($ot->created_at),
                                    'poin' => $ot->poin,
                                ]);
                            }
                            
                            // Ambil daily poin
                            $dailyPoins = $user->dailyPoins()
                                ->where('pv', '>', 0)
                                ->whereBetween('date', [$activeFrom->format('Y-m-d'), $activeUntil->format('Y-m-d')])
                                ->orderBy('date', 'asc')
                                ->get();
                            
                            foreach ($dailyPoins as $dp) {
                                $allTransactions->push([
                                    'date' => Carbon::parse($dp->date),
                                    'poin' => $dp->pv,
                                ]);
                            }
                            
                            // Urutkan semua berdasarkan tanggal
                            $allTransactions = $allTransactions->sortBy('date');
                            
                            // Tentukan tanggal untuk setiap Auto RO yang terlewat
                            // Setiap Auto RO dibuat pada tanggal ketika PV mencapai kelipatan 170 (170, 340, 510, dst)
                            $accumulatedPV = 0;
                            $roDates = [];
                            
                            foreach ($allTransactions as $item) {
                                $accumulatedPV += $item['poin'];
                                
                                // Cek setiap kelipatan 170 PV
                                // Auto RO ke-1 saat PV >= 170, ke-2 saat PV >= 340, ke-3 saat PV >= 510, dst
                                $currentExpectedRO = floor($accumulatedPV / 170);
                                
                                // Jika ada Auto RO baru yang seharusnya dibuat
                                // Pastikan kita hanya menambahkan tanggal untuk Auto RO yang belum ada di array
                                while ($currentExpectedRO > count($roDates)) {
                                    // Simpan tanggal untuk Auto RO ini
                                    $roDates[] = $item['date'];
                                }
                            }
                            
                            // Jika masih kurang (misalnya karena ada gap atau daily poin), gunakan tanggal terakhir transaksi
                            while (count($roDates) < $expectedAutoROCount) {
                                $lastDate = $allTransactions->last() ? $allTransactions->last()['date'] : Carbon::now();
                                $roDates[] = $lastDate;
                            }
                            
                            // Cek Auto RO yang sudah ada dan perbaiki tanggalnya jika salah
                            $existingAutoROs = $user->userPins()
                                ->whereHas('pin', function($q) use ($pin) {
                                    $q->where('name', $pin->name);
                                })
                                ->where('is_ro', true)
                                ->where('is_used', true)
                                ->where('created_at', '<=', $activeUntil)
                                ->orderBy('created_at', 'asc')
                                ->get();
                            
                            // Update tanggal Auto RO yang sudah ada jika tidak sesuai
                            foreach ($existingAutoROs as $index => $existingRO) {
                                if (isset($roDates[$index])) {
                                    $expectedDate = $roDates[$index];
                                    // Jika tanggal berbeda lebih dari 1 menit, update
                                    if (abs($existingRO->created_at->diffInSeconds($expectedDate)) > 60) {
                                        $existingRO->update([
                                            'created_at' => $expectedDate,
                                            'updated_at' => $expectedDate,
                                        ]);
                                        $this->command->info("✓ Auto RO ID {$existingRO->id} tanggal diperbaiki untuk {$user->username} (dari {$existingRO->created_at->format('Y-m-d H:i:s')} ke {$expectedDate->format('Y-m-d H:i:s')})");
                                    }
                                }
                            }
                            
                            // Ambil hanya tanggal untuk Auto RO yang terlewat (mulai dari yang sudah ada)
                            $roDatesToCreate = array_slice($roDates, $existingAutoROCount, $missingAutoROCount);
                            
                            // Buat Auto RO yang terlewat
                            foreach ($roDatesToCreate as $roDate) {
                                $roUserPin = $user->userPins()->create([
                                    'buyer_id' => $user->id,
                                    'pin_id' => $pin->id,
                                    'code' => strtoupper(Str::random(6)),
                                    'name' => $pin->name,
                                    'price' => $pin->ro_price ?? ($pin->name == 'Platinum' ? 12750000 : 1700000),
                                    'level' => $pin->level,
                                    'is_used' => true,
                                    'is_ro' => true,
                                    'created_at' => $roDate,
                                    'updated_at' => $roDate,
                                ]);
                                
                                Helper::pinHistory($roUserPin);
                                Helper::upgrade($roUserPin); // Ini akan membuat bonus generasi ke atas
                                
                                $created++;
                                $this->command->info("✓ Auto RO dibuat untuk {$user->username} (PV: {$totalPVInActive}, Expected: {$expectedAutoROCount}, Existing: {$existingAutoROCount}, Tanggal: {$roDate->format('Y-m-d H:i:s')})");
                            }
                            
                            // Perpanjang masa aktif 45 hari dari Auto RO (jika belum diperpanjang)
                            if ($user->active_until) {
                                Helper::extendActiveStatus($user, 'auto_ro_170pv');
                            }
                            
                            DB::commit();
                            
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $errors++;
                            $this->command->error("✗ Error membuat Auto RO untuk {$user->username}: " . $e->getMessage());
                        }
                    } else {
                        $skipped++;
                        $this->command->line("  - {$user->username}: Auto RO sudah lengkap (PV: {$totalPVInActive}, Expected: {$expectedAutoROCount}, Existing: {$existingAutoROCount})");
                    }
                } else {
                    $skipped++;
                    // Uncomment untuk debug
                    // $this->command->line("  - {$user->username}: Belum mencapai 170 PV (PV: {$totalPVInActive})");
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("✗ Error memproses {$user->username}: " . $e->getMessage());
            }
            
            // Progress indicator
            if ($processed % 10 == 0) {
                $this->command->info("Progress: {$processed}/{$users->count()} user diproses...");
            }
        }
        
        $this->command->info("\n=== Ringkasan ===");
        $this->command->info("Total user diproses: {$processed}");
        $this->command->info("Auto RO dibuat: {$created}");
        $this->command->info("Dilewati: {$skipped}");
        $this->command->info("Error: {$errors}");
        $this->command->info("\nSeeder Auto RO selesai!");
    }
}

