<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class WebPageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'web_page_id',
        'link',
        'type',
        'content',
        'sort_by',
        'user_id',
        'language',
    ];
}
