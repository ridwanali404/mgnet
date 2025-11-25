<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('type'); // Bonus Unilevel Mingguan, Komisi Penjualan, Bonus Unilevel RO, Bonus Royalti Profit Sharing 70%, Bonus Royalti Profit Sharing 30%
            $table->longText('description')->nullable();
            $table->bigInteger('amount');
            $table->boolean('is_poin')->default(false);
            $table->datetime('paid_at')->nullable();
            $table->datetime('used_at')->nullable();
            $table->bigInteger('used_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonuses');
    }
}
