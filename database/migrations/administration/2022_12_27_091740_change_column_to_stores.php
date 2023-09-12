<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToStores extends Migration
{
    public function up()
    {
        Schema::connection('*')->table('stores', function (Blueprint $table) {
            $table->string('week',20)->default('');
            $table->string('time',20)->default('');
            $table->text('map')->nullable();
            $table->integer('image_id')->default(0);
            $table->boolean('show_web')->default(false);
        });
    }
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            //
        });
    }
}
