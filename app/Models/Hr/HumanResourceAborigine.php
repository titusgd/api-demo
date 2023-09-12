<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceAborigine extends Model
{
    use HasFactory;

    protected $table = 'human_resource_aborigines';

    protected $fillable = [
        'hr_id',
        'type',
        'name'
    ];
}
