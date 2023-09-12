<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 刪除資料表
        Schema::connection('*')->dropIfExists('punchtimecards');
        // 重新建立資料表
        Schema::connection('*')->create('punchtimecards', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment("使用者id");
            $table->dateTime("date_time")->comment("日期時間");
            $table->string("status",2)->comment("5.in 上班 | 6.out 下班");
            $table->string("os")->comment("作業系統");
            $table->string("client_ip")->comment("使用者ip");
            $table->timestamps();;
        });
    }

    public function down()
    {
        Schema::dropIfExists('punchtimecards');
    }
};
