<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceExperience extends Model
{
    use HasFactory;

    protected $table = 'human_resource_experiences';

    protected $fillable = [
        'hr_id',
        'seniority',
        'annual_leave',
        'start_date',
        'end_date',
        'introduction'
    ];
}
