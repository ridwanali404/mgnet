<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('free'); // free, premium, upgrade
            $table->bigInteger('price')->default(0);
            $table->bigInteger('bonus_sponsor')->default(0);
            $table->integer('poin_pair')->default(0);
            $table->integer('poin_reward')->default(0);
            $table->integer('poin_ro')->default(0);
            $table->integer('pair_flush')->default(0);
            $table->integer('reward_flush')->default(0);
            $table->integer('level')->default(0);
            $table->bigInteger('bonus_monoleg')->default(0);
            $table->boolean('is_generasi')->default(false);
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
        Schema::dropIfExists('pins');
    }
}