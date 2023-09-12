<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ErrorReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'describe',
        'os',
        'browser',
        'error_url',
        'error_image',
        'size',
        'status',
        'user_id',
        'programmer_id',
    ];
}
