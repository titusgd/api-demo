<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('*')->dropIfExists('notices');

        Schema::connection('*')->create('notices', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->integer('user_id');
            $table->string('title',150);
            $table->string('content',500);
            $table->text('link')->nullable();
            $table->boolean('close_type')->default(false);
            $table->string('type','50')->comment('類別');
            $table->integer('fk_id')->comment('fk_id');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('notices');
    }
};
