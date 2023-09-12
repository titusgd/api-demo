<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HomeSliderShow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'link',
        'flag',
        'show',
        'user_id',
        'sort_by',
    ];
}
