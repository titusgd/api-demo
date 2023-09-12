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
        Schema::connection($this->connection)->create('human_resource_insurances', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');

            // 異動
            $table->date('change_enrollment')->nullable()->comment('異動加入日期');
            $table->date('change_withdrawal')->nullable()->comment('異動退出日期');

            // 團體保險
            $table->date('group_enrollment')->nullable()->comment('團體保險加入日期');
            $table->date('group_withdrawal')->nullable()->comment('團體保險退出日期');

            // 勞健保 轉義 laborHealth
            $table->date('labor_health_enrollment')->nullable()->comment('勞健保加入日期');
            $table->date('labor_health_withdrawal')->nullable()->comment('勞健保退出日期');

            // 勞保
            $table->decimal('labor_amount', 10, 2)->default(0)->comment('勞保金額');
            $table->decimal('labor_deductible', 10, 2)->default(0)->comment('勞保自付額');
            $table->decimal('labor_pensio', 10, 2)->default(0)->comment('勞保投保金額');

            // 健保
            $table->decimal('health_amount', 10, 2)->default(0)->comment('健保金額');
            $table->decimal('health_deductible', 10, 2)->default(0)->comment('健保自付額');
            $table->string('health_family')->comment('健保眷屬（人）');

            // 提撥比例
            $table->decimal('appropriation_company', 10, 2)->default(0)->comment('公司提撥');
            $table->decimal('appropriation_self', 10, 2)->default(0)->comment('自行提撥');

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
        Schema::dropIfExists('human_resource_insurances');
    }
};
