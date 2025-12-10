<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserPin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateBonusDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan mengupdate deskripsi bonus yang sudah ada di database.
     * Mengubah "dari upgrade" menjadi "dari join" atau sebaliknya berdasarkan pin type.
     * 
     * Logika:
     * 1. Cari semua bonus yang deskripsinya mengandung "dari upgrade" atau "dari join"
     * 2. Extract username dari deskripsi (user yang melakukan upgrade/join)
     * 3. Cari UserPin user tersebut yang dibuat sekitar waktu bonus dibuat
     * 4. Cek pin type-nya
     * 5. Update deskripsi sesuai pin type
     * 
     * Cara menjalankan:
     * php artisan db:seed --class=UpdateBonusDescriptionSeeder
     */
    public function run(): void
    {
        $this->command->info('Memulai update deskripsi bonus...');
        
        // Bonus yang perlu dicek: Komisi Sponsor, Bonus Generasi, Komisi Monoleg
        $bonusTypes = ['Komisi Sponsor', 'Bonus Generasi', 'Komisi Monoleg'];
        
        $bonuses = Bonus::whereIn('type', $bonusTypes)
            ->whereNotNull('description')
            ->where(function($q) {
                $q->where('description', 'like', '%dari upgrade%')
                  ->orWhere('description', 'like', '%dari join%');
            })
            ->get();
        
        $this->command->info('Ditemukan ' . $bonuses->count() . ' bonus yang perlu dicek.');
        
        if ($bonuses->count() == 0) {
            $this->command->warn('Tidak ada bonus yang perlu diupdate. Seeder selesai.');
            return;
        }
        
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        $progressBar = $this->command->getOutput()->createProgressBar($bonuses->count());
        $progressBar->start();
        
        foreach ($bonuses as $bonus) {
            try {
                // Extract username dari deskripsi
                // Pattern: "dari upgrade/join [username] paket..."
                // atau "dari [username] paket..." (untuk Komisi Sponsor)
                $description = $bonus->description;
                $username = null;
                
                // Pattern 1: "dari upgrade [username]" atau "dari join [username]"
                if (preg_match('/dari\s+(?:upgrade|join)\s+([a-zA-Z0-9_]+)\s+paket/i', $description, $matches)) {
                    $username = $matches[1];
                }
                // Pattern 2: "dari [username] paket" (untuk Komisi Sponsor)
                elseif (preg_match('/dari\s+([a-zA-Z0-9_]+)\s+paket/i', $description, $matches)) {
                    $username = $matches[1];
                }
                // Pattern 3: "dari penggunaan pin ... oleh [username]"
                elseif (preg_match('/oleh\s+([a-zA-Z0-9_]+)/i', $description, $matches)) {
                    $username = $matches[1];
                }
                
                if (!$username) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }
                
                // Cari user berdasarkan username
                $targetUser = User::where('username', $username)->first();
                
                if (!$targetUser) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }
                
                // Cari UserPin user tersebut yang dibuat sekitar waktu bonus dibuat
                // Rentang waktu: 1 jam sebelum sampai 1 jam sesudah bonus dibuat
                $bonusTime = Carbon::parse($bonus->created_at);
                $startTime = $bonusTime->copy()->subHour();
                $endTime = $bonusTime->copy()->addHour();
                
                $userPin = UserPin::where('user_id', $targetUser->id)
                    ->whereBetween('created_at', [$startTime, $endTime])
                    ->with('pin')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                // Jika tidak ditemukan dalam rentang 1 jam, coba cari dalam rentang 24 jam
                if (!$userPin) {
                    $startTime = $bonusTime->copy()->subDay();
                    $endTime = $bonusTime->copy()->addDay();
                    
                    $userPin = UserPin::where('user_id', $targetUser->id)
                        ->whereBetween('created_at', [$startTime, $endTime])
                        ->with('pin')
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                
                if (!$userPin || !$userPin->pin) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }
                
                // Tentukan action yang benar berdasarkan pin type
                $correctAction = $userPin->pin->type == 'upgrade' ? 'upgrade' : 'join';
                
                // Cek apakah deskripsi sudah benar
                $hasUpgrade = stripos($description, 'dari upgrade') !== false;
                $hasJoin = stripos($description, 'dari join') !== false;
                
                $needsUpdate = false;
                $newDescription = $description;
                
                if ($correctAction == 'upgrade' && $hasJoin) {
                    // Seharusnya "upgrade" tapi pakai "join"
                    $newDescription = preg_replace('/dari\s+join\s+/i', 'dari upgrade ', $newDescription);
                    $needsUpdate = true;
                } elseif ($correctAction == 'join' && $hasUpgrade) {
                    // Seharusnya "join" tapi pakai "upgrade"
                    $newDescription = preg_replace('/dari\s+upgrade\s+/i', 'dari join ', $newDescription);
                    $needsUpdate = true;
                } elseif ($correctAction == 'join' && !$hasJoin && !$hasUpgrade) {
                    // Tidak ada "join" atau "upgrade", tambahkan "join"
                    // Pattern: "dari [username]" -> "dari join [username]"
                    $newDescription = preg_replace('/dari\s+(' . preg_quote($username, '/') . ')\s+paket/i', 'dari join $1 paket', $newDescription);
                    $needsUpdate = true;
                }
                
                if ($needsUpdate && $newDescription != $description) {
                    $bonus->update(['description' => $newDescription]);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("\nError pada bonus ID {$bonus->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Update deskripsi bonus selesai!');
        $this->command->info('Bonus yang diupdate: ' . $updatedCount);
        $this->command->info('Bonus yang dilewati: ' . $skippedCount);
        if ($errorCount > 0) {
            $this->command->warn('Error: ' . $errorCount);
        }
    }
}
