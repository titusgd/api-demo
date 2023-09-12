<?php

namespace App\Models\kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ProductClass extends Model
{
    use HasFactory;
    protected $table = "kkday_product_classes";

    protected $fillable = [
        'type',
        'description_ch',
        'description_en',
    ];
}
