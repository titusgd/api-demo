<?php

namespace App\Models\kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AppClass extends Model
{
    use HasFactory;
    protected $table = "kkday_app_classes";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
    ];
}
