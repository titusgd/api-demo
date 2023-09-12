<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkday_country_codes', function (Blueprint $table) {
            $table->id();
            $table->string('tel_area')->comment('電話代碼');
            $table->string('code')->comment('國家代碼');
            $table->string('name_ch')->comment('國家名稱');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kkday_country_codes');
    }
};
