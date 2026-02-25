<?php

namespace Database\Seeders;

use App\Models\Pin;
use App\Models\GenerasiBonusAmount;
use Illuminate\Database\Seeder;

class GenerasiBonusAmountSeeder extends Seeder
{
    /**
     * Nominal bonus generasi per paket (dari spesifikasi MP).
     */
    public function run()
    {
        $gold = Pin::where('name', 'Gold')->where('type', 'premium')->first();
        $platinum = Pin::where('name', 'Platinum')->where('type', 'premium')->first();

        if (!$gold || !$platinum) {
            $this->command->warn('Pin Gold atau Platinum tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $goldAmounts = [
            1 => 95000,
            2 => 76000,
            3 => 57000,
            4 => 38000,
            5 => 28500,
            6 => 30400,
            7 => 15200,
            8 => 15200,
            9 => 11400,
            10 => 11400,
        ];

        $platinumAmounts = [
            1 => 712500,
            2 => 570000,
            3 => 427500,
            4 => 285000,
            5 => 213750,
            6 => 213750,
            7 => 114000,
            8 => 114000,
            9 => 85500,
            10 => 85500,
        ];

        foreach ($goldAmounts as $level => $amount) {
            GenerasiBonusAmount::updateOrCreate(
                ['pin_id' => $gold->id, 'level' => $level],
                ['amount' => $amount]
            );
        }

        foreach ($platinumAmounts as $level => $amount) {
            GenerasiBonusAmount::updateOrCreate(
                ['pin_id' => $platinum->id, 'level' => $level],
                ['amount' => $amount]
            );
        }

        $this->command->info('Generasi bonus amounts (Gold & Platinum) seeded.');
    }
}
