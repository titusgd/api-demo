<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class LeaveAnnual extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'take_office_day',
        'start',
        'end',
        'pai',
        'formula',
        'content',
        'version'
    ];
}
