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
        Schema::connection('*')->table('ticket_product_bases', function (Blueprint $table) {
            $table->integer('prod_no')->comment('商品編號')->default(0)->change();
            $table->string('prod_name')->comment('商品名稱')->nullable()->change();
            $table->integer('prod_url_no')->comment('商品網址編號')->default(0)->change();
            $table->string('prod_type')->comment('商品類型')->nullable()->change();
            $table->json('tag')->comment('標籤')->nullable()->change();
            $table->integer('rating_count')->comment('評分數量')->default(0)->change();
            $table->integer('avg_rating_star')->comment('平均評分')->default(0)->change();
            $table->boolean('instant_booking')->comment('即時預訂')->default(false)->change();
            $table->integer('order_count')->comment('訂單數量')->default(0)->change();
            $table->integer('days')->comment('天數')->default(0)->change();
            $table->integer('hours')->comment('小時')->default(0)->change();
            $table->integer('duration')->comment('總時數')->default(0)->change();
            $table->string('introduction')->comment('介紹')->nullable()->change();
            $table->string('prod_img_url')->comment('商品圖片網址')->nullable()->change();
            $table->integer('b2c_price')->comment('B2C價格')->default(0)->change();
            $table->integer('b2b_price')->comment('B2B價格')->default(0)->change();
            $table->string('prod_currency')->comment('商品幣別')->nullable()->change();
            $table->json('countries')->comment('國家')->nullable()->change();
            $table->string('purchase_type')->comment('購買類型')->nullable()->change();
            $table->string('purchase_date')->comment('購買日期')->nullable()->change();
            $table->string('earliest_sale_date')->comment('最早銷售日期')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_product_bases', function (Blueprint $table) {
            //
        });
    }
};
