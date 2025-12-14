<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\Helper;
use App\Models\Bonus;
use App\Models\GlobalProfitSharingSaving;
use App\Models\GlobalProfitSharingDaily;
use App\Models\User;
use DateTime;
use Carbon\Carbon;

class RegenerateGlobalProfitSharing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:regenerate {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate bonus Global Profit Sharing untuk bulan tertentu (default: bulan ini). Format: Y-m (contoh: 2025-12)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month') ?? date('Y-m');
        
        try {
            $date = DateTime::createFromFormat('Y-m', $month);
            if (!$date || $date->format('Y-m') !== $month) {
                throw new \Exception('Format bulan tidak valid');
            }
        } catch (\Exception $e) {
            $this->error("Format bulan tidak valid! Gunakan format Y-m (contoh: 2025-12)");
            return 1;
        }

        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        $monthReadable = Carbon::parse($date->format('Y-m-01'))->translatedFormat('F Y');

        $this->info("==========================================");
        $this->info("REGENERATE BONUS GLOBAL PROFIT SHARING");
        $this->info("==========================================");
        $this->info("Bulan: {$monthReadable} ({$month})");
        $this->newLine();

        try {
            \DB::beginTransaction();

            // 1. Hapus bonus GPS yang sudah dibuat untuk bulan tersebut
            $this->info("1. Menghapus bonus GPS yang sudah dibuat...");
            $deletedBonuses = Bonus::where('type', 'Bonus Global Profit Sharing')
                ->whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->where('description', 'like', '%bulan ' . $month . '%')
                ->delete();
            $this->info("   ✓ Dihapus {$deletedBonuses} bonus GPS");

            // 2. Restore GPS saving dari GPS daily untuk bulan tersebut
            $this->info("2. Restore GPS saving dari GPS daily...");
            $platinumUsers = User::whereHas('profitSharings', function ($q) {
                $q->where('is_perdana_platinum', true);
            })
            ->whereHas('premiumUserPin', function ($q) {
                $q->whereHas('pin', function ($qPin) {
                    $qPin->where('name', 'Platinum')->where('type', 'premium');
                });
            })
            ->where('is_active', true)
            ->get();

            $restoredCount = 0;
            $bar = $this->output->createProgressBar($platinumUsers->count());
            $bar->start();

            foreach ($platinumUsers as $user) {
                // Hitung total GPS daily untuk bulan tersebut
                $gpsDailyTotal = $user->globalProfitSharingDailies()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');

                if ($gpsDailyTotal > 0) {
                    // Update atau create GPS saving
                    $gpsSaving = GlobalProfitSharingSaving::firstOrCreate(
                        ['user_id' => $user->id],
                        ['daily_accumulation' => 0, 'wallet_cashback' => 0, 'date' => $endDate]
                    );

                    // Restore wallet_cashback (capped at 22.5jt)
                    $walletCashback = min($gpsDailyTotal, 22500000);
                    $gpsSaving->update([
                        'daily_accumulation' => $gpsDailyTotal,
                        'wallet_cashback' => $walletCashback,
                        'date' => $endDate,
                    ]);
                    $restoredCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("   ✓ Restore {$restoredCount} GPS saving dari GPS daily");

            // 3. Buat bonus GPS baru dari GPS saving
            $this->info("3. Membuat bonus GPS baru...");
            Helper::payoutGlobalProfitSharing($month);
            
            // Hitung bonus yang dibuat
            $newBonuses = Bonus::where('type', 'Bonus Global Profit Sharing')
                ->whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->where('description', 'like', '%bulan ' . $month . '%')
                ->count();
            
            $totalAmount = Bonus::where('type', 'Bonus Global Profit Sharing')
                ->whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->where('description', 'like', '%bulan ' . $month . '%')
                ->sum('amount');

            $this->info("   ✓ Dibuat {$newBonuses} bonus GPS baru");
            $this->info("   ✓ Total amount: Rp " . number_format($totalAmount, 0, ',', '.'));

            \DB::commit();

            $this->newLine();
            $this->info("==========================================");
            $this->info("REGENERATE BERHASIL!");
            $this->info("==========================================");
            $this->info("Bulan: {$monthReadable}");
            $this->info("Bonus GPS yang dibuat: {$newBonuses}");
            $this->info("Total amount: Rp " . number_format($totalAmount, 0, ',', '.'));
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
