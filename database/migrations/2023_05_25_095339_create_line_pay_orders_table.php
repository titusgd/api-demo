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
        Schema::create('line_pay_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->comment('訂單編號');
            $table->string('amount')->comment('主要交易金額');
            $table->string('currency')->comment('交易幣別');
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
        Schema::dropIfExists('line_pay_orders');
    }
};
