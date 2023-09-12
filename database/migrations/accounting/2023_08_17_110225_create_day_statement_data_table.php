<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection("*")->dropIfExists("day_statement_data");
        Schema::connection('*')->create('day_statement_data', function (Blueprint $table) {
            $table->id();
            $table->integer('day_statement_id');
            $table->boolean('debit_credit')->comment('類別');
            $table->string('summary')->comment('摘要');
            $table->decimal('price', 12, 2)->comment('金額');
            $table->integer('accounting_subject_id')->comment('會計科目');
            $table->integer('pay_type')->comment('支付方式');
            $table->string('summons')->comment('傳票號碼');
            $table->string('invoice')->comment('單據號碼');
            $table->string('type')->comment('來源表');
            $table->integer('fk_id')->comment('來源表id');
            $table->string('code')->comment('來源code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('day_statement_data');
    }
};
