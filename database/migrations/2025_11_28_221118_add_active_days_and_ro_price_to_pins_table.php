<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->integer('active_days')->nullable()->after('is_generasi')->comment('Hari aktif untuk paket ini (45 untuk Gold, 90 untuk Platinum)');
            $table->bigInteger('ro_price')->nullable()->after('active_days')->comment('Harga Repeat Order untuk paket ini (1.700.000 untuk Gold, 12.750.000 untuk Platinum)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->dropColumn(['active_days', 'ro_price']);
        });
    }
};
