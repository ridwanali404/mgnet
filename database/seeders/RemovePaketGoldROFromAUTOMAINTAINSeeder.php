<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;

class RemovePaketGoldROFromAUTOMAINTAINSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder untuk menghilangkan "paket Gold (RO)" dari deskripsi Bonus Generasi
     * yang memiliki sumber AUTOMAINTAIN
     * 
     * Format lama: "Bonus Generasi dari RO {username} paket Gold (RO) (AUTOMAINTAIN). ..."
     * Format baru: "Bonus Generasi dari RO {username} (AUTOMAINTAIN). ..."
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai update deskripsi Bonus Generasi dari RO AUTOMAINTAIN...');
        
        // Cari semua bonus generasi yang memiliki pattern "paket Gold (RO) (AUTOMAINTAIN)" atau variasi lainnya
        $bonuses = Bonus::where('type', 'Bonus Generasi')
            ->where(function($q) {
                $q->where('description', 'like', '%paket Gold (RO) (AUTOMAINTAIN)%')
                  ->orWhere('description', 'like', '%paket Gold (RO) AUTOMAINTAIN)%')
                  ->orWhere('description', 'like', '%baket Gold (RO) AUTOMAINTAIN)%'); // Handle typo "baket"
            })
            ->get();
        
        $this->command->info("Ditemukan {$bonuses->count()} bonus generasi dengan pattern 'paket Gold (RO) AUTOMAINTAIN' yang perlu diupdate");
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($bonuses as $bonus) {
            try {
                $originalDescription = $bonus->description;
                $newDescription = $originalDescription;
                
                // Pattern 1: "Bonus Generasi dari RO {username} paket Gold (RO) (AUTOMAINTAIN). ..."
                // Menjadi: "Bonus Generasi dari RO {username} (AUTOMAINTAIN). ..."
                if (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\)\./', $originalDescription)) {
                    $newDescription = preg_replace(
                        '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\)\./',
                        'Bonus Generasi dari RO $1 (AUTOMAINTAIN).',
                        $originalDescription
                    );
                }
                // Pattern 2: "Bonus Generasi dari RO {username} paket Gold (RO) AUTOMAINTAIN). ..." (tanpa kurung di AUTOMAINTAIN)
                elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) AUTOMAINTAIN\)\./', $originalDescription)) {
                    $newDescription = preg_replace(
                        '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) AUTOMAINTAIN\)\./',
                        'Bonus Generasi dari RO $1 (AUTOMAINTAIN).',
                        $originalDescription
                    );
                }
                // Pattern 3: "Bonus Generasi dari RO {username} paket Gold (RO) (AUTOMAINTAIN) ..." (dengan teks setelahnya)
                elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\) /', $originalDescription)) {
                    $newDescription = preg_replace(
                        '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\) /',
                        'Bonus Generasi dari RO $1 (AUTOMAINTAIN) ',
                        $originalDescription
                    );
                }
                // Pattern 4: "Bonus Generasi dari RO {username} paket Gold (RO) AUTOMAINTAIN) ..." (tanpa kurung di AUTOMAINTAIN, dengan teks setelahnya)
                elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) AUTOMAINTAIN\) /', $originalDescription)) {
                    $newDescription = preg_replace(
                        '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) AUTOMAINTAIN\) /',
                        'Bonus Generasi dari RO $1 (AUTOMAINTAIN) ',
                        $originalDescription
                    );
                }
                // Pattern 5: Untuk push-up mechanism "Bonus Generasi dari RO {username} paket Gold (RO) (AUTOMAINTAIN). Generasi ke-X (Push-up ..."
                elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\)\. Generasi ke-(\d+) \(Push-up/', $originalDescription)) {
                    $newDescription = preg_replace(
                        '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) (paket|baket) Gold \(RO\) \(AUTOMAINTAIN\)\. Generasi ke-(\d+) \(Push-up/',
                        'Bonus Generasi dari RO $1 (AUTOMAINTAIN). Generasi ke-$3 (Push-up',
                        $originalDescription
                    );
                }
                
                // Jika ada perubahan, update
                if ($newDescription !== $originalDescription) {
                    $bonus->update([
                        'description' => $newDescription
                    ]);
                    
                    $updated++;
                    $this->command->line("✓ Updated ID {$bonus->id}");
                    $this->command->line("  Old: {$originalDescription}");
                    $this->command->line("  New: {$newDescription}");
                } else {
                    $skipped++;
                    $this->command->warn("⚠ Skipped ID {$bonus->id} - Pattern tidak cocok");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("✗ Error pada ID {$bonus->id}: {$e->getMessage()}");
            }
        }
        
        $this->command->info("\n=== Ringkasan ===");
        $this->command->info("Total ditemukan: {$bonuses->count()}");
        $this->command->info("Diupdate: {$updated}");
        $this->command->info("Dilewati: {$skipped}");
        $this->command->info("Error: {$errors}");
        $this->command->info("\nSeeder selesai!");
    }
}

