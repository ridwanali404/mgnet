<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProfitSharing;
use App\Models\ProfitSharingDaily;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use App\Models\GlobalDailyPoin;
use App\Models\KeyValue;
use App\Models\Poin;
use App\Models\UserPin;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class ProfitSharingDailySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan mengisi tabel profit_sharing_daily dengan data historis
     * berdasarkan transaksi yang sudah ada. Seeder akan:
     * 1. Mengambil semua user yang memiliki profit sharing (Platinum aktivasi perdana)
     * 2. Loop dari tanggal pertama transaksi sampai hari ini
     * 3. Untuk setiap tanggal, hitung omzet harian dari Transaction, OfficialTransaction, dan GlobalDailyPoin
     * 4. Hitung profit sharing amount (5% dari omzet)
     * 5. Insert ke profit_sharing_daily untuk semua user yang memiliki profit sharing record
     * 
     * Catatan: Seeder ini mengasumsikan semua user yang memiliki profit sharing record
     * sudah qualified. Jika ingin lebih akurat, uncomment pengecekan qualified di dalam loop.
     * 
     * Cara menjalankan:
     * php artisan db:seed --class=ProfitSharingDailySeeder
     */
    public function run(): void
    {
        $this->command->info('Memulai seeder untuk profit_sharing_daily...');
        
        // Ambil semua user yang memiliki profit sharing (Platinum aktivasi perdana)
        $users = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })->where('is_active', true)->get();
        
        $this->command->info('Ditemukan ' . $users->count() . ' user dengan profit sharing.');
        
        if ($users->count() == 0) {
            $this->command->warn('Tidak ada user dengan profit sharing. Seeder selesai.');
            return;
        }
        
        // Tentukan tanggal mulai (dari tanggal pertama transaksi atau 1 tahun yang lalu)
        $firstTransaction = Transaction::whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('poin', '>', 0)
            ->orderBy('created_at', 'asc')
            ->first();
        
        $startDate = $firstTransaction 
            ? Carbon::parse($firstTransaction->created_at)->startOfDay()
            : Carbon::now()->subYear()->startOfDay();
        
        $endDate = Carbon::now()->startOfDay();
        
        $this->command->info('Periode: ' . $startDate->format('Y-m-d') . ' sampai ' . $endDate->format('Y-m-d'));
        
        $totalDays = $startDate->diffInDays($endDate);
        $this->command->info('Total hari: ' . $totalDays);
        
        $progressBar = $this->command->getOutput()->createProgressBar($totalDays);
        $progressBar->start();
        
        $currentDate = $startDate->copy();
        $insertedCount = 0;
        $skippedCount = 0;
        $batchData = [];
        $batchSize = 500;
        
        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');
            
            // Hitung omzet harian
            $dailyOmzet = $this->calculateDailyOmzet($currentDate);
            
            if ($dailyOmzet > 0) {
                // Hitung profit sharing amount (5% dari omzet)
                $profitSharingAmount = round($dailyOmzet * 0.05);
                
                // Untuk setiap user, cek apakah qualified dan simpan
                foreach ($users as $user) {
                    // Cek apakah user sudah qualified (minimal 3 tim aktif)
                    // Untuk optimasi, kita cek sekali per user dan cache hasilnya
                    // Atau kita bisa skip pengecekan qualified untuk seeder dan asumsikan semua user qualified
                    // karena mereka sudah memiliki profit sharing record
                    
                    // Cek apakah sudah ada data untuk tanggal ini
                    $existing = ProfitSharingDaily::where('user_id', $user->id)
                        ->where('date', $date)
                        ->exists();
                    
                    if (!$existing) {
                        // Untuk seeder, kita asumsikan user qualified jika memiliki profit sharing record
                        // Jika ingin lebih akurat, bisa uncomment pengecekan qualified di bawah
                        /*
                        $activeTeams = $user->uplines()
                            ->whereHas('premiumUserPin')
                            ->where('is_active', true)
                            ->count();
                        
                        if ($activeTeams >= 3) {
                        */
                            $batchData[] = [
                                'user_id' => $user->id,
                                'amount' => $profitSharingAmount,
                                'date' => $date,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            
                            if (count($batchData) >= $batchSize) {
                                ProfitSharingDaily::insert($batchData);
                                $insertedCount += count($batchData);
                                $batchData = [];
                            }
                        /*
                        }
                        */
                    } else {
                        $skippedCount++;
                    }
                }
            }
            
            $currentDate->addDay();
            $progressBar->advance();
        }
        
        // Insert sisa data
        if (count($batchData) > 0) {
            ProfitSharingDaily::insert($batchData);
            $insertedCount += count($batchData);
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Seeder selesai!');
        $this->command->info('Data baru yang diinsert: ' . $insertedCount);
        $this->command->info('Data yang sudah ada (dilewati): ' . $skippedCount);
    }
    
    /**
     * Hitung omzet harian dari transaksi
     */
    private function calculateDailyOmzet(Carbon $date)
    {
        $poin = 0;
        
        // Cek apakah menggunakan Poin model
        if (KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $poinRecord = Poin::whereDate('date', $date->format('Y-m-d'))->first();
            if ($poinRecord) {
                // Tambahkan penggunaan PIN ke omset
                $pinOmzet = UserPin::whereDate('created_at', $date->format('Y-m-d'))
                    ->sum('price');
                return ($poinRecord->poin * 1000) + $pinOmzet; // Convert poin ke rupiah + PIN omset
            }
        }
        
        // Hitung dari Transaction
        $transactionPoin = Transaction::whereDate('created_at', $date->format('Y-m-d'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        
        // Hitung dari OfficialTransaction
        $officialTransactionPoin = OfficialTransaction::whereDate('created_at', $date->format('Y-m-d'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        
        // Hitung dari GlobalDailyPoin
        $globalDailyPoin = GlobalDailyPoin::whereDate('date', $date->format('Y-m-d'))
            ->sum('pv');
        
        $poin = $transactionPoin + $officialTransactionPoin + $globalDailyPoin;
        
        // Hitung dari penggunaan PIN (pembelian PIN harian)
        $pinOmzet = UserPin::whereDate('created_at', $date->format('Y-m-d'))
            ->sum('price');
        
        // Convert poin ke rupiah (1 poin = 1000 rupiah) + PIN omset
        return ($poin * 1000) + $pinOmzet;
    }
}
