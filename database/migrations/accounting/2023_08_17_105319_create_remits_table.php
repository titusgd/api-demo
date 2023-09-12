<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection("*")->dropIfExists("remits");
        Schema::connection('*')->create('remits', function (Blueprint $table) {
            $table->id();
            $table->string('type',30)                ->comment('同images.type用法，紀錄功能類別');
            $table->integer('fk_id')                 ->comment('同images.fk_id用法，紀錄fk id');
            $table->integer('stores_id')             ->comment("分店fk id")   ->default(0);
            $table->integer('user_id')               ->comment("新增者fk id") ->default(0);
            $table->integer('accounting_subject_id') ->comment("會計科目fk id")->default(0);
            $table->integer('price')                 ->comment('金額');
            $table->date('date')                     ->comment('匯款日期');
            $table->string('note',100)               ->comment('備註')         ->nullable();   
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('remits');
    }
};
