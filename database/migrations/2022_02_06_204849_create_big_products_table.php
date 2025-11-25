<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBigProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('big_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')
                ->onUpdate('cascade')->onDelete('cascade');

            // child product
            $table->bigInteger('child_product_id')->unsigned()->nullable();
            $table->foreign('child_product_id')->references('id')->on('products')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('product_name');
            $table->integer('product_poin');

            $table->integer('qty');
            $table->integer('poin');
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
        Schema::dropIfExists('big_products');
    }
}
