<?php

namespace App\Models\Cafe\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;
use App\Models\Cafe\Menu\Item;
class Folder extends Model
{
    use HasFactory;
    protected $table = "menu_folders";
    protected $fillable = [
        'menu_group_id',
        'name',
        'sort',
        'store_id',
        'editor_id'
    ];
    protected $attributes = [
        'sort' => 0,
        'store_id' => 0
    ];

    public function menuItems(){
        return $this->hasMany(Item::class,'menu_folder_id');
    }
}
