<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->dropIfExists('ip_lists');
        Schema::connection('*')->create('ip_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('編輯人員');
            $table->string('ip',20)->comment('ip位置');
            $table->string('note',50)->comment('備註、分店名稱');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('ip_lists');
    }
};
