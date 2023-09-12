<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'stores_id',
        'user_id',
        'auditor_id',
        'code',
        'status',
        'note',
    ];
}
