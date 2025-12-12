<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TripReward;

class TripRewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rewards = [
            [
                'name' => 'Bali',
                'nominal' => 5000000,
                'description' => 'Paket wisata ke Bali',
                'is_active' => true,
            ],
            [
                'name' => 'Umroh',
                'nominal' => 35000000,
                'description' => 'Paket Umroh',
                'is_active' => true,
            ],
            [
                'name' => 'Singapore',
                'nominal' => 8000000,
                'description' => 'Paket wisata ke Singapore',
                'is_active' => true,
            ],
            [
                'name' => 'Thailand',
                'nominal' => 7000000,
                'description' => 'Paket wisata ke Thailand',
                'is_active' => true,
            ],
            [
                'name' => 'Japan',
                'nominal' => 25000000,
                'description' => 'Paket wisata ke Japan',
                'is_active' => true,
            ],
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($rewards as $reward) {
            $existing = TripReward::where('name', $reward['name'])->first();
            
            if ($existing) {
                $existing->update($reward);
                $updatedCount++;
            } else {
                TripReward::create($reward);
                $createdCount++;
            }
        }

        $this->command->info('Trip Rewards seeded successfully!');
        $this->command->info('Created: ' . $createdCount . ' trip rewards.');
        $this->command->info('Updated: ' . $updatedCount . ' trip rewards.');
        $this->command->info('Total: ' . count($rewards) . ' trip rewards.');
    }
}
