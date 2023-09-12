<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            
            $table->id();
            $table->string('number')->comment('伸議書單號');
            $table->integer('user_id')->comment("新增人員id");
            $table->integer('store_id')->comment("新增人員所屬分店");
            $table->string('title',50)->comment("標題");
            $table->string('content',500)->comment("內容");
            $table->timestamps();
        });

        // user_id   int 
        // store_id  int
        // title     varchar(50)
        // content   varchar(500)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
