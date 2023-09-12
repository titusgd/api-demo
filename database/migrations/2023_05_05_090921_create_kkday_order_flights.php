<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkday_order_flights', function (Blueprint $table) {
            $table->id();
            $table->string('kkday_order_id')->comment('訂單ID');
            $table->string('traffic_type')->nullable(true)->comment('交通類型值');
            $table->string('arrival_airport')->nullable(true)->comment('抵達機場');
            $table->string('arrival_flightType')->nullable(true)->comment('抵達班機為國內線/國際線');
            $table->string('arrival_airlineName')->nullable(true)->comment('抵達班機的航空公司');
            $table->string('arrival_flightNo')->nullable(true)->comment('抵達班機的航班編號');
            $table->string('arrival_terminalNo')->nullable(true)->comment('抵達班機的航廈編號');
            $table->string('arrival_visa')->nullable(true)->comment('抵達班機的簽證');
            $table->string('arrival_date')->nullable(true)->comment('抵達班機的日期(yyyy-MM-dd)');
            $table->string('arrival_time')->nullable(true)->comment('抵達班機的時間');
            $table->string('departure_airport')->nullable(true)->comment('離開機場');
            $table->string('departure_flightType')->nullable(true)->comment('離開班機為國內線/國外線');
            $table->string('departure_airlineName')->nullable(true)->comment('離開班機的航空公司名稱');
            $table->string('departure_flightNo')->nullable(true)->comment('離開班機的航班編號');
            $table->string('departure_terminalNo')->nullable(true)->comment('離開班機的航廈編號');
            $table->string('departure_date')->nullable(true)->comment('離開班機的日期(yyyy-MM-dd)');
            $table->string('departure_time')->nullable(true)->comment('離開班機的時間');

            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('kkday_order_flights');
    }
};
