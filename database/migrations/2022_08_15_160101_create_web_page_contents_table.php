<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebPageContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_page_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('web_page_id')->comment("FK ID");
            $table->integer('user_id')    ->comment("新增者")->default(0);
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
        //
    }
}
