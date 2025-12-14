<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Cart;
use App\Traits\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestAutoRO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-ro {username} {--poin=300}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Auto RO feature dengan membuat transaksi simulasi';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $testPoin = $this->option('poin');
        
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            $this->error("User dengan username '{$username}' tidak ditemukan!");
            return 1;
        }
        
        $this->info("=== Test Auto RO untuk {$username} ===");
        $this->info("Poin yang akan ditambahkan: {$testPoin}");
        
        // Cek kondisi sebelum test
        $this->info("\n--- Kondisi Sebelum Test ---");
        $this->info("Username: {$user->username}");
        $this->info("Pin: " . ($user->premiumUserPin->pin->name ?? 'N/A'));
        $this->info("Active Until: {$user->active_until}");
        $this->info("Is Active: " . ($user->is_active ? 'Yes' : 'No'));
        
        if (!$user->active_until) {
            $this->error("User tidak dalam masa aktif!");
            return 1;
        }
        
        // Hitung PV saat ini
        $activeFrom = Carbon::parse($user->active_until)->subDays($user->active_days_initial ?? 45);
        $activeUntil = Carbon::parse($user->active_until);
        
        $transactionPoinBefore = Transaction::where('user_id', $user->id)
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->whereBetween('created_at', [$activeFrom, $activeUntil])
            ->sum('poin');
        
        $officialPoinBefore = \App\Models\OfficialTransaction::where('user_id', $user->id)
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->whereBetween('created_at', [$activeFrom, $activeUntil])
            ->sum('poin');
        
        $dailyPoinPVBefore = $user->dailyPoins()
            ->where('pv', '>', 0)
            ->whereBetween('date', [$activeFrom->format('Y-m-d'), $activeUntil->format('Y-m-d')])
            ->sum('pv');
        
        $totalPVBefore = $transactionPoinBefore + $officialPoinBefore + $dailyPoinPVBefore;
        
        $this->info("PV saat ini: {$totalPVBefore}");
        $this->info("  - Transaction PV: {$transactionPoinBefore}");
        $this->info("  - Official PV: {$officialPoinBefore}");
        $this->info("  - Daily Poin PV: {$dailyPoinPVBefore}");
        
        // Cek apakah sudah ada RO di bulan ini (limit 1 kali per bulan)
        $hasROBefore = $user->userPins()
            ->whereHas('pin', function($q) use ($user) {
                $pin = $user->premiumUserPin->pin->name ?? null;
                if ($pin) {
                    $q->where('name', $pin);
                }
            })
            ->where('is_ro', true)
            ->where('is_used', true)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        $this->info("Jumlah RO di bulan ini (sebelum): {$hasROBefore}");
        
        // Simulasi: Buat transaksi dengan poin yang ditentukan
        $this->info("\n--- Membuat Transaksi Simulasi ---");
        
        // Cari produk dengan poin yang sesuai atau terdekat
        $product = Product::where('poin', '<=', $testPoin)
            ->orderBy('poin', 'desc')
            ->first();
        
        if (!$product) {
            $this->error("Tidak ada produk dengan poin <= {$testPoin}!");
            return 1;
        }
        
        // Hitung qty yang diperlukan
        $qty = ceil($testPoin / $product->poin);
        $totalPoin = $product->poin * $qty;
        
        $this->info("Produk: {$product->name} (Poin: {$product->poin})");
        $this->info("Qty: {$qty}");
        $this->info("Total Poin: {$totalPoin}");
        
        if ($this->confirm("Apakah Anda yakin ingin membuat transaksi simulasi ini?")) {
            DB::beginTransaction();
            try {
                // Buat transaksi
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'general',
                    'poin' => $totalPoin,
                    'price' => $product->price * $qty,
                    'status' => 'paid', // Langsung paid untuk test
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Buat cart
                Cart::create([
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $product->price,
                    'price_total' => $product->price * $qty,
                    'poin' => $product->poin,
                    'poin_total' => $totalPoin,
                ]);
                
                $this->info("✓ Transaksi dibuat: ID {$transaction->id}");
                
                // Panggil fungsi Auto RO (seperti di TransactionController@confirm)
                $this->info("Memanggil Helper::checkAndTriggerAutoROFromPV()...");
                $result = Helper::checkAndTriggerAutoROFromPV($user, $totalPoin);
                
                if ($result) {
                    $this->info("✓ Auto RO berhasil dibuat!");
                } else {
                    $this->warn("✗ Auto RO tidak dibuat (mungkin belum mencapai 170 PV atau sudah ada RO)");
                }
                
                DB::commit();
                
                // Cek hasil setelah test
                $this->info("\n--- Hasil Setelah Test ---");
                $user->refresh();
                
                $this->info("Active Until setelah: {$user->active_until}");
                
                // Cek RO di bulan ini setelah test (limit 1 kali per bulan)
                $hasROAfter = $user->userPins()
                    ->whereHas('pin', function($q) use ($user) {
                        $pin = $user->premiumUserPin->pin->name ?? null;
                        if ($pin) {
                            $q->where('name', $pin);
                        }
                    })
                    ->where('is_ro', true)
                    ->where('is_used', true)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count();
                
                $this->info("Jumlah RO di bulan ini (sesudah): {$hasROAfter}");
                
                if ($hasROAfter > $hasROBefore) {
                    $newRO = $user->userPins()
                        ->whereHas('pin', function($q) use ($user) {
                            $pin = $user->premiumUserPin->pin->name ?? null;
                            if ($pin) {
                                $q->where('name', $pin);
                            }
                        })
                        ->where('is_ro', true)
                        ->where('is_used', true)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->latest()
                        ->first();
                    
                    if ($newRO) {
                        $this->info("RO baru dibuat:");
                        $this->info("  - ID: {$newRO->id}");
                        $this->info("  - Code: {$newRO->code}");
                        $this->info("  - Created: {$newRO->created_at}");
                    }
                } else {
                    $this->warn("Auto RO tidak dibuat karena sudah ada Auto RO di bulan ini (limit 1 kali per bulan)");
                }
                
                // Cek bonus generasi untuk upline
                $sponsor = $user->sponsor;
                if ($sponsor) {
                    $bonusGenerasi = $sponsor->bonuses()
                        ->where('type', 'Bonus Generasi')
                        ->where('description', 'like', '%RO ' . $user->username . '%')
                        ->latest()
                        ->first();
                    
                    if ($bonusGenerasi) {
                        $this->info("\nBonus Generasi untuk upline ({$sponsor->username}):");
                        $this->info("  - Amount: Rp " . number_format($bonusGenerasi->amount, 0, ',', '.'));
                        $this->info("  - Description: {$bonusGenerasi->description}");
                        $this->info("  - Created: {$bonusGenerasi->created_at}");
                    }
                }
                
                $this->info("\n✓ Test selesai!");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error: " . $e->getMessage());
                $this->error($e->getTraceAsString());
                return 1;
            }
        } else {
            $this->info("Test dibatalkan.");
        }
        
        return 0;
    }
}

