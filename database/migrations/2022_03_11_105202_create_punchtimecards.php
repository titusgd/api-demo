<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePunchtimecards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('punchtimecards', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment("使用者id");
            $table->dateTime("date_time")->comment("日期時間");
            $table->string("status",2)->comment("5.in 上班 | 6.out 下班");
            $table->string("os")->comment("作業系統");
            $table->string("client_ip")->comment("使用者ip");
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
        Schema::dropIfExists('punchtimecard');
    }
}
