<?php

namespace App\Models\Cafe\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

/**
 * Cafe/Menu/Item 咖啡菜單項目Model.用於紀錄菜單項目資料
 * @property int $menu_group_id 菜單群組id
 * @property int $menu_folder_id 資料夾id
 * @property string $name 菜單名稱
 * @property float $price 價格
 * @property int $image_id  圖片id
 * @property bool $use  使用狀態
 * @property int $accounting_subject_id 會計科目id
 * @property int $sort 排序編號
 */
class Item extends Model
{
    use HasFactory;
    /**
     * table
     * 資料表名稱 
     */
    protected $table = 'menu_items';
    protected $fillable = [
        'menu_group_id',
        'menu_folder_id',
        'name',
        'price',
        'image_id',
        'use',
        'accounting_subject_id'
    ];
    /**
     * attributes 
     * 屬性預設參數
     * @var array
     */
    protected $attributes = [
        'sort' => 0,
        'image_id' => 0,
    ];

    protected $casts = [
        'use'=>'boolean',
    //     'price'=>'decimal:2',
    //     'created_at' => 'datetime:Y/m/d H:i:s',
    //     'updated_at' => 'datetime:Y/m/d H:i:s',
    ];
}
