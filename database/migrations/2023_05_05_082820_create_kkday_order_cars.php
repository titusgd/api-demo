<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkday_order_cars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kkday_order_id')->comment('訂單ID');
            $table->string('traffic_type')->nullable(true)->comment('交通類型值');
            $table->string('is_rent_customize')->comment('客製化路徑');
            $table->string('s_location')->nullable(true)->comment('出發地');
            $table->string('e_location')->nullable(true)->comment('抵達地');
            $table->string('s_address')->nullable(true)->comment('出發地址');
            $table->string('e_address')->nullable(true)->comment('抵達地址');
            $table->string('s_date')->nullable(true)->comment('出發日(yyyy-MM-dd)');
            $table->string('e_date')->nullable(true)->comment('抵達日(yyyy-MM-dd)');
            $table->string('s_time')->nullable(true)->comment('出發時間');
            $table->string('e_time')->nullable(true)->comment('抵達時間');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('kkday_order_cars');
    }
};
