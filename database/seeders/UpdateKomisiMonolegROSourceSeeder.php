<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use Illuminate\Support\Facades\DB;

class UpdateKomisiMonolegROSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder untuk mengupdate deskripsi Komisi Monoleg dari RO
     * Mengubah "RO Platinum" atau "RO Gold" menjadi "RO Automaintain"
     * karena semua Komisi Monoleg dari RO berasal dari Automaintain
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai update deskripsi Komisi Monoleg dari RO...');
        
        // Cari semua Komisi Monoleg yang deskripsinya mengandung "RO Platinum" atau "RO Gold"
        // Pattern: "Komisi Monoleg dari RO Platinum oleh ..." atau "Komisi Monoleg dari RO Gold oleh ..."
        $bonuses = Bonus::where('type', 'Komisi Monoleg')
            ->where(function($query) {
                $query->where('description', 'like', '%RO Platinum%')
                      ->orWhere('description', 'like', '%RO Gold%');
            })
            ->get();
        
        $this->command->info("Ditemukan {$bonuses->count()} Komisi Monoleg yang perlu diupdate");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($bonuses as $bonus) {
            $originalDescription = $bonus->description;
            $newDescription = $originalDescription;
            
            // Ubah "RO Platinum" menjadi "RO Automaintain"
            // Pattern: "Komisi Monoleg dari RO Platinum oleh ..." → "Komisi Monoleg dari RO Automaintain oleh ..."
            $newDescription = preg_replace(
                '/RO Platinum/',
                'RO Automaintain',
                $newDescription
            );
            
            // Ubah "RO Gold" menjadi "RO Automaintain"
            // Pattern: "Komisi Monoleg dari RO Gold oleh ..." → "Komisi Monoleg dari RO Automaintain oleh ..."
            $newDescription = preg_replace(
                '/RO Gold/',
                'RO Automaintain',
                $newDescription
            );
            
            // Jika ada perubahan, update
            if ($newDescription !== $originalDescription) {
                $bonus->update([
                    'description' => $newDescription
                ]);
                
                $updated++;
                $this->command->line("✓ Updated ID {$bonus->id}: {$originalDescription} → {$newDescription}");
            } else {
                $skipped++;
            }
        }
        
        $this->command->info("\n=== Ringkasan ===");
        $this->command->info("Total ditemukan: {$bonuses->count()}");
        $this->command->info("Diupdate: {$updated}");
        $this->command->info("Dilewati: {$skipped}");
        $this->command->info("\nSeeder selesai!");
    }
}
