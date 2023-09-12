<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceOther extends Model
{
    use HasFactory;

    protected $table = 'human_resource_others';

    protected $fillable = [
        'hr_id',
        'disability_identification',
        'sales_performance',
        'punch_in',
        'service_area_id',
        'service_area_code',
        'service_area_name',

    ];
}
