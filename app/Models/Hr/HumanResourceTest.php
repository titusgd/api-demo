<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceTest extends Model
{
    use HasFactory;

    protected $table = 'human_resource_tests';

    protected $fillable = [
        'hr_id',
        'dominance',
        'influence',
        'caution',
        'steady',

    ];
}
