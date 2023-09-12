<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection("*")->dropIfExists("cheques");
        Schema::connection('*')->create('cheques', function (Blueprint $table) {

            $table->id();
            $table->string('type', 30)->comment('同images.type用法，紀錄功能類別');
            $table->integer('fk_id')->comment('同images.fk_id用法，紀錄fk id');
            $table->integer('stores_id')->comment("分店fk id")->default(0);
            $table->integer('user_id')->comment("新增者fk id")->default(0);
            $table->integer('accounting_subject_id')->comment("會計科目fk id")->default(0);
            $table->string('code', 30)->comment('票號');
            $table->integer('status')->comment('狀態')->default(1);
            $table->integer('price')->comment('金額');
            $table->date('expiry_date')->comment('支票到期日')->nullable();
            $table->date('cashed_date')->comment('支票兌現日')->nullable();
            $table->string('note', 100)->comment('備註')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cheques');
    }
};
