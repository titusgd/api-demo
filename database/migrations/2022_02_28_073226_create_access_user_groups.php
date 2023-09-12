<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessUserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_user_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('user_groups_id')->comment("使用者群組");
            $table->integer('user_id');
            $table->integer('accesses_id')->comment("權限");
            $table->boolean('flag')->comment("使用狀態");
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
        Schema::dropIfExists('access_user_groups');
    }
}
