<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class WebHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'year',
        'month',
        'content',
        'user_id',
        'flag',
    ];
}
