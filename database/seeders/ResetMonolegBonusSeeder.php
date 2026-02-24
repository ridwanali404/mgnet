<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\UserPin;
use App\Models\Pin;
use App\Traits\Helper;
use Carbon\Carbon;

/**
 * Reset penuh data bonus monoleg (Komisi Monoleg), lalu isi ulang dengan logika yang benar.
 * Timestamp bonus mengikuti waktu penggunaan UserPin (waktu daftar/upgrade/RO member).
 *
 * Langkah:
 * 1. Reverse automaintain dari setiap bonus Komisi Monoleg yang ada
 * 2. Hapus semua record bonus type Komisi Monoleg
 * 3. Isi ulang dari setiap UserPin (Gold/Platinum) dengan timestamp = userPin->created_at
 */
class ResetMonolegBonusSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== Reset Bonus Monoleg (Komisi Monoleg) ===');
        $this->command->info('');

        // 1. Reverse automaintain untuk setiap Komisi Monoleg
        $bonuses = Bonus::where('type', 'Komisi Monoleg')->get();
        $this->command->info('1. Reverse automaintain dari ' . $bonuses->count() . ' bonus Komisi Monoleg...');

        foreach ($bonuses as $bonus) {
            $user = $bonus->user;
            if (!$user) {
                continue;
            }
            $amount = (int) round(0.1 * $bonus->amount);
            if ($amount <= 0) {
                continue;
            }
            $user->decrement('cash_automaintain', $amount);
            $user->refresh();
            $user->automaintains()->create([
                'type' => 'D',
                'amount' => $amount,
                'current' => $user->cash_automaintain,
                'description' => 'Reversal automaintain dari reset bonus monoleg: ' . $bonus->description,
            ]);
        }

        // 2. Hapus semua Komisi Monoleg
        $deleted = Bonus::where('type', 'Komisi Monoleg')->delete();
        $this->command->info('2. Dihapus ' . $deleted . ' record bonus Komisi Monoleg.');
        $this->command->info('');

        // 3. Isi ulang dari UserPin (Gold/Platinum), timestamp = waktu penggunaan UserPin
        $this->command->info('3. Mengisi ulang bonus monoleg (timestamp = waktu penggunaan UserPin)...');

        $userPins = UserPin::with(['user', 'pin'])
            ->whereHas('pin', function ($q) {
                $q->whereIn('name', ['Gold', 'Platinum'])->where('type', 'premium');
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
            if (str_contains($pin->name, 'BSM')) {
                $skipped++;
                continue;
            }
            if (!$pin->monoleg_percent || $pin->monoleg_percent <= 0) {
                $skipped++;
                continue;
            }

            $firstUpline = $user->upline ?? $user->sponsor;
            if (!$firstUpline || $firstUpline->uplines()->whereHas('premiumUserPin')->count() < 1) {
                $skipped++;
                continue;
            }

            // Timestamp ikut updated_at UserPin joiner (waktu pendaftaran/pakai pin)
            $occurredAt = isset($userPin->updated_at) && $userPin->updated_at
                ? ($userPin->updated_at instanceof Carbon ? $userPin->updated_at : Carbon::parse($userPin->updated_at))
                : ($userPin->created_at instanceof Carbon ? $userPin->created_at : Carbon::parse($userPin->created_at));

            if ($userPin->is_ro ?? false) {
                $goldPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
                if (!$goldPin) {
                    $skipped++;
                    continue;
                }
                $basePrice = $goldPin->ro_price ?? 1700000;
                $monolegPercent = $goldPin->monoleg_percent ?? 9;
                Helper::calculateMonolegBonusRecursive(
                    $firstUpline,
                    $user,
                    $basePrice,
                    $monolegPercent,
                    'RO Automaintain',
                    $occurredAt
                );
            } else {
                $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                $descriptionTemplate = 'Bonus Monoleg 9%% dari ' . $action . ' %s paket ' . $pin->name . '.';
                Helper::calculateMonolegBonusRecursive(
                    $firstUpline,
                    $user,
                    $pin->price,
                    $pin->monoleg_percent,
                    $descriptionTemplate,
                    $occurredAt
                );
            }
            $processed++;
        }

        $this->command->info('   Diproses: ' . $processed . ' UserPin, dilewati: ' . $skipped);
        $this->command->info('');
        $this->command->info('=== Reset Bonus Monoleg Selesai ===');
    }
}
