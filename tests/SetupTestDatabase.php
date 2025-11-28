<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

trait SetupTestDatabase
{
    /**
     * Setup database testing sebelum test dijalankan
     */
    protected function setUpTestDatabase(): void
    {
        // Pastikan menggunakan database testing
        $databaseName = env('DB_DATABASE', 'mgnet_test');
        
        // Set database connection untuk testing
        config(['database.connections.mysql.database' => $databaseName]);
        
        // Reconnect dengan database baru
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        // Run migrations untuk testing database
        Artisan::call('migrate:fresh', [
            '--env' => 'testing',
            '--database' => 'mysql',
        ]);
    }
}

