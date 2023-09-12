<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ApidocProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'apidoc_id',
        'sn',
        'group',
        'name',
        'api',
        'method'
    ];
}
