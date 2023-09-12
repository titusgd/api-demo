<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'fk_id',
        'user_id',
        'level',
        'status',
        'note',
        'date',
        'rank'
    ];

    public function selectData(array $columns, array $where): object
    {
        return $this;
    }

    private function addWhere(array $where)
    {
    }


    // 付款/收款專用
    public function getReviewUser($type, $id)
    {
        $results = $this::select(
            'user_id as id',
            DB::raw("(select `name` from `users` where id = reviews.user_id) as name"),
            'rank',
            'status as audit',
            'date',
            'note as reason'
        )->where([
            ['fk_id', '=', $id],
            ['type',  '=', $type],
        ])
            ->orderBy('rank', 'desc')
            ->get()
            ->toArray();

        $results[2] = $results[1];
        $results[1] = $results[0];
        $results[0]['rank'] = '承辦人';
        $results[1]['rank'] = '會計';
        $results[2]['rank'] = '財務主管';

        return $results;
    }

    public function checkReview($type, $id)
    {
        return $this::select(
            'id'
        )->where([
            ['fk_id', '=', $id],
            ['type',  '=', $type],
        ])
            ->whereNotNull('date')
            ->orderBy('rank', 'desc')
            ->first();
    }

    public function del($type, $id)
    {

        return $this::where([
            ['fk_id', '=', $id],
            ['type',  '=', $type],
        ])
            ->delete();
    }
}
