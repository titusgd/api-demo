<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceEmergencyContact extends Model
{
    use HasFactory;

    protected $table = 'human_resource_emergency_contacts';

    protected $fillable = [
        'hr_id',
        'name',
        'relation',
        'mobile',
        'tel'
    ];
}
