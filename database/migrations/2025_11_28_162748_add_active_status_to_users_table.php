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
        Schema::table('users', function (Blueprint $table) {
            $table->date('active_until')->nullable()->after('monoleg_id');
            $table->integer('active_days_initial')->nullable()->after('active_until'); // 45 untuk Gold, 90 untuk Platinum
            $table->boolean('is_active')->default(true)->after('active_days_initial');
            $table->integer('sponsor_count')->default(0)->after('is_active'); // Untuk tracking jumlah sponsor
            $table->string('placement_side')->nullable()->after('sponsor_count'); // 'left' atau 'right' untuk penempatan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['active_until', 'active_days_initial', 'is_active', 'sponsor_count', 'placement_side']);
        });
    }
};
