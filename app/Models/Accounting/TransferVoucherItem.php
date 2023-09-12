<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class TransferVoucherItem extends Model
{
    use HasFactory;

    protected $hidden = ['transfer_voucher_id'];

    protected $fillable = [
        "id",
        "transfer_voucher_id",
        "accounting_subject_id",
        "debit_credit",
        "summary",
        "price",
        "note",
    ];

    public $validationRules = [
        '*.subject' =>'required|integer',
        '*.summary' =>'required',
        '*.price'   =>'required|regex:/^(-)?\d*(\.\d{1,2})?$/',
    ];

    public $validationMsg = [
        '*.subject.required' =>"01 subject",
        '*.subject.integer'  =>"01 subject",
        '*.summary.required' =>"01 summary",
        '*.price.required'   =>"01 price",
        '*.price.regex'      =>"01 price",
    ];

    public function createData($insert_id, $item)
    {   
        foreach ( $item as $k => $v ) {
            $type = ( $v['type'] == 1 ) ? 1 : 2;
            $create = $this::create([
                "transfer_voucher_id"  =>$insert_id,
                "accounting_subject_id"=>$v['subject'],
                "debit_credit"         =>$type,
                "summary"              =>$v['summary'],
                "price"                =>$v['price'],
                "note"                 =>$v['note']
            ]); 
        }         

    }

    public function updateData($id, $data)
    {   
        
        // 刪除細項
        $item_id = array_column($data, "id");
        $res=$this::where('transfer_voucher_id',$id)
        ->whereNotIn('id',$item_id)
        ->delete();

        // 更新細項
        foreach( $data as $k => $v ) {
            $type = ( $v['type'] == 1 ) ? 1 : 2;
            $payment_item = $this::find($v['id']);
            $payment_item->accounting_subject_id = $v['subject'];
            $payment_item->summary               = $v['summary'];
            $payment_item->price                 = $v['price'];
            $payment_item->note                  = $v['note'];
            $payment_item->debit_credit          = $type;
            $payment_item->save();
        }
    
    }

    // 取付總金額
    public function getPrice($id, $type)
    {   
        switch ( $type ) {
            case 1 :  // 借方總金額
                $results = $this::
                select(DB::raw("sum(price) as price"))
                ->where([
                    ['transfer_voucher_id','=',$id],
                    ['debit_credit','=',1],
                ])
                ->first();
                break;
            case 2 : // 貸方總金額
                $results = $this::
                select(DB::raw("sum(price) as price"))
                ->where([
                    ['transfer_voucher_id','=',$id],
                    ['debit_credit','=',2],
                ])
                ->first();
                break;
            default :
                $results = $this::
                select(DB::raw("sum((case when debit_credit = 1 then price else price*-1 end)) as price"))
                ->where('transfer_voucher_id','=',$id)
                ->first();
                break;
        }

        return intval($results->price);
    }

    public function getTransferItem($id)
    {
        return $this::select(
            "id",
            "summary",
            DB::raw('0+cast(price as char) as price'),
            DB::raw('case debit_credit when 1 then 1 else 0 end as type'),
            "note",
        )
        ->with([
            'subject',
        ])
        ->where([
            ['transfer_voucher_id','=', $id],
        ])
        ->orderBy('debit_credit')
        ->get()
        ->toArray();
    }

    public function subject()
    {
        return $this->belongsToMany('App\Models\AccountingSubject', 'transfer_voucher_items', 'id', 'accounting_subject_id')->select('accounting_subjects.id','code','name');
    }

    public function del($id) {

        return $this::where([
            ['transfer_voucher_id', '=', $id]
        ])
        ->delete();
        
    }
    
}
