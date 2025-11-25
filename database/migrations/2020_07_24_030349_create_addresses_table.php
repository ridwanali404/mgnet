<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('recipient')->nullable();
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('phone')->nullable();
            $table->integer('province_id')->unsigned()->nullable();
            $table->foreign('province_id')->references('province_id')->on('province')
                ->onUpdate('cascade')->onDelete('set null');
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('city_id')->on('city')
                ->onUpdate('cascade')->onDelete('set null');
            $table->integer('subdistrict_id')->unsigned()->nullable();
            $table->foreign('subdistrict_id')->references('subdistrict_id')->on('subdistrict')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('postal_code')->nullable();;
            $table->boolean('is_active')->default(false);
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
        Schema::dropIfExists('addresses');
    }
}
