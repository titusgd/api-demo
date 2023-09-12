<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    protected $connection = '*';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('human_resources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('編號');
            $table->string('chinese_name')->comment('名稱');
            $table->string('english_name')->nullable()->comment('英文名稱');
            $table->integer('rank_id')->default(0)->comment('職等');
            $table->integer('flag')->default(0)->comment('開啓狀態');
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
        Schema::dropIfExists('human_resources');
    }
};
