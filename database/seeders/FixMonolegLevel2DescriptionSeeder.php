<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\User;
use App\Traits\Helper;

/**
 * Fix deskripsi bonus monoleg untuk level > 1.
 *
 * Untuk level > 1, bonus monoleg harus tercatat dari "member pertama langsung" (Leg Kiri)
 * dari leg tersebut, bukan dari member ke-2 yang join. Contoh: bonus level 2 ke konsorpusat1
 * dari join A2 harus diubah menjadi dari A1 (member pertama langsung dari Apusat).
 *
 * Seeder ini mengoreksi deskripsi yang sudah salah (masih pakai A2, B2, dst) menjadi
 * member pertama langsung (A1, B1, dst).
 */
class FixMonolegLevel2DescriptionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== Fix Deskripsi Bonus Monoleg Level 2+ (pakai member pertama langsung) ===');
        $this->command->info('');

        $bonuses = Bonus::where('type', 'Komisi Monoleg')
            ->where(function ($q) {
                for ($level = 2; $level <= 10; $level++) {
                    $q->orWhere('description', 'like', '%(Level ' . $level . ')%');
                }
            })
            ->get();

        $this->command->info('Ditemukan ' . $bonuses->count() . ' bonus monoleg dengan Level 2+');
        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($bonuses as $bonus) {
            $description = $bonus->description;

            // Parse level: "... (Level N)"
            if (!preg_match('/\(Level (\d+)\)/', $description, $levelMatch)) {
                $skipped++;
                continue;
            }
            $level = (int) $levelMatch[1];
            if ($level < 2) {
                $skipped++;
                continue;
            }

            // Parse username: "dari join X paket" atau "dari upgrade X paket"
            if (!preg_match('/dari (?:join|upgrade) (.+?) paket/', $description, $userMatch)) {
                $skipped++;
                continue;
            }
            $wrongUsername = trim($userMatch[1]);
            $wrongUser = User::where('username', $wrongUsername)->first();
            if (!$wrongUser) {
                $this->command->warn("  User tidak ditemukan: {$wrongUsername} (Bonus ID {$bonus->id})");
                $errors++;
                continue;
            }

            $sponsor = $wrongUser->sponsor;
            if (!$sponsor) {
                $skipped++;
                continue;
            }

            $firstDirect = Helper::getFirstDirectDownlineWithPremium($sponsor);
            if (!$firstDirect || $firstDirect->username === $wrongUsername) {
                // Sudah benar (member pertama) atau tidak ada first direct
                $skipped++;
                continue;
            }

            // Ganti hanya bagian nama member di deskripsi (antara "dari join/upgrade " dan " paket")
            $newDescription = preg_replace(
                '/(dari (?:join|upgrade) )' . preg_quote($wrongUsername, '/') . '(\s+paket)/',
                '${1}' . $firstDirect->username . '${2}',
                $description,
                1
            );
            if ($newDescription === $description || $newDescription === null) {
                $skipped++;
                continue;
            }

            $bonus->update(['description' => $newDescription]);
            $fixed++;
            $this->command->line("  ✓ Bonus ID {$bonus->id}: \"... dari join {$wrongUsername} ...\" → \"... dari join {$firstDirect->username} ...\" (Level {$level})");
        }

        $this->command->info('');
        $this->command->info("Selesai: diperbaiki {$fixed}, dilewati {$skipped}, error {$errors}");
        $this->command->info('=== Fix Deskripsi Bonus Monoleg Level 2+ Selesai ===');
    }
}
