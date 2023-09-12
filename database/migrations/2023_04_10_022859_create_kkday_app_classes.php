<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkday_app_classes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('description_ch', 50)->nullable(true);
            $table->string('description_en')->nullable(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kkday_app_classes');
    }
};
