<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')  ->comment("新增者")->default(0);
            $table->string('language',3)->comment("語系");
            $table->string('name',100)  ->comment('標題');
            $table->string('link',50)   ->comment('連結');
            $table->boolean('flag')     ->comment('啟用旗標');
            $table->integer('sort_by')  ->comment("排序");
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
        Schema::dropIfExists('videos');
    }
}
