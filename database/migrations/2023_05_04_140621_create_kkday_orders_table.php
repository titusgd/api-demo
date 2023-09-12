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
        Schema::create('kkday_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('prod_no')->comment('商品 ID');
            $table->integer('pkg_no')->comment('套餐 ID');
            $table->integer('item_no')->comment('品項編號 ID');
            $table->string('s_date')->nullable()->comment('出發日期 yyyy-mm-dd');
            $table->string('e_date')->nullable()->comment('結束時間 yyyy-mm-dd');
            $table->string('event_time')->nullable()->comment('場次時間 hh:mm');
            $table->string('partner_order_no')->nullable()->comment('自訂編號');
            $table->string('buyer_first_name')->comment('購買人名稱');
            $table->string('buyer_last_name')->comment('購買人姓氏');
            $table->string('buyer_email')->comment('購買人 Email');
            $table->string('buyer_tel_country_code')->comment('購買人電話國碼');
            $table->string('buyer_tel_number')->comment('購買人電話');
            $table->string('buyer_country')->comment('購買人國家');
            $table->string('guide_lang')->comment('導覽語系');
            $table->text('order_note')->comment('訂單備註');
            $table->integer('qty')->comment('購買數量');
            $table->decimal('price', 10, 2)->comment('單價');
            $table->decimal('discount_price', 10, 2)->default(0.00)->comment('折扣價');
            $table->decimal('total_price', 10, 2)->comment('總價 = (原價 - 折扣價) * 數量');
            $table->string('pay_type')->default('01')->comment('付款種類');
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
        Schema::dropIfExists('kkday_orders');
    }
};
