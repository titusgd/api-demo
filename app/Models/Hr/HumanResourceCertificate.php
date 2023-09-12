<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HumanResourceCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'human_resource_certificates';

    protected $fillable = [
        'hr_id',
        'name',
        'number',
        'place',
        'note',
        'type_id',
        'type_name',
        'type_code',
        'expiry_sdate',
        'expiry_edate'
    ];
}
