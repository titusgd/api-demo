<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('*')->create('stores', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')          ->comment("新增者")->default(0);
            $table->string('code')              ->comment('分店代碼');
            $table->string('store',20)          ->comment('分店名稱');
            $table->string('address',50)        ->comment('地址');
            $table->string('phone',15)          ->comment('電話');
            $table->string('representative',10) ->comment('負責人');
            $table->boolean('use_flag')         ->comment('營業旗標，1:正常營業 0:停業');
            $table->string('floor',30)          ->comment('樓層-樓上');
            $table->string('basement',30)       ->comment('樓層-地下室');
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
        Schema::dropIfExists('stores');
    }
}
