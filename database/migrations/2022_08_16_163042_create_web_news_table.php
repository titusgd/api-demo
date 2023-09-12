<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_news', function (Blueprint $table) {
            $table->id();
            $table->string('language',3)->comment("語系");
            $table->string('type',2)    ->comment('類型 1:媒體消息 2:最新消息');
            $table->string('media',30)  ->comment("媒體");
            $table->string('title',30)  ->comment("標題");
            $table->string('summary',300)->comment("摘要");
            $table->date("date")         ->comment("日期");
            $table->boolean('flag')      ->comment('啟用旗標');
            $table->boolean('link_flag') ->comment('使用連結旗標');
            $table->string('link',50)    ->comment('連結');
            $table->integer('user_id')   ->comment("新增者")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_news');
    }
}
