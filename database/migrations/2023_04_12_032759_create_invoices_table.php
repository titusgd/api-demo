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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_order_no')->comment('自定編號');
            $table->integer('total_amount')->comment('收據金額');
            $table->string('invoice_number')->comment('收據號碼');
            $table->string('invoice_trans_no')->comment('開立流水號');
            $table->string('random_num')->comment('收據防偽隨機碼 ');
            $table->string('message')->comment('回傳訊息');
            $table->datetime('create_time')->comment('時間');
            $table->string('check_code')->comment('時間');
            $table->string('display_url')->comment('網址');
            // 作廢單
            $table->string('invalid_no')->nullable()->comment('作廢單流水號');
            $table->string('status')->comment('狀態');
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
        Schema::dropIfExists('invoices');
    }
};
