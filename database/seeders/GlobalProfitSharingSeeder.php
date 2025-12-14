<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\GlobalProfitSharingDaily;
use App\Models\GlobalProfitSharingSaving;
use App\Models\UserPin;
use App\Traits\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GlobalProfitSharingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan mengisi tabel global_profit_sharing_daily dengan data historis
     * berdasarkan transaksi yang sudah ada. Seeder akan:
     * 1. Mengambil semua user Platinum aktif
     * 2. Loop dari tanggal pertama UserPin sampai H-1 (kemarin)
     * 3. Untuk setiap tanggal, hitung omzet harian menggunakan Helper::transactionPoinDaily()
     * 4. Hitung GPS amount (5% dari omzet) dan bagi ke semua Platinum aktif
     * 5. Insert ke global_profit_sharing_daily dan update global_profit_sharing_savings
     * 
     * Cara menjalankan:
     * php artisan db:seed --class=GlobalProfitSharingSeeder
     */
    public function run(): void
    {
        $this->command->info('Memulai seeder untuk Global Profit Sharing (GPS)...');
        
        // Tentukan tanggal mulai (dari tanggal pertama UserPin)
        $firstUserPin = UserPin::orderBy('created_at', 'asc')->first();
        
        if (!$firstUserPin) {
            $this->command->warn('Tidak ada UserPin ditemukan. Seeder selesai.');
            return;
        }
        
        $startDate = Carbon::parse($firstUserPin->created_at)->startOfDay();
        $endDate = Carbon::yesterday()->startOfDay(); // H-1 (kemarin)
        
        $this->command->info('Periode: ' . $startDate->format('Y-m-d') . ' sampai ' . $endDate->format('Y-m-d') . ' (H-1)');
        
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $this->command->info('Total hari: ' . $totalDays);
        
        $progressBar = $this->command->getOutput()->createProgressBar($totalDays);
        $progressBar->start();
        
        $currentDate = $startDate->copy();
        $insertedCount = 0;
        $skippedCount = 0;
        $updatedCount = 0;
        
        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $year = $currentDate->format('Y');
            
            // Hitung omzet harian menggunakan Helper::transactionPoinDaily()
            $totalOmzet = Helper::transactionPoinDaily($date) * 1000; // Convert poin ke rupiah
            
            if ($totalOmzet > 0) {
                // Persentase GPS (5%)
                $gpsPercent = 0.05;
                $totalGpsAmount = round($totalOmzet * $gpsPercent);
                
                // Dapatkan semua user Platinum perdana (first pin Platinum, bukan upgrade/RO/maintain)
                // Hanya Platinum yang JOIN dari awal (type = 'premium', bukan 'upgrade')
                // Dan harus memiliki profit_sharings record dengan is_perdana_platinum = true
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
                
                // Hitung jumlah per member: GPS Amount : Jumlah Platinum Perdana Aktif
                $platinumCount = $platinumUsers->count();
                if ($platinumCount > 0) {
                    $gpsAmountPerMember = round($totalGpsAmount / $platinumCount);
                } else {
                    $gpsAmountPerMember = 0;
                }
                
                if ($gpsAmountPerMember > 0) {
                    foreach ($platinumUsers as $user) {
                        // Cek apakah sudah ada data untuk tanggal ini
                        $existing = GlobalProfitSharingDaily::where('user_id', $user->id)
                            ->where('date', $date)
                            ->exists();
                        
                        if (!$existing) {
                            // Simpan data harian
                            GlobalProfitSharingDaily::create([
                                'user_id' => $user->id,
                                'date' => $date,
                                'amount' => $gpsAmountPerMember,
                            ]);
                            
                            // Update atau create saving record
                            $gpsSaving = GlobalProfitSharingSaving::firstOrCreate(
                                [
                                    'user_id' => $user->id,
                                ],
                                [
                                    'daily_accumulation' => 0,
                                    'wallet_cashback' => 0,
                                    'date' => $date,
                                ]
                            );
                            
                            // Tambahkan akumulasi harian
                            $dailyAccumulation = $gpsSaving->daily_accumulation + $gpsAmountPerMember;
                            // Batas wallet cashback maksimal 22.500.000 (tidak ada batas tahunan)
                            $walletCashback = min($dailyAccumulation, 22500000);
                            
                            $gpsSaving->update([
                                'daily_accumulation' => $dailyAccumulation,
                                'wallet_cashback' => $walletCashback,
                                'date' => $date,
                            ]);
                            
                            $insertedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }
                }
            }
            
            $currentDate->addDay();
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Seeder selesai!');
        $this->command->info('Data baru yang diinsert: ' . $insertedCount);
        $this->command->info('Data yang sudah ada (dilewati): ' . $skippedCount);
        
        // Summary per user
        $this->command->newLine();
        $this->command->info('Summary per user:');
        $summary = GlobalProfitSharingSaving::select('user_id', 'daily_accumulation', 'wallet_cashback')
            ->orderBy('wallet_cashback', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($summary as $item) {
            $user = User::find($item->user_id);
            if ($user) {
                $this->command->line("  {$user->username} ({$user->name}):");
                $this->command->line("    - Total Akumulasi: Rp " . number_format($item->daily_accumulation, 0, ',', '.'));
                $this->command->line("    - Wallet Cashback: Rp " . number_format($item->wallet_cashback, 0, ',', '.'));
            }
        }
    }
}
