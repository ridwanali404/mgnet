<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PowerPlusQualification;
use App\Traits\Helper;
use Carbon\Carbon;

class PowerPlusLegOmzetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan mengupdate semua existing PowerPlusQualification
     * dengan menghitung leg_omzets berdasarkan data yang ada
     */
    public function run(): void
    {
        $this->command->info('Memulai update leg_omzets untuk existing PowerPlusQualification...');
        
        // Ambil semua qualification yang belum punya leg_omzets atau leg_omzets null/kosong
        $qualifications = PowerPlusQualification::where(function($query) {
                $query->whereNull('leg_omzets')
                      ->orWhere('leg_omzets', '[]')
                      ->orWhere('leg_omzets', '');
            })
            ->get();
        
        $total = $qualifications->count();
        $this->command->info("Ditemukan {$total} qualification yang perlu diupdate.");
        
        if ($total == 0) {
            $this->command->info('Tidak ada data yang perlu diupdate.');
            return;
        }
        
        $updated = 0;
        $failed = 0;
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();
        
        foreach ($qualifications as $qualification) {
            try {
                // Ambil bulan dari date qualification
                $month = Carbon::parse($qualification->date)->format('Y-m');
                
                // Hitung leg_omzets untuk bulan tersebut
                $legOmzets = Helper::calculateAllLegOmzetMonthly($qualification->user, $month);
                
                // Logika qualification baru: minimal 2 grup dengan 15k-30k dan minimal 2 grup >=30k
                $qualified15kGroups = 0; // Grup dengan omset 15.000 - 29.999
                $qualified30kGroups = 0; // Grup dengan omset >= 30.000
                
                foreach ($legOmzets as $legName => $omzet) {
                    if ($omzet >= 30000) {
                        $qualified30kGroups++;
                    } elseif ($omzet >= 15000) {
                        $qualified15kGroups++;
                    }
                }
                
                $isQualified15k = $qualified15kGroups >= 2; // Minimal 2 grup dengan 15k-30k
                $isQualified30k = $qualified30kGroups >= 2; // Minimal 2 grup dengan >=30k
                
                // Untuk backward compatibility, update left_omzet dan right_omzet juga
                $leftOmzet = $legOmzets['Leg 1'] ?? 0;
                $rightOmzet = isset($legOmzets['Leg 2']) ? $legOmzets['Leg 2'] : 0;
                $smallerLegOmzet = min($leftOmzet, $rightOmzet);
                
                // Update qualification
                $qualification->update([
                    'leg_omzets' => $legOmzets,
                    'left_omzet' => $leftOmzet,
                    'right_omzet' => $rightOmzet,
                    'smaller_leg_omzet' => $smallerLegOmzet,
                    'is_qualified_15k' => $isQualified15k,
                    'is_qualified_30k' => $isQualified30k,
                ]);
                
                $updated++;
            } catch (\Exception $e) {
                $this->command->error("\nError pada qualification ID {$qualification->id}: " . $e->getMessage());
                $failed++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Update selesai!");
        $this->command->info("Berhasil diupdate: {$updated}");
        $this->command->info("Gagal: {$failed}");
    }
}
