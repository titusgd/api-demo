<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAnnualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_annuals', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('使用者');
            $table->integer('year')->comment('年分');
            $table->string('take_office_day',10)->comment('入職日期');
            $table->string('start',15)->comment("起始日期");
            $table->string('end',15)->comment('結束日期');
            $table->decimal('pai_day',4,2)->comment('可休天數');
            $table->text('formula')->comment("計算規則");
            $table->string('content')->comment('說明');
            // $table->boolean('take_effect')->comment('生效');
            $table->string('version',20)->comment('版本');
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
        Schema::dropIfExists('leave_annuals');
    }
}
