<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')   ->comment("新增者")->default(0);
            $table->string('language',3) ->comment("語系");
            $table->integer('year')      ->comment('年');
            $table->integer('month')     ->comment('月');
            $table->string('content',500)->comment('內容');
            $table->boolean('flag')      ->comment('啟用旗標');
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
        Schema::dropIfExists('web_histories');
    }
}
