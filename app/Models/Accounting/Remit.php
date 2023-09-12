<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Remit extends Model
{
    use HasFactory;

    protected $fillable = [
        "stores_id",
        "user_id",
        "accounting_subject_id",
        "fk_id",
        "type",
        "price",
        "date",
        "note"
    ];

    /** 新增匯款資料
    *   type                  : 關聯類別
    *   fk_id                 : 關聯資料id
    *   accounting_subject_id : 關聯會計科目id
    *   date                  : 匯款日期
    *   price                 : 支票金額
    */
    public function createData($type, $id, $accounting_subject_id, $date, $price)
    {   
        return $this::create([
            "type"                 =>$type,
            "fk_id"                =>$id, 
            "accounting_subject_id"=>$accounting_subject_id, 
            "date"                 =>$date, 
            "price"                =>$price
        ]);
    }

    public function del($type, $id) {

        return $this::where([
            ['fk_id', '=', $id],
            ['type',  '=', $type],
        ])
        ->delete();
        
    }
}
