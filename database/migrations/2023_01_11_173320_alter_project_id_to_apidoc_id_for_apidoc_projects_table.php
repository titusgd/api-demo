<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectIdToApidocIdForApidocProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apidoc_projects', function (Blueprint $table) {
            $table->renameColumn('project_id', 'apidoc_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apidoc_projects', function (Blueprint $table) {
            //
        });
    }
}
