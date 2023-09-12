<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'name',
        'link',
        'flag',
        'show',
        'user_id',
        'sort_by',
    ];
}
