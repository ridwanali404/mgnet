<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Traits\Helper;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Bonus harian - Pair bonus
        $schedule->call(function () {
            ini_set('max_execution_time', '-1');
            ini_set('memory_limit', '-1');
            Helper::pair(date('Y-m-d'));
            // Helper::generasi(date('Y-m-d'));
            // Log::info('Pair bonus generated: ' . date('Y-m-d'));
        })->dailyAt('23:00');
        // })->everyMinute();

        // Bonus harian - Profit Sharing (dihitung harian jika sudah Qualified)
        $schedule->call(function () {
            ini_set('max_execution_time', '-1');
            ini_set('memory_limit', '-1');
            Helper::calculateProfitSharing(date('Y-m-d'));
            Log::info('Profit Sharing calculated: ' . date('Y-m-d'));
        })->dailyAt('23:30');

        // Bonus harian - Uang Trip (dihitung harian jika sudah Qualified, masuk tabel klaim)
        $schedule->call(function () {
            ini_set('max_execution_time', '-1');
            ini_set('memory_limit', '-1');
            Helper::calculateUmrohTrip(date('Y-m-d'));
            Log::info('Umroh Trip calculated: ' . date('Y-m-d'));
        })->dailyAt('23:45');

        // Bonus harian - Global Profit Sharing (GPS) untuk seluruh Platinum aktif
        $schedule->call(function () {
            ini_set('max_execution_time', '-1');
            ini_set('memory_limit', '-1');
            Helper::calculateGlobalProfitSharing(date('Y-m-d'));
            Log::info('Global Profit Sharing calculated: ' . date('Y-m-d'));
        })->dailyAt('23:50');

        // Power Plus: hitung per bulan, reset setiap bulan (hanya omset bulan berjalan)
        // Jalankan tiap hari agar data "bulan ini" selalu terupdate dari daily_poins bulan ini saja
        $schedule->call(function () {
            ini_set('max_execution_time', '-1');
            ini_set('memory_limit', '-1');
            Helper::calculatePowerPlus(date('Y-m'));
            Log::info('Power Plus calculated for month: ' . date('Y-m'));
        })->dailyAt('00:15');

        // Backups (to Google Drive)
        $schedule->command('backup:clean')->dailyAt('01:30');
        $schedule->command('backup:run --only-to-disk=google')->dailyAt('01:35');
        // $schedule->command('backup:run --only-db')->dailyAt('01:35');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}