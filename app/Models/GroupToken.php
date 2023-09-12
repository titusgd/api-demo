<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class GroupToken extends Model
{
    use HasFactory;

    protected $connection = 'group';
    protected $table = 'tokens';
    protected $fillable = [
        'token','expired'
    ];

}
