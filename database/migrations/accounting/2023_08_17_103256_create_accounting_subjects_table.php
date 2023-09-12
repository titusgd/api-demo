<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection("*")->dropIfExists("accounting_subjects");
        Schema::connection('*')->create('accounting_subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')   ->comment("新增者fk id")->default(0);
            $table->integer('subject_id')->comment("會計科目fk id")->default(0);
            $table->tinyInteger('level') ->comment('科目層級');
            $table->string('code',20)    ->comment('會計科目代碼')->unique();
            $table->string('name',20)    ->comment('會計科目名稱');
            $table->integer('type')      ->comment("類別 1:營業收入 2:營業成本 3:營業費用 4:非營業費用 5:非營業收入")->default(0);
            $table->boolean('flag')      ->comment("是否出現在選單，有 1:true 0:false");   
            $table->string('note1',50)   ->comment("備註一")->nullable();
            $table->string('note2',50)   ->comment("備註二")->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('accounting_subjects');
    }
};
