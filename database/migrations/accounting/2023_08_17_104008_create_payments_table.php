<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection("*")->dropIfExists("payments");
        Schema::connection('*')->create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('stores_id')     ->comment("分店fk id")    ->default(0);
            $table->integer('user_id')       ->comment("新增者fk id")  ->default(0);
            $table->integer('object_id')     ->comment('付款對項fk id')->default(0);
            $table->string('object_type',20) ->comment('付款對項類別') ->nullable();
            $table->string('code',20)        ->comment('單號')        ->unique();
            $table->string('note',100)       ->comment('備註')        ->nullable();
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
        Schema::dropIfExists('payments');
    }
};
