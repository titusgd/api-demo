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
        Schema::create('travel_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_order_no')->comment('自訂編號');
            $table->string('category')->comment('收據種類');
            $table->integer('status')->default(1)->comment('1=即時開立收據 2=預約自動開立收據');
            $table->string('buyer_name')->nullable()->comment('買受人名稱 ');
            $table->string('buyer_ubn')->nullable()->comment('買受人統一編號 ');
            $table->string('buyer_address')->nullable()->comment('買受人地址');
            $table->string('buyer_email')->comment('買受人電子信箱');
            $table->string('buyer_phone')->nullable()->comment('買受人手機');
            $table->string('seller_name')->comment('經辦人名稱 ');
            $table->integer('total_amt')->comment('收據金額');
            $table->integer('email_lang')->default(0)->comment('收據通知信語系');
            $table->string('item_name')->comment('摘要（商品名稱）');
            $table->string('item_count')->comment('商品數量');
            $table->string('item_unit')->comment('商品單位');
            $table->string('item_price')->comment('商品單價');
            $table->string('item_amt')->comment('商品小計');
            $table->string('tour_name')->nullable()->comment('團名');
            $table->string('tour_no')->nullable()->comment('團號');
            $table->date('tour_date')->nullable()->comment('預計出團日 ');
            $table->integer('tax_noted')->nullable()->comment('申報註記');
            $table->string('comment')->comment('備註');
            $table->date('create_status_time')->nullable()->comment('預計開立日期');
            $table->integer('create_statusadd')->nullable()->comment('預計開立日期延長日');
            $table->string('invoice_number')->nullable()->comment('收據號碼');
            $table->integer('invoice_trans_no')->nullable()->comment('交易號碼');
            $table->string('random_num')->nullable()->comment('隨機碼');
            $table->string('check_code')->nullable()->comment('check code');
            $table->integer('surplus')->nullable()->comment('剩餘張數');
            $table->string('display_url')->nullable()->comment('資料展示網址');
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
        Schema::dropIfExists('travel_invoices');
    }
};
