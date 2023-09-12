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
        Schema::connection($this->connection)->create('human_resource_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->string('name')->nullable()->comment('姓名');
            $table->string('relation')->nullable()->comment('關係');
            $table->string('mobile')->nullable()->comment('手機');
            $table->string('tel')->nullable()->comment('電話');
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
        Schema::dropIfExists('human_resource_emergency_contacts');
    }
};
