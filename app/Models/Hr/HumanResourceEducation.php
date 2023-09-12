<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceEducation extends Model
{
    use HasFactory;

    protected $table = 'human_resource_educations';

    protected $fillable = [
        'hr_id',
        'highest',
        'department',
    ];
}
