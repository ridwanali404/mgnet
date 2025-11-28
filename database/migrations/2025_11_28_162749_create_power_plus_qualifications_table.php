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
        Schema::create('power_plus_qualifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('left_omzet')->default(0)->comment('Omzet kaki kiri dalam point');
            $table->bigInteger('right_omzet')->default(0)->comment('Omzet kaki kanan dalam point');
            $table->bigInteger('smaller_leg_omzet')->default(0)->comment('Omzet kaki kecil (min dari kiri/kanan)');
            $table->boolean('is_qualified_15k')->default(false)->comment('Qualified untuk 15.000 point');
            $table->boolean('is_qualified_30k')->default(false)->comment('Qualified untuk 30.000 point');
            $table->bigInteger('bonus_amount')->default(0)->comment('Jumlah bonus yang diterima');
            $table->date('date');
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_plus_qualifications');
    }
};
