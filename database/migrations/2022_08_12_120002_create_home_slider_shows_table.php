<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeSliderShowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_slider_shows', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment("新增者")->default(0);
            $table->string('name',20) ->comment('標題');
            $table->string('link',50) ->comment('連結');
            $table->boolean('flag')->comment('啟用旗標');
            $table->integer('sort_by')->comment("排序");
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
        Schema::dropIfExists('home_slider_shows');
    }
}
