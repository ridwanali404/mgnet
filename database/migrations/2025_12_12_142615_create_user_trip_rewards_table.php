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
        Schema::create('user_trip_rewards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('trip_reward_id')->unsigned()->nullable();
            $table->foreign('trip_reward_id')->references('id')->on('trip_rewards')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('amount')->default(0); // Jumlah yang diklaim
            $table->boolean('is_paid')->default(false); // Status sudah dibayar/belum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_trip_rewards');
    }
};
