<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyProfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_profits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->integer('pp_used')->default(0);
            $table->integer('pr_used')->default(0);
            $table->integer('pp_current')->default(0);
            $table->bigInteger('pp_id')->unsigned()->nullable();
            $table->foreign('pp_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->integer('pr_current')->default(0);
            $table->date('date');
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
        Schema::dropIfExists('daily_profits');
    }
}