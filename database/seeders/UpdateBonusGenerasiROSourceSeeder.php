<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserPin;

class UpdateBonusGenerasiROSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk update deskripsi Bonus Generasi dari RO
     * Menambahkan informasi sumber RO (AUTORO atau AUTOMAINTAIN)
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai update deskripsi Bonus Generasi dari RO...');
        
        // Ambil semua bonus generasi yang berasal dari RO
        // Pattern: "Bonus Generasi dari RO ..." atau "Bonus Generasi dari RO ... paket Gold (RO) ..."
        $bonuses = Bonus::where('type', 'Bonus Generasi')
            ->where(function($q) {
                $q->where('description', 'like', 'Bonus Generasi dari RO %')
                  ->orWhere('description', 'like', '%RO % paket %');
            })
            ->where('description', 'not like', '%AUTORO%')
            ->where('description', 'not like', '%AUTOMAINTAIN%')
            ->get();
        
        $this->command->info("Ditemukan {$bonuses->count()} bonus generasi dari RO yang perlu diupdate");
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($bonuses as $bonus) {
            try {
                // Extract username dari deskripsi
                // Format: "Bonus Generasi dari RO {username} paket ..."
                // atau "Bonus Generasi dari RO {username}."
                if (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+)/', $bonus->description, $matches)) {
                    $roUsername = $matches[1];
                    
                    // Cari user yang membuat RO
                    $roUser = User::where('username', $roUsername)->first();
                    
                    if (!$roUser) {
                        $skipped++;
                        $this->command->warn("  ⚠ User {$roUsername} tidak ditemukan untuk bonus ID {$bonus->id}");
                        continue;
                    }
                    
                    // Cari RO yang dibuat sekitar waktu bonus
                    $roPins = $roUser->userPins()
                        ->where('is_ro', true)
                        ->whereBetween('created_at', [
                            Carbon::parse($bonus->created_at)->subHours(1),
                            Carbon::parse($bonus->created_at)->addHours(1)
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    if ($roPins->isEmpty()) {
                        $skipped++;
                        $this->command->warn("  ⚠ RO tidak ditemukan untuk bonus ID {$bonus->id} (user: {$roUsername})");
                        continue;
                    }
                    
                    // Cek setiap RO untuk menentukan sumbernya
                    $roSource = null;
                    foreach ($roPins as $roPin) {
                        // Cek apakah ada automaintain record yang terkait
                        $automaintain = $roUser->automaintains()
                            ->where('type', 'D')
                            ->where('amount', 1700000)
                            ->whereBetween('created_at', [
                                Carbon::parse($roPin->created_at)->subMinutes(5),
                                Carbon::parse($roPin->created_at)->addMinutes(5)
                            ])
                            ->first();
                        
                        if ($automaintain) {
                            $roSource = 'AUTOMAINTAIN';
                            break;
                        } else {
                            $roSource = 'AUTORO';
                        }
                    }
                    
                    if (!$roSource) {
                        $skipped++;
                        $this->command->warn("  ⚠ Sumber RO tidak dapat ditentukan untuk bonus ID {$bonus->id}");
                        continue;
                    }
                    
                    // Update deskripsi
                    // Format lama: "Bonus Generasi dari RO {username} paket Gold (RO). ..."
                    // Format baru: "Bonus Generasi dari RO {username} paket Gold (RO) (AUTORO). ..."
                    // atau: "Bonus Generasi dari RO {username} paket Gold (RO) (AUTOMAINTAIN). ..."
                    
                    $oldDescription = $bonus->description;
                    
                    // Cek apakah sudah ada sumber di deskripsi
                    if (strpos($oldDescription, ' (AUTORO)') !== false || strpos($oldDescription, ' (AUTOMAINTAIN)') !== false) {
                        $skipped++;
                        continue;
                    }
                    
                    // Update deskripsi dengan berbagai pattern
                    $newDescription = $oldDescription;
                    
                    // Pattern 1: "Bonus Generasi dari RO {username} paket Gold (RO)."
                    // Menjadi: "Bonus Generasi dari RO {username} paket Gold (RO) ({roSource})."
                    if (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket Gold \(RO\)\./', $oldDescription)) {
                        $newDescription = preg_replace(
                            '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket Gold \(RO\)\./',
                            'Bonus Generasi dari RO $1 paket Gold (RO) (' . $roSource . ').',
                            $oldDescription
                        );
                    }
                    // Pattern 2: "Bonus Generasi dari RO {username} paket Gold (RO) ..." (dengan teks setelahnya)
                    elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket Gold \(RO\) /', $oldDescription)) {
                        $newDescription = preg_replace(
                            '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket Gold \(RO\) /',
                            'Bonus Generasi dari RO $1 paket Gold (RO) (' . $roSource . ') ',
                            $oldDescription
                        );
                    }
                    // Pattern 3: "Bonus Generasi dari RO {username}." (tanpa paket)
                    elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+)\./', $oldDescription)) {
                        $newDescription = preg_replace(
                            '/Bonus Generasi dari RO ([a-zA-Z0-9_]+)\./',
                            'Bonus Generasi dari RO $1 (' . $roSource . ').',
                            $oldDescription
                        );
                    }
                    // Pattern 4: "Bonus Generasi dari RO {username} paket ..." (dengan paket lain)
                    elseif (preg_match('/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket ([^\.]+)\./', $oldDescription)) {
                        $newDescription = preg_replace(
                            '/Bonus Generasi dari RO ([a-zA-Z0-9_]+) paket ([^\.]+)\./',
                            'Bonus Generasi dari RO $1 paket $2 (' . $roSource . ').',
                            $oldDescription
                        );
                    }
                    
                    if ($newDescription !== $oldDescription) {
                        $bonus->update(['description' => $newDescription]);
                        $updated++;
                        $this->command->info("  ✓ Bonus ID {$bonus->id} diupdate: {$roSource}");
                    } else {
                        $skipped++;
                        $this->command->warn("  ⚠ Pattern tidak match untuk bonus ID {$bonus->id}");
                    }
                } else {
                    $skipped++;
                    $this->command->warn("  ⚠ Username tidak dapat diextract dari deskripsi bonus ID {$bonus->id}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("  ✗ Error pada bonus ID {$bonus->id}: " . $e->getMessage());
            }
        }
        
        $this->command->info("\n=== Summary ===");
        $this->command->info("Updated: {$updated}");
        $this->command->info("Skipped: {$skipped}");
        $this->command->info("Errors: {$errors}");
        $this->command->info("\nSelesai!");
    }
}
