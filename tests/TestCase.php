<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup untuk testing dengan database terpisah
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Pastikan menggunakan database testing (mgnet_test)
        // Database ini akan digunakan saat APP_ENV=testing
        $testDatabase = 'mgnet_test';
        
        // Set database connection untuk testing
        config(['database.connections.mysql.database' => $testDatabase]);
        
        // Reconnect dengan database testing
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        // Pastikan database ada, jika tidak ada akan error (user perlu membuat manual atau via script)
        try {
            DB::connection('mysql')->getPdo();
        } catch (\Exception $e) {
            $this->markTestSkipped('Database testing (mgnet_test) tidak tersedia. Silakan buat database terlebih dahulu dengan: ./setup-test-db.sh');
        }
    }
}
