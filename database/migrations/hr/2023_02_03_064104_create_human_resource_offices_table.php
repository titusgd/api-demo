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
        Schema::connection($this->connection)->create('human_resource_offices', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->string('email')->nullable()->comment('公司 Email');
            $table->string('tel')->nullable()->comment('公司電話');
            $table->string('extension')->nullable()->comment('公司分機');
            $table->string('fax')->nullable()->comment('公司傳真');
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
        Schema::dropIfExists('human_resource_offices');
    }
};
