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
        Schema::create('global_profit_sharing_savings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('daily_accumulation')->default(0)->comment('Akumulasi harian 5% dari omzet');
            $table->bigInteger('wallet_cashback')->default(0)->comment('Wallet cashback (maksimal 22.500.000)');
            $table->date('date');
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
            $table->unique(['user_id']); // Satu record per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_profit_sharing_savings');
    }
};
