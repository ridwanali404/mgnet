<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Rank;
use App\Models\UserRank;
use App\Models\Bonus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateAllRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rank:update-all {--reset : Reset cash_rank dan recalculate dari bonus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rank semua member berdasarkan cash_rank';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reset = $this->option('reset');
        
        if ($reset) {
            $this->info('Mode: RESET - Akan menghitung ulang cash_rank dari bonus');
        } else {
            $this->info('Mode: UPDATE - Akan update rank berdasarkan cash_rank yang ada');
        }
        
        if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?')) {
            $this->info('Dibatalkan.');
            return 0;
        }
        
        // Ambil semua rank yang tersedia, urutkan berdasarkan nominal
        $ranks = Rank::orderBy('nominal', 'asc')->get();
        
        if ($ranks->isEmpty()) {
            $this->error('Tidak ada rank yang tersedia!');
            return 1;
        }
        
        $this->info("\nDaftar Rank:");
        foreach ($ranks as $rank) {
            $this->line("  - {$rank->rank}: Rp " . number_format($rank->nominal, 0, ',', '.'));
        }
        
        // Ambil semua member
        $users = User::where('type', 'member')->get();
        $this->info("\nTotal member: {$users->count()}");
        
        $processed = 0;
        $updated = 0;
        $errors = 0;
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            try {
                DB::beginTransaction();
                
                // Jika reset, hitung ulang cash_rank dari bonus
                if ($reset) {
                    // Hitung cash_rank dari bonus yang valid
                    // Bonus yang menghasilkan cash_rank biasanya dari: Komisi Sponsor, Komisi Monoleg, Komisi Pasangan, Bonus Generasi
                    $cashRank = Bonus::where('user_id', $user->id)
                        ->whereIn('type', [
                            'Komisi Sponsor',
                            'Komisi Monoleg',
                            'Komisi Pasangan',
                            'Bonus Generasi'
                        ])
                        ->sum('amount');
                    
                    // Update cash_rank
                    $user->cash_rank = $cashRank;
                    $user->save();
                }
                
                // Hapus semua UserRank yang ada (untuk recalculate)
                $user->userRanks()->delete();
                
                // Recalculate rank berdasarkan cash_rank
                $cashRank = $user->cash_rank;
                
                // Cari semua rank yang sudah dicapai
                $achievedRanks = $ranks->filter(function ($rank) use ($cashRank) {
                    return $cashRank >= $rank->nominal;
                });
                
                // Buat UserRank untuk setiap rank yang sudah dicapai
                // Tentukan tanggal berdasarkan kapan cash_rank pertama kali mencapai nominal rank tersebut
                foreach ($achievedRanks as $rank) {
                    // Cari tanggal bonus pertama yang membuat cash_rank mencapai nominal rank ini
                    $rankDate = $this->getRankAchievementDate($user, $rank->nominal);
                    
                    UserRank::create([
                        'user_id' => $user->id,
                        'rank_id' => $rank->id,
                        'created_at' => $rankDate,
                        'updated_at' => $rankDate,
                    ]);
                }
                
                DB::commit();
                
                if ($achievedRanks->count() > 0) {
                    $updated++;
                }
                
                $processed++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("Error untuk user {$user->username}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("=== Ringkasan ===");
        $this->info("Total diproses: {$processed}");
        $this->info("Rank diupdate: {$updated}");
        $this->info("Error: {$errors}");
        
        if ($reset) {
            $this->info("\n✓ Cash rank sudah dihitung ulang dari bonus");
        }
        $this->info("✓ Rank sudah diupdate untuk semua member");
        
        return 0;
    }
    
    /**
     * Tentukan tanggal ketika user pertama kali mencapai nominal rank tertentu
     * Berdasarkan akumulasi bonus yang menghasilkan cash_rank
     */
    private function getRankAchievementDate($user, $targetNominal)
    {
        // Ambil semua bonus yang menghasilkan cash_rank, urutkan berdasarkan tanggal
        $bonuses = Bonus::where('user_id', $user->id)
            ->whereIn('type', [
                'Komisi Sponsor',
                'Komisi Monoleg',
                'Komisi Pasangan',
                'Bonus Generasi'
            ])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $accumulated = 0;
        foreach ($bonuses as $bonus) {
            $accumulated += $bonus->amount;
            if ($accumulated >= $targetNominal) {
                return Carbon::parse($bonus->created_at);
            }
        }
        
        // Jika tidak ditemukan, gunakan tanggal sekarang
        return Carbon::now();
    }
}
