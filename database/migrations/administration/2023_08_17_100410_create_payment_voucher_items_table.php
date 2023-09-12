<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->dropIfExists('payment_voucher_items');
        Schema::connection('*')->create('payment_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->string('summary')->comment('摘要');
            $table->integer('qty')->comment('數量');
            $table->decimal('price',10,2);
            $table->integer('payment_voucher_id')->comment('fk id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_voucher_items');
    }
};
