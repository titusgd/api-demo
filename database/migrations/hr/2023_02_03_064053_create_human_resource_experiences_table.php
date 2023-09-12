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
        Schema::connection($this->connection)->create('human_resource_experiences', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->integer('seniority')->default(0)->comment('年資');

            // 轉義 annualLeave
            $table->integer('annual_leave')->default(0)->comment('特修天數');

            // 轉義 startDate, endDate
            $table->date('start_date')->nullable()->comment('到職日');
            $table->date('end_date')->nullable()->comment('離職日');
            $table->text('introduction')->nullable()->comment('資歷簡介');
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
        Schema::dropIfExists('human_resource_experiences');
    }
};
