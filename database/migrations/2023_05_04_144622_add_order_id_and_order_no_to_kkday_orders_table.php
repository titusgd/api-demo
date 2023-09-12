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
            $table->string('order_no')->comment('實際的 order_no');
            $table->string('order_id')->comment('關聯的 order_id');
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
