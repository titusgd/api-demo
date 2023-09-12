<?php

namespace App\Models\kkday\CatKey;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class CatSubKey extends Model
{
    use HasFactory;

    protected $table = "kkday_cat_sub_keys";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
        'main_id',
        'sort',
        'use'
    ];
    protected $casts = [
        'use' => 'boolean', // 將 use 欄位轉換為 Boolean
    ];
}
