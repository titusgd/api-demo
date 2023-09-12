<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class State extends Model
{
    use HasFactory;

    protected $table = "kkday_states";

    protected $fillable = [
        "code", "name"
    ];
}
