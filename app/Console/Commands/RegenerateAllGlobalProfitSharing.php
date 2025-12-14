<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\Helper;
use App\Models\GlobalProfitSharingDaily;
use App\Models\GlobalProfitSharingSaving;
use App\Models\UserPin;
use Carbon\Carbon;

class RegenerateAllGlobalProfitSharing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:regenerate-all {--from-date=} {--to-date=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate semua bonus Global Profit Sharing untuk semua hari dari awal data sampai H-1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $this->info("==========================================");
        $this->info("REGENERATE SEMUA BONUS GLOBAL PROFIT SHARING");
        $this->info("==========================================");
        $this->newLine();

        // Tentukan tanggal mulai dan akhir
        $fromDate = $this->option('from-date');
        $toDate = $this->option('to-date');
        $force = $this->option('force');

        if (!$fromDate) {
            // Ambil tanggal pertama dari UserPin atau GlobalProfitSharingDaily
            $firstUserPin = UserPin::orderBy('created_at', 'asc')->first();
            $firstGpsDaily = GlobalProfitSharingDaily::orderBy('date', 'asc')->first();
            
            if ($firstUserPin) {
                $fromDate = Carbon::parse($firstUserPin->created_at)->format('Y-m-d');
            } elseif ($firstGpsDaily) {
                $fromDate = $firstGpsDaily->date;
            } else {
                $fromDate = date('Y-m-d', strtotime('-30 days')); // Default 30 hari terakhir
            }
        }

        if (!$toDate) {
            // Default sampai H-1 (kemarin)
            $toDate = Carbon::yesterday()->format('Y-m-d');
        }

        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);

        if ($startDate->gt($endDate)) {
            $this->error("Tanggal mulai tidak boleh lebih besar dari tanggal akhir!");
            return 1;
        }

        $this->info("Periode: {$startDate->translatedFormat('d F Y')} - {$endDate->translatedFormat('d F Y')}");
        $this->info("Total hari: " . $startDate->diffInDays($endDate) + 1);
        $this->newLine();

        if (!$force && !$this->confirm('Apakah Anda yakin ingin regenerate semua GPS untuk periode ini? (Ini akan mengupdate GPS daily dan GPS saving)')) {
            $this->info("Dibatalkan.");
            return 0;
        }

        try {
            \DB::beginTransaction();

            $currentDate = $startDate->copy();
            $totalDays = $startDate->diffInDays($endDate) + 1;
            $processedDays = 0;
            $skippedDays = 0;
            $totalGpsAmount = 0;
            $totalDistributed = 0;

            $bar = $this->output->createProgressBar($totalDays);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% -- %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $bar->setMessage("Memproses {$dateStr}...");

                try {
                    // Hitung GPS untuk tanggal ini
                    Helper::calculateGlobalProfitSharing($dateStr);
                    
                    // Hitung statistik
                    $dailyGps = GlobalProfitSharingDaily::whereDate('date', $dateStr)->sum('amount');
                    if ($dailyGps > 0) {
                        $processedDays++;
                        $totalDistributed += $dailyGps;
                    } else {
                        $skippedDays++;
                    }

                    $bar->advance();
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Error pada tanggal {$dateStr}: " . $e->getMessage());
                    $skippedDays++;
                    $bar->advance();
                }

                $currentDate->addDay();
            }

            $bar->finish();
            $this->newLine();
            $this->newLine();

            // Hitung total GPS saving
            $totalWalletCashback = GlobalProfitSharingSaving::sum('wallet_cashback');
            $totalDailyAccumulation = GlobalProfitSharingSaving::sum('daily_accumulation');
            $totalUsers = GlobalProfitSharingSaving::where('wallet_cashback', '>', 0)->count();

            \DB::commit();

            $this->info("==========================================");
            $this->info("REGENERATE BERHASIL!");
            $this->info("==========================================");
            $this->info("Periode: {$startDate->translatedFormat('d F Y')} - {$endDate->translatedFormat('d F Y')}");
            $this->info("Total hari diproses: {$processedDays}");
            $this->info("Total hari dilewati: {$skippedDays}");
            $this->info("Total GPS yang dibagikan: Rp " . number_format($totalDistributed, 0, ',', '.'));
            $this->info("Total wallet cashback: Rp " . number_format($totalWalletCashback, 0, ',', '.'));
            $this->info("Total daily accumulation: Rp " . number_format($totalDailyAccumulation, 0, ',', '.'));
            $this->info("Total user dengan GPS: {$totalUsers}");
            $this->info("==========================================");

            return 0;
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
