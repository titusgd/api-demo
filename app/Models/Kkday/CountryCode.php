<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class CountryCode extends Model
{
    use HasFactory;

    protected $table = "kkday_country_codes";

    protected $fillable = [
        'tel_area',
        'code',
        'name_ch',
        'use'
    ];
    protected $casts = [
        'use' => 'boolean', // 將 use 欄位轉換為 Boolean
    ];

    public function cityLists()
    {
        return $this->hasMany(CityCode::class, 'country_id', 'id');
    }
}
