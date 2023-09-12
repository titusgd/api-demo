<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_metas', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')       ->comment("新增者")->default(0);
            $table->string('code',20)        ->comment('代碼');
            $table->string('title',50)       ->comment('頁面標題');
            $table->string('description',500)->comment('頁面描述');
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
        Schema::dropIfExists('web_metas');
    }
}
