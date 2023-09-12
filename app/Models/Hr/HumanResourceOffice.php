<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceOffice extends Model
{
    use HasFactory;

    protected $table = 'human_resource_offices';

    protected $fillable = [
        'hr_id',
        'email',
        'tel',
        'extension',
        'fax',

    ];
}
