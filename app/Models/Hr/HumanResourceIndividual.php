<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceIndividual extends Model
{
    use HasFactory;

    protected $table = 'human_resource_individuals';

    protected $fillable = [
        'hr_id',
        'uid',
        'birthday',
        'blood_type',
        'phone',
        'contact_tel',
        'contact_address',
        'home_tel',
        'home_address',
        'family',
        'note',
    ];
}
