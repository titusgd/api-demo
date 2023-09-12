<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        
        Schema::connection('*')->table('kkday_country_codes', function (Blueprint $table) {
            $table->integer('stor')->comment('排序');
        });
    }
    public function down()
    {
        Schema::table('kkday_country_codes', function (Blueprint $table) {
            //
        });
    }
};
