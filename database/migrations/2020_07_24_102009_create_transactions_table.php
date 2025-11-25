<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('address_id')->unsigned()->nullable();
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('price')->nullable();
            $table->integer('poin')->nullable();
            $table->string('shipment')->nullable();
            $table->string('shipment_number')->nullable();
            $table->bigInteger('shipment_fee')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('code')->nullable();
            $table->bigInteger('price_total')->nullable();
            $table->string('receipt')->nullable();
            $table->string('status')->default('pending'); // expired, paid, packed, shipped, received
            $table->bigInteger('cashback')->nullable();
            $table->bigInteger('sponsor_id')->unsigned()->nullable();
            $table->foreign('sponsor_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            // cr add
            $table->string('type')->default('general'); // general, stockist, masterstockist
            $table->bigInteger('master_stockist_id')->unsigned()->nullable();
            $table->foreign('master_stockist_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('transactions');
    }
}
