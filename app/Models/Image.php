<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Image extends Model
{
    protected $fillable = [
        "image_name",
        "file_name",
        "path",
        "url",
        "user_id",
        "extension",
        "fk_id",
        "type",
        "created_at",
        "updated_at",
    ];
}
