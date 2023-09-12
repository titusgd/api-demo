<?php

namespace App\Models\Kkday\CatKey;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Main extends Model
{
    use HasFactory;

    protected $table = 'kkday_cat_main_keys';

    protected $fillable = [
        'name',
        'code',
        'sort', 
        'use'
    ];
    protected $casts = [
        'use' => 'boolean', // 將 use 欄位轉換為 Boolean
    ];
    public function sub_key_list()
    {
        return $this->hasMany('App\Models\Kkday\CatKey\CatSubKey', 'main_id', 'id');
    }
}
