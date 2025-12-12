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
        Schema::create('trip_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama reward, contoh: "Bali", "Umroh"
            $table->bigInteger('nominal'); // Nominal reward, contoh: 5000000 untuk Bali, 35000000 untuk Umroh
            $table->text('description')->nullable(); // Deskripsi reward (opsional)
            $table->boolean('is_active')->default(true); // Status aktif/tidak aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_rewards');
    }
};
