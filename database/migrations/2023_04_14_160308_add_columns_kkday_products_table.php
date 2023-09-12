<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kkday_products', function (Blueprint $table) {
            $table->string('purchase_type')->nullable();
            $table->string('purchase_date')->nullable();
            $table->string('earliest_sale_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kkday_products', function (Blueprint $table) {
            //
        });
    }
};
