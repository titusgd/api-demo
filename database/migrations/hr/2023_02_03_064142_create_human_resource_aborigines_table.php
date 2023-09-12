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
        Schema::connection($this->connection)->create('human_resource_aborigines', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->boolean('type')->default(false)->comment('是否為原住民');
            $table->string('name')->nullable()->comment('名稱');
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
        Schema::dropIfExists('human_resource_aborigines');
    }
};
