<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('kkday_citys', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(true)->comment('城市名稱');
            $table->string('code')->nullable(true)->comment('城市代碼');
            $table->string('country_code')->nullable(true)->comment('國家代碼');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kkday_citys');
    }
};
