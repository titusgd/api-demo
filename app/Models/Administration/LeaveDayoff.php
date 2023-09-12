<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;
use App\Services\Service;

use App\Models\Administration\LeaveType;
use App\Models\Account\User;

use App\Traits\ReviewTrait;
use App\Traits\NotifyTrait;

class LeaveDayoff extends Model
{
    use HasFactory;
    use ReviewTrait, NotifyTrait;

    protected $fillable = [
        'user_id',
        'start',
        'end',
        'note',
        'leave_type_id',
        'total_hour',
        'number'
    ];

    public function reviews()
    {
        return $this->hasMany('App\Models\Administration\Review', 'fk_id', 'id');
    }

    public function leaveTypes()
    {
        return $this->belongsTo('App\Models\Administration\LeaveType');
    }
}
