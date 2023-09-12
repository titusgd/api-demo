<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->create('ticket_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(true)->comment('名稱');
            $table->integer('image_id')->comment('圖片id');
            $table->integer('editor')->comment('編輯者');
            $table->boolean('status')->comment('使用狀態');
            $table->string('type')->nullable(true)->comment('類別');
            $table->string('link')->nullable(true)->comment('連結');
            $table->text('city')->nullable(true)->comment('城市代碼');
            $table->text('tag')->nullable(true)->comment('分類代碼');
            $table->date('date_from')->nullable(true)->comment('起');
            $table->date('date_to')->nullable(true)->comment('迄');
            $table->integer('sort')->comment('排序');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sliders');
    }
};
