<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class CityCode extends Model
{
    use HasFactory;
    protected $table = "kkday_citys";

    protected $fillable = [
        'id',
        "name",
        "code",
        "country_code",
        "country_id",
        'use'
    ];
    protected $casts = [
        'use' => 'boolean', // 將 use 欄位轉換為 Boolean
    ];
}
