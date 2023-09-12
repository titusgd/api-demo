<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'title',
        'content',
    ];

    public function selectData(array $column = [], array $where = []): object
    {
        $query = $this;
        return $this;
    }

    private function addWhere(object $query, array $where): object
    {
        foreach ($where as $key => $val) {
            switch ($key) {
                case 'where':
                    break;
                case 'whereIn':
                    break;
                case 'whereNotIn':
                    break;
                case 'orderBy':
                    break;
            }
        }
        return $query;
    }

    public function application_items()
    {
        return $this->hasMany('App\Models\Administration\ApplicationItem');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Administration\Review', 'fk_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\Account\User');
    }

    // public function store()
    // {
    //     return $this->belongsTo('App\Models\store');
    // }
}
