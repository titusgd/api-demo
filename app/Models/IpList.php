<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class IpList extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'ip', 'note', 'user_id', 'created_at', 'updated_at'
    ];

    public function formate()
    {
        return [
            'id' => $this->id,
            'ip' => $this->ip,
            'note' => ($this->note) ?? ""
        ];
    }

}
