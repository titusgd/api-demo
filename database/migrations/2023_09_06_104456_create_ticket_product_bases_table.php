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
        Schema::create('ticket_product_bases', function (Blueprint $table) {
            $table->id();
            $table->integer('prod_no')->comment('商品編號');
            $table->string('prod_name')->comment('商品名稱');
            $table->integer('prod_url_no')->comment('商品網址編號');
            $table->string('prod_type')->comment('商品類型');
            $table->json('tag')->comment('標籤');
            $table->integer('rating_count')->comment('評分數量');
            $table->integer('avg_rating_star')->comment('平均評分');
            $table->boolean('instant_booking')->comment('即時預訂');
            $table->integer('order_count')->comment('訂單數量');
            $table->integer('days')->comment('天數');
            $table->integer('hours')->comment('小時');
            $table->integer('duration')->comment('總時數');
            $table->string('introduction')->comment('介紹');
            $table->string('prod_img_url')->comment('商品圖片網址');
            $table->integer('b2c_price')->comment('B2C價格');
            $table->integer('b2b_price')->comment('B2B價格');
            $table->string('prod_currency')->comment('商品幣別');
            $table->json('countries')->comment('國家');
            $table->string('purchase_type')->comment('購買類型');
            $table->string('purchase_date')->comment('購買日期');
            $table->string('earliest_sale_date')->comment('最早銷售日期');
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
        Schema::dropIfExists('ticket_product_bases');
    }
};
