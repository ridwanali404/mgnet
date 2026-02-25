<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\UserPin;
use App\Traits\Helper;
use Carbon\Carbon;

/**
 * Reset bonus generasi lalu isi ulang dengan nominal dari generasi_bonus_amounts (nilai saat ini).
 * Timestamp bonus mengikuti waktu penggunaan UserPin (waktu daftar/upgrade/RO).
 *
 * Langkah:
 * 1. Reverse automaintain dari setiap Bonus Generasi
 * 2. Hapus semua record Bonus Generasi
 * 3. Isi ulang dari setiap UserPin (Gold/Platinum/upgrade Platinum/RO) dengan nominal saat ini
 */
class ResetGenerasiBonusSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== Reset Bonus Generasi (pakai nominal saat ini) ===');
        $this->command->info('');

        // 1. Reverse automaintain untuk setiap Bonus Generasi (7% dari amount)
        $bonuses = Bonus::where('type', 'Bonus Generasi')->get();
        $this->command->info('1. Reverse automaintain dari ' . $bonuses->count() . ' bonus Bonus Generasi...');

        foreach ($bonuses as $bonus) {
            $user = $bonus->user;
            if (!$user) {
                continue;
            }
            $amount = (int) round(0.07 * $bonus->amount);
            if ($amount <= 0) {
                continue;
            }
            $user->decrement('cash_automaintain', $amount);
            $user->refresh();
            $user->automaintains()->create([
                'type' => 'D',
                'amount' => $amount,
                'current' => $user->cash_automaintain,
                'description' => 'Reversal automaintain dari reset bonus generasi: ' . $bonus->description,
            ]);
        }

        // 2. Hapus semua Bonus Generasi
        $deleted = Bonus::where('type', 'Bonus Generasi')->delete();
        $this->command->info('2. Dihapus ' . $deleted . ' record Bonus Generasi.');
        $this->command->info('');

        // 3. Isi ulang dari UserPin (join Gold/Platinum, upgrade Platinum, RO Gold/Platinum)
        $this->command->info('3. Mengisi ulang bonus generasi (nominal dari generasi_bonus_amounts saat ini)...');

        $userPins = UserPin::with(['user', 'pin'])
            ->whereHas('pin', function ($q) {
                $q->where('is_generasi', true)
                    ->where(function ($q2) {
                        $q2->where(function ($q3) {
                            $q3->where('type', 'premium')->whereIn('name', ['Gold', 'Platinum']);
                        })->orWhere(function ($q3) {
                            $q3->where('type', 'upgrade')->where('name', 'like', '%Platinum%');
                        });
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $processed = 0;
        $skipped = 0;

        foreach ($userPins as $userPin) {
            $user = $userPin->user;
            $pin = $userPin->pin;
            if (!$user || !$pin) {
                $skipped++;
                continue;
            }

            $occurredAt = isset($userPin->updated_at) && $userPin->updated_at
                ? ($userPin->updated_at instanceof Carbon ? $userPin->updated_at : Carbon::parse($userPin->updated_at))
                : ($userPin->created_at instanceof Carbon ? $userPin->created_at : Carbon::parse($userPin->created_at));

            Helper::giveBonusGenerasiForUserPin($userPin, $occurredAt);
            $processed++;
        }

        $this->command->info('   Diproses: ' . $processed . ' UserPin, dilewati: ' . $skipped);
        $this->command->info('');
        $this->command->info('=== Reset Bonus Generasi Selesai ===');
    }
}
