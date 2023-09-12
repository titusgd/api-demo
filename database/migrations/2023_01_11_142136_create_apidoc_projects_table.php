<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApidocProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apidoc_projects', function (Blueprint $table) {
            $table->id();
            $table->integer('sn')->comment('排序');
            $table->string('name')->comment('名稱');
            $table->string('link')->comment('API連結');
            $table->integer('owner')->comment('擁有者');
            $table->json('view')->comment('瀏覽權限');
            $table->json('edit')->comment('編輯權限');
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
        Schema::dropIfExists('apidoc_projects');
    }
}
