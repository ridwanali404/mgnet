<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('buyer_id')->unsigned()->nullable();
            $table->foreign('buyer_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('pin_id')->unsigned()->nullable();
            $table->foreign('pin_id')->references('id')->on('pins')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('code');
            $table->string('name');
            $table->bigInteger('price');
            $table->integer('level')->default(0);
            $table->boolean('is_used')->default(false);
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
        Schema::dropIfExists('user_pins');
    }
}