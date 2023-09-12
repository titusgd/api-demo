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
        Schema::create('kkday_order_qtys', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kkday_order_id')->comment('訂單ID');
            $table->string('traffic_type')->nullable(true)->comment('交通類型值');
            $table->string('CarPsg_adult')->nullable(true)->comment('成人數量');
            $table->string('CarPsg_child')->nullable(true)->comment('孩童數量');
            $table->string('CarPsg_infant')->nullable(true)->comment('嬰兒數量');
            $table->string('SafetySeat_sup_child')->nullable(true)->comment('供應商提供的兒童安全座椅數量');
            $table->string('SafetySeat_sup_infant')->nullable(true)->comment('供應商提供的嬰兒安全座椅數量');
            $table->string('SafetySeat_self_child')->nullable(true)->comment('自備的兒童安全座椅數量');
            $table->string('SafetySeat_self_infant')->nullable(true)->comment('自備的嬰兒安全座椅數量');
            $table->string('Luggage_carry')->nullable(true)->comment('隨身行李數量');
            $table->string('Luggage_check')->nullable(true)->comment('托運行李數量');

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
        Schema::dropIfExists('kkday_order_qtys');
    }
};
