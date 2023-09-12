<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class firm extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'user_id',
        'stores_id',
        'gui_number',
        'firm',
        'address',
        'phone',
        'representative',
        'contact_name',
        'contact_phone',
    ];

}
