<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        "stores_id",
        "user_id",
        "accounting_subject_id",
        "fk_id",
        "code",
        "type",
        "status",
        "price",
        "expiry_date",
        "cashed_date",
        "note"
    ];

    /** 新增支票資料
    *    type                  : 關聯類別
    *    fk_id                 : 關聯資料id
    *    accounting_subject_id : 關聯會計科目id
    *    code                  : 票號
    *    expiry_date           : 到期日
    *    cashed_date           : 兌現日
    *    price                 : 支票金額
    */
    public function createData($type,$id,$s_id,$code,$e_date,$c_date,$price)
    {
        return $this::create([
            "type"                 =>$type,
            "fk_id"                =>$id,
            "accounting_subject_id"=>$s_id,
            "code"                 =>$code,
            "expiry_date"          =>$e_date,
            "cashed_date"          =>$c_date,
            "price"                =>$price,
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
