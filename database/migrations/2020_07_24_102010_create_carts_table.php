<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('transaction_id')->unsigned()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('qty');
            $table->string('name')->nullable();
            $table->bigInteger('price')->nullable();
            $table->bigInteger('price_total')->nullable();
            $table->integer('poin')->nullable();
            $table->integer('poin_total')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('weight_total')->nullable();
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
        Schema::dropIfExists('carts');
    }
}
