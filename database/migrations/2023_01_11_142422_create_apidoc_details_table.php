<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApidocDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apidoc_details', function (Blueprint $table) {
            $table->id();
            $table->json('req')->comment('請求參數');
            $table->json('res')->comment('返回值');
            $table->json('reqjson')->comment('請求參數 JSON 範例');
            $table->json('resjson')->comment('返回值 JSON 範例');
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
        Schema::dropIfExists('apidoc_details');
    }
}
