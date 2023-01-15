<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->string('transaction_sys_id', 255)->unique();
            $table->string('transaction_type', 255);
            $table->string('transaction_referenced_item_id', 255);
            $table->string('transaction_buyer_id', 255);
            $table->string('transaction_payment_type', 255);
            $table->string('transaction_payment_ref_id', 255);
            $table->date('transaction_payment_date');
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
};
