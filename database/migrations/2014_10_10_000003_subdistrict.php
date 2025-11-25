<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Subdistrict extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subdistrict', function (Blueprint $table) {
            $table->increments('subdistrict_id');
            $table->integer('province_id')->unsigned();
            $table->foreign('province_id')
                ->references('province_id')->on('province')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')
                ->references('city_id')->on('city')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('type');
            $table->string('subdistrict_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subdistrict');
    }
}