<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kkday_cat_sub_keys', function (Blueprint $table) {
            $table->integer('main_id')->after('id')->comment('主分類ID');
            $table->integer('sort')->after('type')->comment('排序');
        });
    }

    public function down()
    {
        Schema::table('kkday_cat_sub_keys', function (Blueprint $table) {
            //
        });
    }
};
