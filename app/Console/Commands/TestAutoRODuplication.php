<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use App\Traits\Helper;

class TestAutoRODuplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-ro-duplication {username} {--poin=170}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Auto RO untuk memastikan tidak ada duplikasi bonus generasi';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $poin = (int) $this->option('poin');
        
        $this->info("=== Test Auto RO Duplication untuk {$username} ===");
        $this->info("Poin yang akan ditambahkan: {$poin}");
        $this->newLine();
        
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            $this->error("User {$username} tidak ditemukan!");
            return 1;
        }
        
        // Cek kondisi awal
        $this->info("--- Kondisi Awal ---");
        $initialAutoROCount = $user->userPins()
            ->where('is_ro', true)
            ->where('is_used', true)
            ->count();
        
        $this->info("Auto RO awal: {$initialAutoROCount}");
        
        // Hitung PV awal
        $activeUntil = Carbon::parse($user->active_until);
        $initialTransactionPoin = Transaction::where('user_id', $user->id)
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('created_at', '<=', $activeUntil)
            ->sum('poin');
        
        $initialOfficialPoin = OfficialTransaction::where('user_id', $user->id)
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('created_at', '<=', $activeUntil)
            ->sum('poin');
        
        $initialTotalPV = $initialTransactionPoin + $initialOfficialPoin;
        $initialExpectedAutoRO = floor($initialTotalPV / 170);
        
        $this->info("PV awal: {$initialTotalPV}");
        $this->info("Expected Auto RO awal: {$initialExpectedAutoRO}");
        $this->newLine();
        
        // Simulasi: Buat transaksi dan trigger Auto RO
        DB::beginTransaction();
        try {
            // Simulasi 1: Buat transaksi (status: pending)
            $this->info("--- Simulasi 1: Buat Transaksi (status: pending) ---");
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'general',
                'poin' => $poin,
                'price' => $poin * 1000,
                'status' => 'pending',
            ]);
            $this->info("✓ Transaksi dibuat: ID {$transaction->id}, Poin: {$poin}");
            
            // Simulasi 2: Confirm transaksi (status: paid) - SEHARUSNYA trigger Auto RO 1x
            $this->info("--- Simulasi 2: Confirm Transaksi (status: paid) ---");
            $autoROBeforeConfirm = $user->userPins()
                ->where('is_ro', true)
                ->where('is_used', true)
                ->count();
            
            $this->info("Auto RO sebelum confirm: {$autoROBeforeConfirm}");
            
            // Update status menjadi 'paid' dan trigger Auto RO
            $transaction->update(['status' => 'paid']);
            $this->info("✓ Status diupdate menjadi 'paid'");
            
            // Panggil checkAndTriggerAutoROFromPV seperti di confirm()
            Helper::checkAndTriggerAutoROFromPV($user, $transaction->poin);
            
            $autoROAfterConfirm = $user->userPins()
                ->where('is_ro', true)
                ->where('is_used', true)
                ->count();
            
            $this->info("Auto RO setelah confirm: {$autoROAfterConfirm}");
            
            if ($autoROAfterConfirm > $autoROBeforeConfirm) {
                $newAutoROCount = $autoROAfterConfirm - $autoROBeforeConfirm;
                if ($newAutoROCount == 1) {
                    $this->info("✓ Auto RO dibuat 1x (BENAR)");
                } else {
                    $this->error("✗ ERROR: Auto RO dibuat {$newAutoROCount}x (seharusnya 1x)!");
                }
            } else {
                $this->warn("⚠ Auto RO tidak dibuat (mungkin belum mencapai 170 PV atau sudah ada)");
            }
            $this->newLine();
            
            // Simulasi 3: Received transaksi (status: received) - SEHARUSNYA TIDAK trigger Auto RO lagi
            $this->info("--- Simulasi 3: Received Transaksi (status: received) ---");
            $transaction->update(['status' => 'received']);
            $this->info("✓ Status diupdate menjadi 'received'");
            
            // JANGAN panggil checkAndTriggerAutoROFromPV di sini (sudah dipanggil di confirm)
            $autoROAfterReceived = $user->userPins()
                ->where('is_ro', true)
                ->where('is_used', true)
                ->count();
            
            $this->info("Auto RO setelah received: {$autoROAfterReceived}");
            
            if ($autoROAfterReceived > $autoROAfterConfirm) {
                $this->error("✗ ERROR: Auto RO dibuat lagi saat received (DUPLIKASI)!");
            } else {
                $this->info("✓ Auto RO tidak dibuat lagi saat received (BENAR)");
            }
            $this->newLine();
            
            // Simulasi 4: Panggil checkAndTriggerAutoROFromPV lagi (simulasi duplikasi) - SEHARUSNYA TIDAK membuat Auto RO baru
            $this->info("--- Simulasi 4: Panggil checkAndTriggerAutoROFromPV Lagi (Test Duplikasi) ---");
            $autoROBeforeSecondCall = $user->userPins()
                ->where('is_ro', true)
                ->where('is_used', true)
                ->count();
            
            $this->info("Auto RO sebelum panggilan kedua: {$autoROBeforeSecondCall}");
            
            // Panggil lagi (simulasi jika dipanggil 2x)
            Helper::checkAndTriggerAutoROFromPV($user, $transaction->poin);
            
            $autoROAfterSecondCall = $user->userPins()
                ->where('is_ro', true)
                ->where('is_used', true)
                ->count();
            
            $this->info("Auto RO setelah panggilan kedua: {$autoROAfterSecondCall}");
            
            if ($autoROAfterSecondCall > $autoROBeforeSecondCall) {
                $this->error("✗ ERROR: Auto RO dibuat lagi saat panggilan kedua (DUPLIKASI DITEMUKAN)!");
            } else {
                $this->info("✓ Auto RO tidak dibuat lagi (TIDAK ADA DUPLIKASI)");
            }
            $this->newLine();
            
            // Cek bonus generasi untuk upline
            $this->info("--- Cek Bonus Generasi untuk Upline ---");
            $sponsor = $user->sponsor;
            if ($sponsor) {
                // Ambil bonus generasi yang baru dibuat (dalam 1 menit terakhir)
                $recentBonuses = $sponsor->bonuses()
                    ->where('type', 'Bonus Generasi')
                    ->where('description', 'like', '%RO ' . $username . '%')
                    ->where('created_at', '>=', Carbon::now()->subMinute())
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $this->info("Bonus generasi untuk {$sponsor->username} (dari RO {$username}):");
                $this->info("Jumlah: {$recentBonuses->count()}");
                
                // Group by description dan amount untuk cek duplikasi
                $grouped = $recentBonuses->groupBy(function($bonus) {
                    return $bonus->description . '|' . $bonus->amount;
                });
                
                $duplicateFound = false;
                foreach ($grouped as $key => $bonuses) {
                    if ($bonuses->count() > 1) {
                        $duplicateFound = true;
                        $this->error("✗ DUPLIKASI DITEMUKAN:");
                        $this->error("  Description: " . substr($bonuses->first()->description, 0, 80) . "...");
                        $this->error("  Amount: " . number_format($bonuses->first()->amount));
                        $this->error("  Jumlah duplikat: {$bonuses->count()}");
                        foreach ($bonuses as $bonus) {
                            $this->error("    - ID: {$bonus->id}, Created: {$bonus->created_at}");
                        }
                    }
                }
                
                if (!$duplicateFound) {
                    $this->info("✓ Tidak ada duplikasi bonus generasi");
                    
                    // Tampilkan detail bonus
                    foreach ($recentBonuses as $bonus) {
                        $this->info("  - ID: {$bonus->id}, Amount: " . number_format($bonus->amount) . ", Created: {$bonus->created_at}");
                        $this->info("    Desc: " . substr($bonus->description, 0, 80) . "...");
                    }
                }
            } else {
                $this->warn("User tidak punya sponsor, tidak ada bonus generasi");
            }
            $this->newLine();
            
            // Rollback transaksi test
            DB::rollBack();
            $this->info("--- Test Selesai (Transaksi di-rollback) ---");
            
            // Summary
            $this->newLine();
            $this->info("=== Summary ===");
            if ($autoROAfterConfirm > $autoROBeforeConfirm && $autoROAfterConfirm - $autoROBeforeConfirm == 1) {
                $this->info("✓ Auto RO dibuat 1x saat confirm (BENAR)");
            } else {
                $this->error("✗ Auto RO tidak dibuat atau dibuat lebih dari 1x saat confirm");
            }
            
            if ($autoROAfterReceived == $autoROAfterConfirm) {
                $this->info("✓ Tidak ada duplikasi saat received (BENAR)");
            } else {
                $this->error("✗ Ada duplikasi saat received");
            }
            
            if ($autoROAfterSecondCall == $autoROBeforeSecondCall) {
                $this->info("✓ Tidak ada duplikasi saat panggilan kedua (BENAR)");
            } else {
                $this->error("✗ Ada duplikasi saat panggilan kedua");
            }
            
            if (!$duplicateFound) {
                $this->info("✓ Tidak ada duplikasi bonus generasi (BENAR)");
            } else {
                $this->error("✗ Ada duplikasi bonus generasi");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}

