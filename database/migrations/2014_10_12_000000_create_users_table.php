<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('type')->default('member'); // admin, member
            $table->string('username')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->integer('bank_id')->unsigned()->nullable();
            $table->foreign('bank_id')->references('id')->on('banks')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('bank_account')->nullable();
            $table->string('bank_as')->nullable();
            $table->string('ktp')->nullable();
            $table->string('npwp')->nullable();
            $table->boolean('is_stockist')->default(false);
            $table->boolean('is_master_stockist')->default(false);
            $table->bigInteger('cash_reward')->default(0);
            $table->bigInteger('cash_automaintain')->default(0);
            $table->bigInteger('cash_award')->default(0);
            $table->bigInteger('cash_rank')->default(0);

            $table->string('image')->nullable();

            // cr add
            $table->bigInteger('member_id')->nullable();
            $table->string('phase')->nullable();
            $table->string('roles')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('sponsor_id')->unsigned()->nullable();
            $table->foreign('sponsor_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('monoleg_id')->unsigned()->nullable();
            $table->foreign('monoleg_id')->references('id')->on('users')
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
        Schema::dropIfExists('users');
    }
}