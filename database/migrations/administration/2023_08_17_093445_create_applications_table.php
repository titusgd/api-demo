<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 移除蟲鳴資料表
        Schema::dropIfExists('applications');
        Schema::connection('*')->dropIfExists('applications');
        // 建立資料表
        Schema::connection('*')->create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('number')->comment('伸議書單號');
            $table->integer('user_id')->comment("新增人員id");
            $table->integer('store_id')->comment("新增人員所屬分店");
            $table->string('title',50)->comment("標題");
            $table->string('content',500)->comment("內容");
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('applications');
    }
};
