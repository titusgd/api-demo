<?php

namespace App\Models\Cafe\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Group extends Model
{
    use HasFactory;
    protected $table="menu_groups";
    protected $fillable = [
        'name',
        'editor_id',
        'sort',
    ];
}
