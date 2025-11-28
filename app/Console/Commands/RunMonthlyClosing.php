<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MonthlyClosingController;
use App\Traits\Helper;
use App\Models\MonthlyClosing;
use App\Models\Bonus;
use DateTime;
use Illuminate\Http\Request;

class RunMonthlyClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'closing:monthly {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run monthly closing untuk bulan tertentu (default: bulan ini)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month') ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);

        $this->info("Memulai closing untuk bulan: {$month}");

        // Check if already closed
        if (MonthlyClosing::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->count()) {
            $this->warn("Closing untuk bulan {$month} sudah pernah dilakukan!");
            if (!$this->confirm('Apakah Anda ingin melanjutkan? (Ini akan membuat duplikasi bonus)')) {
                return;
            }
        }

        // Run existing closing logic
        $this->info("Menjalankan closing logic yang sudah ada...");
        $request = new Request(['month' => $month]);
        $controller = new MonthlyClosingController();
        
        try {
            $controller->store($request);
            $this->info("✓ Closing logic berhasil dijalankan");
        } catch (\Exception $e) {
            $this->error("Error pada closing logic: " . $e->getMessage());
        }

        // Run Profit Sharing 5% payout
        $this->info("Menjalankan payout Profit Sharing 5%...");
        try {
            Helper::payoutProfitSharing($month);
            $this->info("✓ Profit Sharing 5% payout berhasil");
        } catch (\Exception $e) {
            $this->error("Error pada Profit Sharing payout: " . $e->getMessage());
        }

        // Summary
        $this->newLine();
        $this->info("==========================================");
        $this->info("SUMMARY BONUS BULAN {$month}");
        $this->info("==========================================");
        
        $bonusTypes = Bonus::whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        foreach ($bonusTypes as $bonus) {
            $this->line("  {$bonus->type}:");
            $this->line("    - Jumlah: {$bonus->count} records");
            $this->line("    - Total: Rp " . number_format($bonus->total, 0, ',', '.'));
        }

        $totalBonus = Bonus::whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->sum('amount');

        $this->newLine();
        $this->info("Total Bonus: Rp " . number_format($totalBonus, 0, ',', '.'));
        $this->info("==========================================");
    }
}
