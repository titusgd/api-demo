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
        Schema::create('ctbc_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->comment('商店代號');
            $table->integer('terminal_id')->comment('終端機代號');
            $table->string('lidm')->comment('交易編號');
            $table->integer('purch_amt')->comment('交易金額');
            $table->integer('tx_type')->comment('交易方式， 0: 一般交易, 1: 分期交易, 2: 紅利折抵一般交易, 3: 紅利折抵分期交易');
            $table->integer('option');
            $table->string('key')->comment(' URL 帳務管理後台登錄的壓碼字串');
            $table->string('merchant_name')->comment('商店名稱');
            $table->string('auth_res_url')->comment('從收單行端取得授權碼後，要導回的網址');
            $table->string('order_detail')->comment('訂單描述，中文請填 BIG5 碼');
            $table->string('auto_cap')->comment('是否自動請款');
            $table->string('customize')->comment('設定刷卡頁顯示特定語系或客制化頁面。1: 繁體 2: 簡體 3: 英文 4: 客制化頁面');
            $table->string('mac_string')->comment('呼叫 auth_in_mac() 後，得到的 InMac');
            $table->string('debug')->comment('預設(進行交易時)請填 0， 偵錯時請填 1。');
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
        Schema::dropIfExists('ctbc_orders');
    }
};
