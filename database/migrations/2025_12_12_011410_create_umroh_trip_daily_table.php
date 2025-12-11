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
        Schema::create('umroh_trip_daily', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('amount')->default(0)->comment('Jumlah bonus Trip harian (4% dari omzet nasional hari itu dibagi member qualified)');
            $table->date('date');
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umroh_trip_daily');
    }
};
