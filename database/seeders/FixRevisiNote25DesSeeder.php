<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\UserPin;
use App\Models\User;
use App\Models\Pin;
use App\Traits\Helper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixRevisiNote25DesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder untuk fix revisi Note 25 Desember:
     * 1. Fix Komisi Monoleg dari AUTO RO - gunakan harga Gold (1.7 juta) bukan harga pin asli
     * 2. Tambahkan bonus monoleg level 2+ yang belum ada
     * 3. Catatan: Omset fix tidak perlu seeder karena dihitung on-the-fly
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== Memulai Fix Revisi Note 25 Desember ===');
        $this->command->info('');
        
        // 1. Fix Komisi Monoleg dari AUTO RO
        $this->fixKomisiMonolegRO();
        
        // 2. Tambahkan bonus monoleg level 2+ yang belum ada
        $this->addMissingMonolegLevel2Plus();
        
        $this->command->info('');
        $this->command->info('=== Fix Revisi Note 25 Desember Selesai ===');
    }
    
    /**
     * Fix Komisi Monoleg dari AUTO RO
     * Recalculate amount menggunakan harga Gold (1.7 juta) bukan harga pin asli
     */
    private function fixKomisiMonolegRO()
    {
        $this->command->info('1. Memperbaiki Komisi Monoleg dari AUTO RO...');
        
        // Ambil pin Gold untuk mendapatkan ro_price dan monoleg_percent
        $goldPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
        if (!$goldPin) {
            $this->command->warn('  ⚠️  Pin Gold tidak ditemukan, skip fix Komisi Monoleg RO');
            return;
        }
        
        $goldRoPrice = $goldPin->ro_price ?? 1700000;
        $goldMonolegPercent = $goldPin->monoleg_percent ?? 9;
        $correctAmount = round($goldRoPrice * $goldMonolegPercent / 100);
        
        // Cari semua Komisi Monoleg dari RO
        $bonuses = Bonus::where('type', 'Komisi Monoleg')
            ->where('description', 'like', '%RO Automaintain%')
            ->get();
        
        $this->command->info("  Ditemukan {$bonuses->count()} Komisi Monoleg dari RO");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($bonuses as $bonus) {
            // Hitung amount yang benar
            $newAmount = $correctAmount;
            
            // Jika amount sudah benar, skip
            if ($bonus->amount == $newAmount) {
                $skipped++;
                continue;
            }
            
            // Update amount
            $oldAmount = $bonus->amount;
            $bonus->update([
                'amount' => $newAmount
            ]);
            
            // Update automaintain jika ada
            $automaintain = DB::table('automaintains')
                ->where('user_id', $bonus->user_id)
                ->where('description', 'like', '%' . $bonus->description . '%')
                ->where('amount', $oldAmount)
                ->first();
            
            if ($automaintain) {
                DB::table('automaintains')
                    ->where('id', $automaintain->id)
                    ->update([
                        'amount' => $newAmount
                    ]);
            }
            
            $updated++;
            $this->command->line("  ✓ Updated Bonus ID {$bonus->id}: Rp " . number_format($oldAmount) . " → Rp " . number_format($newAmount));
        }
        
        $this->command->info("  Diupdate: {$updated}, Dilewati: {$skipped}");
        $this->command->info('');
    }
    
    /**
     * Tambahkan bonus monoleg level 2+ yang belum ada
     * Recalculate untuk semua user pin yang sudah ada
     */
    private function addMissingMonolegLevel2Plus()
    {
        $this->command->info('2. Menambahkan bonus monoleg level 2+ yang belum ada...');
        
        // Ambil semua user pin yang memenuhi syarat untuk bonus monoleg
        // Gold & Platinum (bukan BSM, bukan RO untuk yang normal, atau RO untuk yang RO)
        $userPins = UserPin::with(['user', 'pin'])
            ->whereHas('pin', function($q) {
                $q->whereIn('name', ['Gold', 'Platinum'])
                  ->where('type', 'premium');
            })
            ->get();
        
        $this->command->info("  Memproses {$userPins->count()} user pin...");
        
        $processed = 0;
        $added = 0;
        $skipped = 0;
        
        foreach ($userPins as $userPin) {
            $user = $userPin->user;
            $pin = $userPin->pin;
            
            // Skip jika user atau pin tidak ada
            if (!$user || !$pin) {
                $skipped++;
                continue;
            }
            
            $isRO = $userPin->is_ro ?? false;
            
            // Skip jika bukan Gold/Platinum atau BSM
            if (str_contains($pin->name, 'BSM')) {
                $skipped++;
                continue;
            }
            
            // Skip jika tidak ada monoleg_percent
            if (!$pin->monoleg_percent || $pin->monoleg_percent <= 0) {
                $skipped++;
                continue;
            }
            
            $firstUpline = $user->upline ?? $user->sponsor;
            if (!$firstUpline || $firstUpline->uplines()->whereHas('premiumUserPin')->count() < 1) {
                $skipped++;
                continue;
            }
            
            // Tentukan base price dan monoleg percent
            if ($isRO) {
                // Untuk RO, gunakan harga Gold
                $goldPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
                if (!$goldPin) {
                    $skipped++;
                    continue;
                }
                $basePrice = $goldPin->ro_price ?? 1700000;
                $monolegPercent = $goldPin->monoleg_percent ?? 9;
                $descriptionTemplate = 'Komisi Monoleg dari RO Automaintain oleh %s.';
            } else {
                // Untuk join/upgrade normal (level > 1 pakai member pertama langsung)
                $basePrice = $pin->price;
                $monolegPercent = $pin->monoleg_percent;
                $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                $descriptionTemplate = 'Bonus Monoleg 9%% dari ' . $action . ' %s paket ' . $pin->name . '.';
            }
            
            // Cek bonus monoleg yang sudah ada untuk user pin ini
            $existingBonuses = Bonus::where('type', 'Komisi Monoleg')
                ->where('description', 'like', '%' . $user->username . '%')
                ->whereDate('created_at', $userPin->created_at->format('Y-m-d'))
                ->get();
            
            // Hitung level maksimal yang sudah ada
            $maxExistingLevel = 0;
            foreach ($existingBonuses as $existingBonus) {
                if (preg_match('/Level (\d+)/', $existingBonus->description, $matches)) {
                    $level = (int)$matches[1];
                    if ($level > $maxExistingLevel) {
                        $maxExistingLevel = $level;
                    }
                } else {
                    // Jika tidak ada level, berarti level 1
                    $maxExistingLevel = max($maxExistingLevel, 1);
                }
            }
            
            // Jika sudah ada level 1, kita perlu cek apakah ada level 2+
            // Untuk sekarang, kita akan recalculate semua level dan skip yang sudah ada
            // Ini lebih aman untuk memastikan tidak ada yang terlewat
            
            // Recalculate bonus monoleg untuk semua level (jalur ikut upline)
            $level = 1;
            $currentSponsor = $firstUpline;
            $currentUserForMonoleg = $user;
            $descriptionUser = $user;
            $newBonusesAdded = 0;
            
            while ($currentSponsor && $level <= 100) {
                if ($currentSponsor->uplines()->whereHas('premiumUserPin')->count() >= 1) {
                    $monoleg = Helper::findMonolegRecursive($currentSponsor, $currentUserForMonoleg);
                    
                    if ($monoleg && $monoleg->premiumUserPin) {
                        $amount = round($basePrice * $monolegPercent / 100);
                        
                        if ($amount > 0) {
                            // Deskripsi selalu joiner; level = kedalaman dari penerima ke joiner di tree
                            $description = strpos($descriptionTemplate, '%s') !== false
                                ? sprintf($descriptionTemplate, $user->username)
                                : $descriptionTemplate;
                            $depthLevel = Helper::monolegDepthFromRecipientToJoiner($monoleg, $user);
                            $description .= ' (Level ' . $depthLevel . ')';
                            $pinOccurredAt = $userPin->updated_at ?? $userPin->created_at;
                            // Cek apakah bonus ini sudah ada
                            $bonusExists = Bonus::where('type', 'Komisi Monoleg')
                                ->where('user_id', $monoleg->id)
                                ->where('amount', $amount)
                                ->where('description', $description)
                                ->whereDate('created_at', $userPin->created_at->format('Y-m-d'))
                                ->exists();
                            
                            if (!$bonusExists) {
                                $bonus = $monoleg->bonuses()->create([
                                    'type' => 'Komisi Monoleg',
                                    'amount' => $amount,
                                    'description' => $description,
                                    'created_at' => $pinOccurredAt,
                                    'updated_at' => $pinOccurredAt,
                                ]);
                                
                                Helper::automaintain($monoleg, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                                
                                $newBonusesAdded++;
                            }
                        }
                        
                        // Level > 1: hanya jalur pertama (member pertama di bawah Leg) — joiner harus di bawah firstDirect
                        $firstDirect = Helper::getFirstDirectDownlineWithPremium($monoleg);
                        if ($firstDirect && $level >= 1) {
                            $joinerInFirstPath = ($user->id === $firstDirect->id)
                                || Helper::isUserUnderLeg($user, $firstDirect, $monoleg);
                            if (!$joinerInFirstPath) {
                                break;
                            }
                        }
                        $currentUserForMonoleg = $firstDirect ?? $monoleg;
                        $currentSponsor = $monoleg->upline;
                        $level++;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            
            if ($newBonusesAdded > 0) {
                $added += $newBonusesAdded;
                $this->command->line("  ✓ UserPin ID {$userPin->id} ({$user->username}): Ditambahkan {$newBonusesAdded} bonus monoleg level 2+");
            }
            
            $processed++;
            
            // Progress indicator setiap 100 user pin
            if ($processed % 100 == 0) {
                $this->command->info("  Progress: {$processed}/{$userPins->count()} user pin diproses...");
            }
        }
        
        $this->command->info("  Diproses: {$processed}, Ditambahkan: {$added} bonus baru, Dilewati: {$skipped}");
        $this->command->info('');
    }
}

