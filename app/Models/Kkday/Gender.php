<?php

namespace App\Models\kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Gender extends Model
{
    use HasFactory;
    protected $table = "kkday_genders";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
    ];
}
