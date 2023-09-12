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
        Schema::create('kkday_order_mobiles', function (Blueprint $table) {
            $table->id();
            /**
             * 請依照底下的文字，自動生成 migration
             * kkday_order_id
             * mobile_model_no(手機型號)
             * IMEI(手機IMEI)
             * active_date(啟用日(yyyy-MM-dd)
             */
            $table->integer('kkday_order_id')->comment('關聯 kkday_orders id');
            $table->string('mobile_model_no')->comment('手機型號');
            $table->string('IMEI')->comment('手機IMEI');
            $table->date('active_date')->comment('啟用日(yyyy-MM-dd)');
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
        Schema::dropIfExists('kkday_order_mobiles');
    }
};
