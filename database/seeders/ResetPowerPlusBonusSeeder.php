<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\PowerPlusQualification;
use Carbon\Carbon;

/**
 * Reset bonus Power Plus sampai bulan sebelumnya.
 * Menghapus semua record Bonus Power Plus dan PowerPlusQualification
 * untuk bulan-bulan yang sudah lewat (sampai akhir bulan lalu).
 *
 * Setelah dijalankan, jalankan perhitungan ulang per bulan jika perlu:
 *   Helper::calculatePowerPlus('2025-01');
 *   Helper::calculatePowerPlus('2025-02');
 * atau: php artisan powerplus:reset-group-omzet --month=YYYY-MM --recalculate
 */
class ResetPowerPlusBonusSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== Reset Bonus Power Plus (sampai bulan sebelumnya) ===');
        $this->command->info('');

        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $startOfCurrentMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

        $this->command->info('Batas: bonus & kualifikasi dengan tanggal <= ' . $endOfPreviousMonth . ' akan dihapus.');
        $this->command->info('');

        // 1. Hapus Bonus Power Plus yang created_at sebelum bulan berjalan
        $bonusDeleted = Bonus::where('type', 'Bonus Power Plus')
            ->where('created_at', '<', $startOfCurrentMonth)
            ->delete();

        $this->command->info('1. Dihapus ' . $bonusDeleted . ' record Bonus Power Plus (sampai bulan sebelumnya).');

        // 2. Hapus PowerPlusQualification sampai akhir bulan sebelumnya
        $qualDeleted = PowerPlusQualification::where('date', '<=', $endOfPreviousMonth)->delete();

        $this->command->info('2. Dihapus ' . $qualDeleted . ' record PowerPlusQualification (sampai bulan sebelumnya).');
        $this->command->info('');
        $this->command->info('=== Reset Bonus Power Plus selesai ===');
        $this->command->info('Untuk mengisi ulang, jalankan perhitungan per bulan:');
        $this->command->info('  App\\Traits\\Helper::calculatePowerPlus(\'YYYY-MM\');');
        $this->command->info('atau: php artisan powerplus:reset-group-omzet --month=YYYY-MM --recalculate');
    }
}
