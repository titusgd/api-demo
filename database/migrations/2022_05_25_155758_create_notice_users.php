<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_users', function (Blueprint $table) {
            $table->id();
            $table->integer('notice_id');
            $table->integer('forwarder_id')->comment('轉發者')->nullable();
            $table->integer('recipient_id')->comment('收件者');
            $table->boolean('close_type')->default(false);
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
        Schema::dropIfExists('notice_users');
    }
}
