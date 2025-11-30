<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixPlacementSide extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:placement-side';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix placement_side for existing users that don\'t have it set';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai perbaikan placement_side...');
        
        // Ambil semua user yang memiliki sponsor_id tapi belum punya placement_side
        $users = User::whereNotNull('sponsor_id')
            ->whereNull('placement_side')
            ->where('type', 'member')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->info("Ditemukan {$users->count()} user yang perlu diperbaiki.");
        
        $fixed = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            $sponsor = $user->sponsor;
            
            if (!$sponsor) {
                $this->warn("User {$user->username} (ID: {$user->id}) tidak memiliki sponsor yang valid. Skip.");
                $skipped++;
                continue;
            }
            
            // Hitung sponsor yang sudah ada dari sponsor ini
            $sponsorCount = $sponsor->sponsors()->count();
            
            // Tentukan placement_side berdasarkan urutan
            $placement_side = null;
            
            if ($sponsorCount == 0) {
                // Ini sponsor pertama
                $placement_side = 'left';
            } else if ($sponsorCount == 1) {
                // Ini sponsor kedua
                $placement_side = 'right';
            } else {
                // Untuk sponsor ketiga dan seterusnya, cek distribusi kiri/kanan
                $leftCount = $sponsor->sponsors()->where('placement_side', 'left')->count();
                $rightCount = $sponsor->sponsors()->where('placement_side', 'right')->count();
                
                // Tempatkan di sisi yang lebih sedikit
                $placement_side = ($leftCount <= $rightCount) ? 'left' : 'right';
            }
            
            // Update user
            $user->update([
                'placement_side' => $placement_side
            ]);
            
            $this->info("✓ User {$user->username} (ID: {$user->id}) -> placement_side: {$placement_side}");
            $fixed++;
        }
        
        $this->info("\nSelesai!");
        $this->info("Total diperbaiki: {$fixed}");
        $this->info("Total di-skip: {$skipped}");
        
        return 0;
    }
}

