<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class SheduleDatetime extends Model
{
    use HasFactory;
    protected $fillable = [
        'shedule_id', 'date', 'time', 'time_total', 'store_id', 'user_id'
    ];
}
