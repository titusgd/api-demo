<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('currency_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('name_ch', 20)->comment('貨幣名稱')->nullable(true);
            $table->string('code', 10)->comment('貨幣代碼')->nullable(true);
            $table->decimal('exchange',7, 4)->comment('貨幣匯率')->default(0);
            $table->integer('updater_id')->comment('更新人員')->default(0);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('currency_exchanges');
    }
};
