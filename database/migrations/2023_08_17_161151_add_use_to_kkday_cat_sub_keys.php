<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kkday_cat_sub_keys', function (Blueprint $table) {
            Schema::connection('*')->table('kkday_cat_sub_keys', function (Blueprint $table) {
                $table->boolean('use')->default(1)->comment('啟用狀態 0:關閉 1:啟用');
            });
        });
    }

    public function down()
    {
        Schema::table('kkday_cat_sub_keys', function (Blueprint $table) {
            //
        });
    }
};
