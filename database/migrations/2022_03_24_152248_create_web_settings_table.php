<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category',20)->comment('類別');
            $table->string('item',20)->comment('項目');
            $table->string('value_type',20)->comment('項目型態string、boolean、number...等');
            $table->string('restriction')->comment('限制條件:nuique、max、min...等，如有多個，請使用 | 將其分隔開');
            $table->string('default_value')->comment('預設值');
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
        Schema::dropIfExists('web_settings');
    }
}
