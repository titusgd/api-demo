<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeResjsonRepjsonToApidocApisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apidoc_apis', function (Blueprint $table) {
            $table->text('resjson')->change();
            $table->text('reqjson')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apidoc_apis', function (Blueprint $table) {
            //
        });
    }
}
