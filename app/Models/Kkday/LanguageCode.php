<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class LanguageCode extends Model
{
    use HasFactory;

    protected $table = "language_codes";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
    ];
}
