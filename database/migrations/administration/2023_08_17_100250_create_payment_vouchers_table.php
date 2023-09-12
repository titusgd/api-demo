<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->dropIfExists('payment_vouchers');
        Schema::connection('*')->create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->default('')->comment('支憑單號');
            $table->integer('user_id')->comment("新增人員id");
            $table->integer('store_id')->comment("新增人員所屬分店");
            $table->string('title',50)->comment("標題");
            $table->string('content',500)->comment("內容");
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
