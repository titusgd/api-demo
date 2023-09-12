<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class GoogleAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
    ];
}
