<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 移除重名資料表
        Schema::connection('*')->dropIfExists('reviews');
        // 建立資料表
        Schema::connection('*')->create('reviews', function (Blueprint $table) {
            //  type      varchar(30)    同images.type用法，紀錄功能類別
            //  fk_id     int            同images.fk_id用法，紀錄fk id
            //  user_id   int            簽核人員user id
            //  level     int            簽核層級
            //  status    boolean        簽核結果 1 true 0 false
            //  note      varchar(100)   備註
            $table->id();
            $table->string('type',30)->comment('同images.type用法，紀錄功能類別');
            $table->integer('fk_id')->comment('同images.fk_id用法，紀錄fk id');
            $table->integer('user_id')->comment('簽核人員user id');
            $table->integer('rank')->comment('簽核層級');
            $table->string('status',2)->comment('簽核結果 1.未審核|2.未核准|3.核准');
            // pending 未審核 | approval 核准 | fail 未核准
            $table->dateTime('date')->nullable()->comment('簽核日期');
            $table->string('note',100)->comment('備註');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
