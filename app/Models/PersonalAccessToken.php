<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tokenable_id',
        'last_used_at',
        'created_at',
        'updated_at',
    ];

}
