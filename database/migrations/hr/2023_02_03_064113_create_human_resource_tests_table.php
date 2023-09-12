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
        Schema::connection($this->connection)->create('human_resource_tests', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            // 轉義 D, I, C, S
            $table->integer('dominance')->nullable()->comment('支配型');
            $table->integer('influence')->nullable()->comment('影響型');
            $table->integer('caution')->nullable()->comment('分析型');
            $table->integer('steady')->nullable()->comment('穩健型');
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
        Schema::dropIfExists('human_resource_tests');
    }
};
