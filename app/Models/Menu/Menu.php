<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'short',
        'code',
        'show',
        'menu_id',
        'user_group_id'
    ];

    public function menu()
    {
        return $this->hasMany(Menu::class, 'menu_id', 'id');
    }
}
