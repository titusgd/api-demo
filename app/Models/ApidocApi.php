<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ApidocApi extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'req',
        'res',
        'reqjson',
        'resjson'
    ];
}
