<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApidocListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apidoc_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->comment('project ID');
            $table->integer('sn')->comment('排序')->default(0);
            $table->string('group')->comment('群組');
            $table->string('name')->comment('名稱');
            $table->string('api')->comment('API路徑');
            $table->string('method')->comment('API方法');
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
        Schema::dropIfExists('apidoc_lists');
    }
}
