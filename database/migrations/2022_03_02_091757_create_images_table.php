<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string("image_name",40)->comment('顯示圖片名稱'); 
            $table->string("file_name",30)->comment("檔案名稱"); // 檔名 所屬類別+年月日時分秒+流水號
            $table->string("path",150)->comment("路徑");
            $table->string("url",150)->comment("網址");
            $table->integer("user_id")->comment("上傳人員");
            $table->string("extension",10)->comment("副檔名");
            $table->integer("fk_id")->comment("對應主表pk_id");
            $table->string("type",30)->comment("所屬類別"); // 菜單、系統錯誤...等
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
        Schema::dropIfExists('images');
    }
}
