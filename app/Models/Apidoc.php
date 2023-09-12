<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use App\Models\ConnModel as Model;
use App\Models\ConnModel as Model;

class Apidoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'link',
        'sn',
        'owner',
        'view',
        'edit'
    ];
}
