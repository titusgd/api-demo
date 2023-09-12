<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kkday_states', function (Blueprint $table) {
            $table->id();
            $table->string("code")->comment('市場代碼');
            $table->string("name")->comment('市場名稱');
            $table->timestamps();
        });
    }
     
    public function down()
    {
        Schema::dropIfExists('kkday_states');
    }
};
