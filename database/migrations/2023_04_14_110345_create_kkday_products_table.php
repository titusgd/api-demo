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
        Schema::create('kkday_products', function (Blueprint $table) {
            $table->id();
            $table->integer('prod_no')->comment('產品編號');
            $table->integer('prod_url_no')->comment('產品 URL');
            $table->string('prod_name')->comment('產品名稱');
            $table->string('prod_type')->nullable();
            $table->integer("rating_count")->default(0);
            $table->decimal("avg_rating_star", 12,2)->default(0.00);
            $table->boolean("instant_booking")->default(false);
            $table->integer("order_count")->default(0);
            $table->integer("days")->default(0);
            $table->integer("hours")->default(0);
            $table->integer("duration")->default(0);
            $table->string("introduction")->nullable();
            $table->string("prod_img_url")->nullable();
            $table->decimal("b2c_price", 12, 2)->default(0.00);
            $table->decimal("b2b_price", 12, 2)->default(0.00);
            $table->string("prod_currency")->nullable();
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
        Schema::dropIfExists('kkday_products');
    }
};
