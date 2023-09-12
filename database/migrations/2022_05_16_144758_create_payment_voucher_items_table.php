<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentVoucherItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->string('summary')->comment('摘要');
            $table->integer('qty')->comment('數量');
            $table->decimal('price',10,2);
            $table->integer('payment_voucher_id')->comment('fk id');
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
        Schema::dropIfExists('payment_voucher_items');
    }
}
