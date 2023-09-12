<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ScheduleWork extends Model
{
    use HasFactory;
    protected $table = 'schedule_works';

    protected $fillable = [
        'store_id',
        'staff_id',
        'year',
        'month',
        'day',
        'type',
        'note',
        'updater_id',
        'schedule_date'
    ];
}
