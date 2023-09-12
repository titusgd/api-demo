<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->create('ticket_hot_cities', function (Blueprint $table) {
            $table->id();
            $table->integer('kkday_city_id')->comment('kkday_city_id');
            $table->integer('sort')->comment('排序');
            $table->integer('image_id')->nullable(true)->comment('圖片id');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('hot_cities');
    }
};
