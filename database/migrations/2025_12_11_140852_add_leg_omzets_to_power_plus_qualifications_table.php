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
        Schema::table('power_plus_qualifications', function (Blueprint $table) {
            // Tambahkan kolom untuk menyimpan omset per leg (JSON)
            // Format: {"Leg Kiri": 5000, "Leg 1": 15500, "Leg 2": 7000, "Leg 3": 11000}
            $table->json('leg_omzets')->nullable()->after('right_omzet')->comment('Omset per leg dalam format JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('power_plus_qualifications', function (Blueprint $table) {
            $table->dropColumn('leg_omzets');
        });
    }
};
