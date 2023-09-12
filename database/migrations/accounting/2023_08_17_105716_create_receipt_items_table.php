<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::connection("*")->dropIfExists("receipt_items");
        Schema::connection('*')->create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->integer('receipt_id')->comment("收款單fk id")->default(0);
            $table->integer('accounting_subject_id')->comment("會計科目fk id")->default(0);
            $table->integer('currency_id')->comment('幣別fk id')->default(0);
            $table->tinyInteger('pay_type')->comment("收款方式 23:現金 24:支票 25:匯款 27:應收帳款 28:信用卡 29:線上/電子支付 30:其他")->default(0);
            $table->tinyInteger('debit_credit')->comment("1:借方 2:貸方");
            $table->string('summary', 50)->comment("摘要");
            $table->decimal('qty', $precision = 6, $scale = 1)->comment('數量');
            $table->decimal('price', $precision = 10,  $scale = 2)->comment('價格');
            $table->decimal('exchange_rate', $precision = 7, $scale = 4)->comment('匯率')->default(1);
            $table->datetime('received_date')->comment('收款日期')->nullable();
            $table->string('note', 100)->comment('備註')->nullable();
            $table->integer('transfer_voucher_id')->comment("銷帳轉帳傳票fk id")->nullable()->after('accounting_subject_id');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipt_items');
    }
};
