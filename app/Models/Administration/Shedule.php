<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Shedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'year', 'month', 'hour_max', 'hour_min', 'shift', 'store_id'
    ];
}
