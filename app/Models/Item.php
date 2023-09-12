<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Item extends Model
{
    use HasFactory;
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $fillable = [
        'item_groups_id',
        'firms_id',
        'store_id',
        'user_id',
        'code',
        'name',
        'price',
        'unit',
        'stock',
        'note',
        'use_flag',
        'dealer_price',
        'moq',
        'mpq',
    ];

    public function item_collections()
    {
        return $this->hasOne(ItemC::class, 'item_id', 'id');
    }
}
