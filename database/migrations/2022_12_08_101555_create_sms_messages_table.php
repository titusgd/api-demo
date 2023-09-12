<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('mobile')->comment('手機號碼');
            $table->string('message')->comment('發送簡訊內容');
            $table->datetime('sdate')->comment('預約開始時間，必須大於 now+10 分鐘');
            $table->datetime('edate')->comment('預約結束時間');
            $table->boolean('status')->comment('狀態');
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
        Schema::dropIfExists('sms_messages');
    }
}
