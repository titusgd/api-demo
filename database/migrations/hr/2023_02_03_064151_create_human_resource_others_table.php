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
        Schema::connection($this->connection)->create('human_resource_others', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');

            // 轉義 disabilityIdentification
            $table->boolean('disability_identification')->default(false)->comment('身障手冊');

            // 轉義 salesPerformance
            $table->boolean('sales_performance')->default(false)->comment('業績統計');
            $table->boolean('punchIn')->default(false)->comment('打卡');
            $table->integer('service_area_id')->nullable()->comment('服務地區編號');
            $table->string('service_area_code')->nullable()->comment('服務地區代碼');
            $table->string('service_area_name')->nullable()->comment('服務地區名稱');

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
        Schema::dropIfExists('human_resource_others');
    }
};
