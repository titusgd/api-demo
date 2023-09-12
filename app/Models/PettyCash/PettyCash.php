<?php

namespace App\Models\PettyCash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PettyCash extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable =[
        "id",
        "auditor_id",
        "proposal_id",
        "user_id",
        "price",
        "status",
        "store_id"
    ];

    
}
