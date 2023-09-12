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
        Schema::create('line_pay_order_products', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->comment('訂單編號');
            $table->string('package_id')->comment('套餐編號');
            $table->string('amount')->comment('交易金額');
            $table->string('quantity')->comment('數量');
            $table->string('name')->comment('產品名稱');
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
        Schema::dropIfExists('line_pay_order_products');
    }
};
