<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceInsurance extends Model
{
    use HasFactory;

    protected $table = 'human_resource_insurances';

    protected $fillable = [
        'hr_id',
        'change_enrollment',
        'change_withdrawal',
        'group_enrollment',
        'group_withdrawal',
        'labor_health_enrollment',
        'labor_health_withdrawal',
        'labor_amount',
        'labor_deductible',
        'labor_pensio',
        'health_amount',
        'health_deductible',
        'health_family',
        'appropriation_company',
        'appropriation_self',

    ];
}
