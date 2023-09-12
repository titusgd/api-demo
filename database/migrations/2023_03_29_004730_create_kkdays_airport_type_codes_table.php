<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkdays_airport_type_codes', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable(true);
            $table->string('description_ch')->nullable(true);
            $table->string('description_en')->nullable(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kkdays_airport_type_codes');
    }
};
