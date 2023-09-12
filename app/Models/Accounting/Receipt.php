<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        "stores_id",
        "user_id",
        "object_id",
        "object_type",
        "code",
        "note",
        "receive_date",
    ];

    public function updateData($req)
    {
        $payment = $this::find($req['id']);
        $payment->object_id = $req['store'];
        $payment->save();
    }

    public function getReceiptList($count, $req)
    {   
        //整理查詢條件
        $conditions = $this->conditions($req);

        $results = $this::select(
            "id",
            "code",
            "created_at as payDate",
            "receive_date as receiveDate",
            DB::raw("(select `store` from `stores` where `id` = `receipts`.`object_id`) as users"),
            DB::raw('"" as audit'),
        )
        ->with([
            'list',
        ])
        ->where($conditions[0])
        ->whereRaw($conditions[1])
        ->paginate($count)    
        ->toArray();


        // 製作page回傳格式
        $total      =$results['last_page'];    //總頁數
        $countTotal =$results['total'];        //總筆數
        $page       =$results['current_page'];  //當前頁次

        $pageinfo = [
            "total"     =>$total,      // 總頁數
            "countTotal"=>$countTotal, // 總筆數
            "page"      =>$page,       // 頁次
        ]; 
        
        return [$results['data'], $pageinfo];
    }

    public function list()
    {
        return $this->hasMany('App\Models\Accounting\ReceiptItem', 'receipt_id', 'id')
        ->select(
            "id",
            "receipt_id",
            DB::raw("(select `name` from `accounting_subjects` where `id` = `receipt_items`.`accounting_subject_id`) as `subject`"),
            "summary",
            DB::raw('0+cast(qty as char) as qty'),
            DB::raw('0+cast(price as char) as price'),
            "note",
            DB::raw("case debit_credit when 2 then 1 else 0 end as type")
        )
        ->orderBy('debit_credit','desc');
    }

    public function conditions($req) {

        $conditions = [];
        $conditionsRaw = '1=1';
        unset($req['count']);
        
        // 分店
        if ( isset($req['store']) && $req['store'] != 0 ) {
            array_push($conditions,['stores_id', '=', $req['store']]);
        }

        // 廠商
        if ( isset($req['firm']) && $req['firm'] != 0 ) {
            array_push($conditions,['object_id', '=', $req['firm']]);
        }

        // 日期
        if ( is_array($req['date']) ) {
            if ( isset($req['date']['start']) ) {
                array_push($conditions,['receipts.created_at', '>=', $req['date']['start'] . " 00:00:00"]);
            }
            if ( isset($req['date']['end']) ) {
                array_push($conditions,['receipts.created_at', '<=', $req['date']['end'] . " 23:59:59"]);
            }
        }

        // 摘要搜尋
        if ( isset($req['search']) && $req['search'] != '' ) {
            $conditionsRaw .= ' and EXISTS(SELECT id FROM `receipt_items` WHERE `receipt_id` =`receipts`.`id` and (`summary` regexp  \''. $req['search'] .'\' or `note` regexp  \''. $req['search'] .'\'))';
        }

        // 狀態
        switch ($req['audit']) {
            case 15 : // 已付款(借貸==0)
                $conditionsRaw .= ' and (SELECT sum((case `debit_credit` when 1 then 1 else -1 end)*(`qty`*`price`)) FROM `receipt_items` WHERE `receipt_id` =`receipts`.`id`) = 0';
                break;
            case 16 : // 未付款(借貸!=0)
                $conditionsRaw .= ' and (SELECT sum((case `debit_credit` when 1 then 1 else -1 end)*(`qty`*`price`)) FROM `receipt_items` WHERE `receipt_id` =`receipts`.`id`) <>  0';
                break;
            case 19 : // 已審核
                $conditionsRaw .= ' and EXISTS(SELECT `id` FROM `reviews` WHERE `id` =`receipts`.`id` and `type` = "receipt" and `date` is not null and `rank` = 2)';
                break;
            case 20 : // 未審核
                $conditionsRaw .= ' and EXISTS(SELECT `id` FROM `reviews` WHERE `id` =`receipts`.`id` and `type` = "receipt" and `date` is null and `rank` = 2)';
                break;
            case 21 : // 主管已審核
                $conditionsRaw .= ' and EXISTS(SELECT `id` FROM `reviews` WHERE `id` =`receipts`.`id` and `type` = "receipt" and `date` is not null and `rank` = 1)';
                break;
            case 22 : // 主管未審核
                $conditionsRaw .= ' and EXISTS(SELECT `id` FROM `reviews` WHERE `id` =`receipts`.`id` and `type` = "receipt" and `date` is null and `rank` = 1)';
                break;
        }

        // 應收帳款
        if ( isset($req['subject']) ) {
            if ( $req['subject'] == 0 ) {
                $conditionsRaw .= " and EXISTS(SELECT `id` FROM `receipt_items` WHERE `receipt_id` =`receipts`.`id` and `accounting_subject_id` in (select `id` from `accounting_subjects` where `subject_id` = 46))";
            } else {
                $conditionsRaw .= " and EXISTS(SELECT `id` FROM `receipt_items` WHERE `receipt_id` =`receipts`.`id` and `accounting_subject_id` = {$req['subject']})";
            }
        }

        return [$conditions,$conditionsRaw];
    }

    public function getReceiptDetail($id)
    {   
        // DB::enableQueryLog();
        $results = $this::select(
            "id",
            "code",
            "created_at as date",
            "receive_date as receiveDate",
        )
        ->with([
            'store',
            'firm',
        ])
        ->where('id','=',$id)
        ->get()
        ->toArray();
        
        // dd(DB::getQueryLog());
        return $results;
    }

    public function firm()
    {
        return $this->belongsToMany('App\Models\Store', 'receipts', 'id', 'object_id')->select('stores.id','store as name','phone', 'address');
    }

    public function store()
    {
        return $this->belongsToMany('App\Models\Store', 'receipts', 'id', 'stores_id')->select('stores.id','store as name','phone', 'address');
    }

    public function del($id) 
    {
        return $this::destroy($id);
    }
}
