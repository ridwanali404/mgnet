<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalDailyPoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_daily_poins', function (Blueprint $table) {
            $table->id();
            $table->integer('pp')->default(0); // poin pasangan
            $table->integer('pr')->default(0); // poin reward
            $table->integer('pv')->default(0); // poin ro
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
        Schema::dropIfExists('global_daily_poins');
    }
}
