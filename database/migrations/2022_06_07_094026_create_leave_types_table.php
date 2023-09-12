<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string("name",50)->comment("假別名稱");
            $table->integer("days")->comment("天數額度(年)");
            $table->decimal('min',4,1)    ->comment('最低請假時數');
            $table->string("direction",500)->comment("說明");
            $table->boolean("status")->comment("狀態:true 啟用,false 停用");
            $table->integer("user_id")->comment("編輯人員");
            $table->integer("sort")->comment('排序')->nullable();
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
        Schema::dropIfExists('leave_types');
    }
}
