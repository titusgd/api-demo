<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class WeightUnits extends Model
{
    use HasFactory;

    protected $table = "kkday_weight_units";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
    ];
}
