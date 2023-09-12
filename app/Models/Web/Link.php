<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class link extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'link',
        'code',
        'user_id',
    ];
}
