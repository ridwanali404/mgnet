<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('name');
            $table->bigInteger('price');
            $table->integer('weight')->nullable(); // gram
            $table->bigInteger('price_member')->nullable();
            $table->longText('desc')->nullable();
            $table->longText('images')->nullable();
            $table->string('youtube')->nullable();
            $table->integer('poin')->nullable();
            $table->bigInteger('sold')->default(0);
            $table->boolean('is_ro')->default(false);

            // cr
            $table->bigInteger('price_stockist')->nullable();
            $table->bigInteger('price_master')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_big')->default(false);
            $table->boolean('is_weekly')->default(false);
            $table->integer('month')->nullable();
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
        Schema::dropIfExists('products');
    }
}
