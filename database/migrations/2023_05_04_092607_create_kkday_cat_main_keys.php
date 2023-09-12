<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('kkday_cat_main_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name',50)->comment('主分類名稱');
            $table->string('code',20)->comment('主分類代碼');
            $table->integer('sort')->comment('排序');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kkday_cat_main_keys');
    }
};