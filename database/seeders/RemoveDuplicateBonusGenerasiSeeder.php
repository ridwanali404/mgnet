<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Bonus;
use App\Models\User;

class RemoveDuplicateBonusGenerasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk menghapus bonus generasi yang duplikat
     * Duplikat terjadi karena Auto RO dibuat duplikat (masalah query existingAutoROCount)
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai penghapusan bonus generasi duplikat...');
        
        // Ambil semua bonus generasi
        $allBonuses = Bonus::where('type', 'Bonus Generasi')
            ->orderBy('user_id')
            ->orderBy('description')
            ->orderBy('amount')
            ->orderBy('created_at')
            ->get();
        
        $this->command->info("Total bonus generasi: {$allBonuses->count()}");
        
        $duplicates = [];
        $processed = [];
        $deleted = 0;
        $errors = 0;
        
        // Group bonus berdasarkan user_id, description, dan amount
        $grouped = $allBonuses->groupBy(function($bonus) {
            return $bonus->user_id . '|' . $bonus->description . '|' . $bonus->amount;
        });
        
        foreach ($grouped as $key => $bonuses) {
            // Jika ada lebih dari 1 bonus dengan key yang sama, cek apakah duplikat
            if ($bonuses->count() > 1) {
                // Sort by created_at
                $sorted = $bonuses->sortBy('created_at');
                $first = $sorted->first();
                
                // Cek bonus lain yang dibuat dalam waktu yang sangat dekat (dalam 1 menit)
                $duplicateBonuses = $sorted->filter(function($bonus) use ($first) {
                    if ($bonus->id == $first->id) {
                        return false; // Skip yang pertama
                    }
                    
                    // Cek apakah dibuat dalam waktu yang sangat dekat (dalam 1 menit)
                    $timeDiff = abs($bonus->created_at->diffInSeconds($first->created_at));
                    return $timeDiff < 60; // Dalam 1 menit
                });
                
                if ($duplicateBonuses->count() > 0) {
                    $duplicates[] = [
                        'user_id' => $first->user_id,
                        'description' => $first->description,
                        'amount' => $first->amount,
                        'first_id' => $first->id,
                        'first_created' => $first->created_at,
                        'duplicate_ids' => $duplicateBonuses->pluck('id')->toArray(),
                        'duplicate_count' => $duplicateBonuses->count(),
                    ];
                }
            }
        }
        
        $this->command->info("Ditemukan " . count($duplicates) . " grup bonus duplikat");
        
        // Hapus bonus duplikat (sisakan yang pertama)
        foreach ($duplicates as $duplicate) {
            try {
                $user = User::find($duplicate['user_id']);
                $username = $user ? $user->username : 'Unknown';
                
                // Hapus bonus duplikat
                $deletedCount = Bonus::whereIn('id', $duplicate['duplicate_ids'])->delete();
                
                if ($deletedCount > 0) {
                    $deleted += $deletedCount;
                    $this->command->info("✓ Dihapus {$deletedCount} bonus duplikat untuk user {$username} (ID: {$duplicate['user_id']})");
                    $this->command->info("  Description: " . substr($duplicate['description'], 0, 80) . "...");
                    $this->command->info("  Amount: Rp " . number_format($duplicate['amount'], 0, ',', '.'));
                    $this->command->info("  Bonus pertama (disimpan): ID {$duplicate['first_id']}, Created: {$duplicate['first_created']}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("✗ Error menghapus bonus duplikat: " . $e->getMessage());
            }
        }
        
        $this->command->info("\n=== Summary ===");
        $this->command->info("Grup duplikat ditemukan: " . count($duplicates));
        $this->command->info("Bonus yang dihapus: {$deleted}");
        $this->command->info("Errors: {$errors}");
        $this->command->info("\nSelesai!");
    }
}
