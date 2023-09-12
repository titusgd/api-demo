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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(1)->comment('分類， 1: kkday, 2: 其他');
            $table->string('order_no')->comment('訂單編號');
            $table->string('partner_order_no')->comment('自訂編號');
            $table->string('contact_name')->comment('聯絡人姓名');
            $table->string('contact_email')->comment('聯絡人信箱');
            $table->string('contact_phone')->nullable()->comment('聯絡人手機');
            $table->string('contact_tel')->nullable()->comment('聯絡人電話');
            $table->integer('on_line')->default(1)->comment('1：綫上 2：綫下');
            $table->integer('status')->default(1)->comment('訂單狀態，1：正常，2：取消');
            $table->string('cancel_type')->nullable()->comment('取消種類');
            $table->string('cancel_desc')->nullable()->comment('取消原因');
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
        Schema::dropIfExists('orders');
    }
};
