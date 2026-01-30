<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PowerPlusQualification;
use App\Models\DailyPoin;
use App\Models\User;
use Carbon\Carbon;
use App\Traits\Helper;

class ResetPowerPlusGroupOmzet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'powerplus:reset-group-omzet 
                            {--month= : Bulan yang akan di-reset (format: Y-m, contoh: 2026-01)}
                            {--user-id= : ID user tertentu yang akan di-reset}
                            {--all : Reset semua data omset grup powerplus}
                            {--recalculate : Hitung ulang data PowerPlus sebelum reset (jika belum ada)}
                            {--reset-daily-poins : Reset juga pp dan pr di daily_poins untuk bulan tersebut}
                            {--force : Skip konfirmasi dan langsung reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset omset poin grup Powerplus menjadi 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month');
        $userId = $this->option('user-id');
        $all = $this->option('all');

        // Validasi: minimal harus ada salah satu opsi
        if (!$month && !$userId && !$all) {
            $this->error('Pilih salah satu opsi: --month, --user-id, atau --all');
            $this->info('Contoh penggunaan:');
            $this->line('  php artisan powerplus:reset-group-omzet --all');
            $this->line('  php artisan powerplus:reset-group-omzet --month=2026-01');
            $this->line('  php artisan powerplus:reset-group-omzet --user-id=123');
            return 1;
        }

        // Jika opsi recalculate aktif dan ada bulan, hitung ulang data PowerPlus dulu
        if ($this->option('recalculate') && $month) {
            try {
                $this->info("Menghitung ulang data PowerPlus untuk bulan {$month}...");
                Helper::calculatePowerPlus($month);
                $this->info("✓ Data PowerPlus berhasil dihitung ulang");
            } catch (\Exception $e) {
                $this->error("Error saat menghitung ulang PowerPlus: " . $e->getMessage());
                return 1;
            }
        }

        // Query builder untuk qualifications
        $query = PowerPlusQualification::query();

        // Filter berdasarkan bulan jika ada
        if ($month) {
            try {
                $date = Carbon::createFromFormat('Y-m', $month);
                $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
                $this->info("Filter bulan: {$month} (dari {$startDate} sampai {$endDate})");
                
                // Tampilkan preview data yang akan di-reset
                $previewCount = $query->count();
                if ($previewCount > 0) {
                    $this->info("Ditemukan {$previewCount} qualification(s) untuk bulan ini.");
                }
            } catch (\Exception $e) {
                $this->error("Format bulan tidak valid. Gunakan format Y-m (contoh: 2026-01)");
                return 1;
            }
        }

        // Filter berdasarkan user_id jika ada
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("Filter user ID: {$userId}");
        }

        // Ambil semua qualifications yang akan di-reset
        $qualifications = $query->get();
        $total = $qualifications->count();

        if ($total == 0) {
            $this->warn('Tidak ada data yang ditemukan untuk di-reset.');
            
            // Tampilkan informasi tambahan untuk membantu debugging
            if ($month) {
                $allCount = PowerPlusQualification::count();
                $this->info("Total data PowerPlusQualification di database: {$allCount}");
                
                if ($allCount > 0) {
                    $sampleDates = PowerPlusQualification::select('date')
                        ->distinct()
                        ->orderBy('date', 'desc')
                        ->limit(5)
                        ->pluck('date')
                        ->toArray();
                    
                    $this->info("Contoh tanggal yang ada di database:");
                    foreach ($sampleDates as $sampleDate) {
                        $this->line("  - {$sampleDate} (" . Carbon::parse($sampleDate)->format('F Y') . ")");
                    }
                } else {
                    $this->newLine();
                    $this->info("Tips: Gunakan opsi --recalculate untuk menghitung data PowerPlus terlebih dahulu:");
                    $this->line("  php artisan powerplus:reset-group-omzet --month={$month} --recalculate");
                }
            }
            
            return 0;
        }

        // Konfirmasi sebelum reset (kecuali jika --force digunakan)
        $resetDailyPoins = $this->option('reset-daily-poins');
        $this->warn("PERINGATAN: Command ini akan mereset omset grup Powerplus menjadi 0!");
        $this->info("Jumlah data yang akan di-reset: {$total} qualification(s)");
        
        if ($resetDailyPoins && $month) {
            $this->warn("PERINGATAN TAMBAHAN: Opsi --reset-daily-poins aktif!");
            $this->warn("Ini akan mereset pp dan pr di daily_poins untuk bulan {$month}");
            $this->warn("Ini akan mempengaruhi semua perhitungan bonus yang menggunakan daily_poins!");
        }
        
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        } else {
            $this->warn('Mode --force aktif: Konfirmasi dilewati, langsung melakukan reset...');
        }

        // Reset data
        $this->info('Memulai proses reset...');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $failed = 0;

        foreach ($qualifications as $qualification) {
            try {
                // Ambil struktur leg yang ada (jika ada)
                $legOmzets = $qualification->leg_omzets;
                
                // Jika leg_omzets ada dan merupakan array, reset semua nilai menjadi 0
                // Tetap pertahankan struktur leg yang ada
                $resetLegOmzets = [];
                if (is_array($legOmzets) && !empty($legOmzets)) {
                    foreach ($legOmzets as $legName => $omzet) {
                        $resetLegOmzets[$legName] = 0;
                    }
                }

                // Update qualification dengan nilai yang di-reset
                $qualification->update([
                    'leg_omzets' => $resetLegOmzets,
                    'left_omzet' => 0,
                    'right_omzet' => 0,
                    'smaller_leg_omzet' => 0,
                    'is_qualified_15k' => false,
                    'is_qualified_30k' => false,
                    'bonus_amount' => 0,
                ]);

                $updated++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error pada qualification ID {$qualification->id}: " . $e->getMessage());
                $failed++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Reset daily_poins jika opsi aktif
        $dailyPoinsReset = 0;
        if ($resetDailyPoins && $month) {
            try {
                $this->info('Memulai reset daily_poins...');
                $date = Carbon::createFromFormat('Y-m', $month);
                $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
                
                // Ambil semua user yang memiliki qualification untuk bulan ini
                $userIds = $qualifications->pluck('user_id')->unique()->toArray();
                
                // Untuk setiap user, ambil semua uplines (leg) dan reset daily_poins mereka
                $legUserIds = [];
                foreach ($userIds as $uid) {
                    $user = User::find($uid);
                    if ($user) {
                        // Ambil semua uplines (leg) dari user ini
                        $uplines = $user->uplines()
                            ->whereHas('premiumUserPin')
                            ->orderBy('created_at', 'asc')
                            ->get();
                        
                        foreach ($uplines as $upline) {
                            // Reset daily_poins untuk setiap leg (upline)
                            $legUserIds[] = $upline->id;
                        }
                    }
                }
                
                // Reset pp dan pr di daily_poins untuk semua leg users di bulan tersebut
                $legUserIds = array_unique($legUserIds);
                if (!empty($legUserIds)) {
                    $dailyPoinsReset = DailyPoin::whereIn('user_id', $legUserIds)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->update([
                            'pp' => 0,
                            'pr' => 0,
                        ]);
                    
                    $this->info("✓ Berhasil mereset {$dailyPoinsReset} record daily_poins untuk " . count($legUserIds) . " leg user(s)");
                } else {
                    $this->warn("Tidak ada leg users yang ditemukan untuk di-reset");
                }
            } catch (\Exception $e) {
                $this->error("Error saat reset daily_poins: " . $e->getMessage());
            }
        }

        // Summary
        $this->info('==========================================');
        $this->info('SUMMARY RESET OMSET GRUP POWERPLUS');
        $this->info('==========================================');
        $this->info("Total qualification: {$total}");
        $this->info("Berhasil di-reset: {$updated}");
        if ($resetDailyPoins) {
            $this->info("Daily poins di-reset: {$dailyPoinsReset} record(s)");
        }
        if ($failed > 0) {
            $this->error("Gagal: {$failed}");
        }
        $this->info('==========================================');

        return 0;
    }
}
