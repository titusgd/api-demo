<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebNewsContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_news_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('web_news_id')->comment("FK ID");
            $table->string('type',3)      ->comment('類別 1:文 2:圖');
            $table->string('link',50)     ->comment('連結');
            $table->text('content')       ->comment('內容');
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
        Schema::dropIfExists('web_news_contents');
    }
}
