<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourcePosition extends Model
{
    use HasFactory;

    protected $table = 'human_resource_positions';

    protected $fillable = [
        'hr_id',
        'position_id',

    ];
}
