<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kkday_country_codes', function (Blueprint $table) {
            $table->string('country_code')->nullable(true)->comment('國家代碼');
        });
    }
    public function down()
    {
        Schema::table('kkday_country_codes', function (Blueprint $table) {
            //
        });
    }
};
