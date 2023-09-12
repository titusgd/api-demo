<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ScheduleStartDate extends Model
{
    use HasFactory;

    protected $table = 'schedule_start_dates';
    protected $fillable = [
        'store_id',
        'start_date',
        'updater_id',
    ];

    protected function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}
