<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerasiBonusAmountsTable extends Migration
{
    /**
     * Run the migrations.
     * Nominal bonus generasi per paket (Gold/Platinum) per level (1-10), editable dari admin.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generasi_bonus_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pin_id')->constrained('pins')->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->comment('Generasi 1-10');
            $table->bigInteger('amount')->default(0)->comment('Nominal bonus IDR');
            $table->timestamps();

            $table->unique(['pin_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generasi_bonus_amounts');
    }
}
