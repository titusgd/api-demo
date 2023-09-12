<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->create('ticket_hot_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('kkday_cat_sub_key_id')->comment('kkday_cat_sub_key_id');
            $table->integer('image_id')->comment('圖片id');
            $table->integer('sort')->comment('排序');
            $table->boolean('status')->comment('使用狀態');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hot_tags');
    }
};
