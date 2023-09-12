<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AirportTypeCode extends Model
{
    use HasFactory;

    protected $table = "kkdays_airport_type_codes";
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'id',
        'type',
        'description_ch',
        'description_en',
        'created_at',
        'updated_at',
    ];
}
