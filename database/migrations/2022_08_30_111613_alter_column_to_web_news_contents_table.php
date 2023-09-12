<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnToWebNewsContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_news_contents', function (Blueprint $table) {
            $table->string('type',3)  ->nullable()->change();
            $table->string('link',50) ->nullable()->change();
            $table->text('content')   ->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_news_contents', function (Blueprint $table) {
            //
        });
    }
}
