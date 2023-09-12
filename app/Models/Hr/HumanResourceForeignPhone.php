<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HumanResourceForeignPhone extends Model
{
    use HasFactory;

    protected $table = 'human_resource_foreign_phones';

    protected $fillable = [
        'hr_id',
        'country',
        'phone'
    ];
}
