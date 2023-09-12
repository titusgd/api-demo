<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        // 移除重名資料表
        Schema::connection('*')->dropIfExists('notice_users');
        // 建立資料表
        Schema::connection('*')->create('notice_users', function (Blueprint $table) {
            $table->id();
            $table->integer('notice_id');
            $table->integer('forwarder_id')->comment('轉發者')->nullable();
            $table->integer('recipient_id')->comment('收件者');
            $table->boolean('close_type')->default(false);
            $table->string('type','50')->comment('類別');
            $table->integer('fk_id')->comment('fk_id');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('notice_notice_users');
    }
};
