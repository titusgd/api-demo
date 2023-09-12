<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 移除重名資料表
        Schema::connection('*')->dropIfExists('notice_responses');
        // 建立資料表
        Schema::connection('*')->create('notice_responses', function (Blueprint $table) {
            $table->id();
            $table->integer('notice_id');
            $table->integer('user_id');
            $table->string('content')->comment('內容');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('notice_responses');
    }
};
