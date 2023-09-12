<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_reports', function (Blueprint $table) {
            $table->id();
            // 問題描述
            $table->text('describe')->comment('問題描述');
            // 作業系統
            $table->string('os',50)->comment('作業系統');
            // 瀏覽器種類
            $table->string('browser',50)->comment('瀏覽器');
            // 錯誤頁面 網址
            $table->text('error_url')->comment('頁面網址');
            
            // 錯誤頁面 截圖
            $table->text('error_image')->comment('頁面截圖');
            // 頁面尺寸
            $table->string('size',40)->comment('頁面尺寸');
            // 錯誤處理狀態
            $table
                ->string('status',1)
                ->default('1')
                ->comment("1.pending：未處理|2.processing：已處理|3.solved：處理中");
            
            $table
                ->integer('user_id')
                ->comment("使用者")
                ->nullable();
            $table
                ->integer('programmer_id')
                ->comment("除錯人員")
                ->nullable();
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
        Schema::dropIfExists('error_reports');
    }
}
