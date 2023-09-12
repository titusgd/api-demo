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
        Schema::table('kkday_orders', function (Blueprint $table) {
            $table->integer('status')->default(1)->comment('訂單狀態');
            $table->string('cancel_type')->nullable()->comment('取消種類');
            $table->string('cancel_desc')->nullable()->comment('取消原因');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kkday_orders', function (Blueprint $table) {
            //
        });
    }
};
