<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 移除同名資料表
        Schema::dropIfExists('application_items');
        Schema::connection('*')->dropIfExists('application_items');
        // 建立資料表
        Schema::connection('*')->create('application_items', function (Blueprint $table) {
            // 'application_id', 'summary', 'qty', 'price'
            $table->id();
            $table->string('summary')->comment('摘要');
            $table->integer('qty')->comment('數量');
            $table->decimal('price',10,2);
            $table->integer('application_id')->comment('fk id');
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
        Schema::dropIfExists('application_items');
    }
};
