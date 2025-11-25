<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficialTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('official_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('qty')->default(0);
            $table->integer('poin')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('cashback')->default(0);
            $table->boolean('is_topup')->default(false);
            $table->bigInteger('stockist_id')->unsigned()->nullable();
            $table->foreign('stockist_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            // cr
            $table->boolean('is_turbo')->default(false);
            $table->boolean('is_big')->default(false);
            $table->integer('month')->nullable();
            $table->integer('month_key')->nullable();
            $table->bigInteger('address_id')->unsigned()->nullable();
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onUpdate('cascade')->onDelete('set null');

            // transaction
            $table->string('shipment')->nullable();
            $table->string('shipment_number')->nullable();
            $table->bigInteger('shipment_fee')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('code')->nullable();
            $table->bigInteger('price_total')->nullable();
            $table->string('receipt')->nullable();
            $table->string('status')->default('pending'); // expired, paid, packed, shipped, received
            $table->string('product_name')->nullable();
            $table->bigInteger('product_price')->nullable();
            $table->timestamps();
        });

        Schema::table('official_transactions', function (Blueprint $table) {
            $table->bigInteger('official_transaction_id')->unsigned()->nullable();
            $table->foreign('official_transaction_id')->references('id')->on('official_transactions')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('transaction_id')->unsigned()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('official_transactions');
    }
}
