<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveDayoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_dayoffs', function (Blueprint $table) {
            $table->id();
            $table->string('number')->comment('假單單號');
            $table->integer('user_id')->comment('請假人員');
            $table->integer('substitute_id')->comment('代理人')->nullable();
            $table->datetime('start')->comment('開始時間');
            $table->datetime('end')->comment('結束時間');
            $table->decimal('total_hour')->comment('時數(小時)');
            $table->string('note',500)->comment('請假說明');
            $table->integer('leave_type_id')->comment('請假類別');

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
        Schema::dropIfExists('leave_dayoffs');
    }
}
