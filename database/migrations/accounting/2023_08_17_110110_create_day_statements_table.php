<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("*")->dropIfExists("day_statements");
        Schema::connection('*')->create('day_statements', function (Blueprint $table) {
            $table->id();
            $table->date('statement_date');
            $table->decimal('total_receive', 12, 2)->comment('今日總計-收');
            $table->decimal('total_pay', 12, 2)->comment('今日總計-支');
            $table->integer('time')->comment('日結次數');
            $table->boolean('flag')->comment('0.不可編輯 1.可編輯');
            $table->string('summons')->comment('傳票號碼');
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
        Schema::dropIfExists('day_statements');
    }
};
