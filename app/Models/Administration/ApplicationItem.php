<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ApplicationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'summary', 'qty', 'price'
    ];
}
