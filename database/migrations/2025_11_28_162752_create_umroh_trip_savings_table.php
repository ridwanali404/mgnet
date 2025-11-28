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
        Schema::create('umroh_trip_savings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('yearly_accumulation')->default(0)->comment('Akumulasi tahunan (maksimal 50.000.000)');
            $table->bigInteger('claimed_amount')->default(0)->comment('Jumlah yang sudah diklaim');
            $table->integer('active_teams_count')->default(0)->comment('Jumlah tim aktif (minimal 3)');
            $table->year('year');
            $table->timestamps();
            
            $table->index(['user_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umroh_trip_savings');
    }
};
