<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::connection("*")->dropIfExists("day_statement_logs");
        Schema::connection('*')->create('day_statement_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('最後編輯人員');
            $table->integer('day_statement_id')->comment('日結id');
            $table->integer('action')->comment('1.日結 2.刪除日結');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('day_statement_logs');
    }
};
