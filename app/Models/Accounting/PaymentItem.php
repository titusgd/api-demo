<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class PaymentItem extends Model
{
    use HasFactory;

    protected $hidden = [
        'payment_id'
    ];

    protected $fillable = [
        "id",
        "payment_id",
        "accounting_subject_id",
        "transfer_voucher_id",
        "currency_id",
        "pay_type",
        "debit_credit",
        "summary",
        "qty",
        "price",
        "exchange_rate",
        "pay_date",
        "note",
    ];

    public $validationRules = [
        '*.subject' =>'required|integer',
        '*.summary' =>'required',
        '*.qty'     =>'required|regex:/^(-)?\d*(\.\d{1,1})?$/',
        '*.price'   =>'required|regex:/^(-)?\d*(\.\d{1,2})?$/',
    ];

    public $validationMsg = [
        '*.subject.required' =>"01 subject",
        '*.subject.integer'  =>"01 subject",
        '*.summary.required' =>"01 summary",
        '*.qty.required'     =>"01 qty",
        '*.qty.regex'        =>"01 qty",
        '*.price.required'   =>"01 price",
        '*.price.regex'      =>"01 price",
    ];

    // 付款驗證用 ---------------------------------------------------------
    public $validationRulesPay = [
        'subject' =>'required|integer',
        'payment' =>'required',
        'summary' =>'required',
        'price'   =>'required|regex:/^(-)?\d*(\.\d{1,2})?$/',
        'date'    =>'required|date'
    ];

    public $validationMsgPay = [
        'subject.required' =>"01 subject",
        'subject.integer'  =>"01 subject",
        'payment.required' =>"01 payment",
        'summary.required' =>"01 summary",
        'price.required'   =>"01 price",
        'price.regex'      =>"01 price",
        'date.required'    =>"01 date",
        'date.date'        =>"01 date",
    ];
    // 付款驗證用 ---------------------------------------------------------

    // 支票驗證用 ---------------------------------------------------------
    public $validationRulesCheque = [
        'ticketNumber'  =>'required',
        'expirationDate'=>'required|date',
        'cashDate'      =>'required|date',
    ];

    public $validationMsgCheque = [
        'ticketNumber.required'   =>"01 ticketNumber",
        'expirationDate.required' =>"01 expirationDate",
        'expirationDate.date'     =>"01 expirationDate",
        'cashDate.required'       =>"01 cashDate",
        'cashDate.date'           =>"01 cashDate",
    ];
    // 支票驗證用 ---------------------------------------------------------

    // 取付款單總金額
    public function getPrice($id, $type)
    {   
        switch ( $type ) {
            case 1 :  // 借方總金額
                $results = $this::
                select(DB::raw("sum(price*qty) as price"))
                ->where([
                    ['payment_id','=',$id],
                    ['debit_credit','=',1],
                ])
                ->first();
                break;
            case 2 : // 貸方總金額
                $results = $this::
                select(DB::raw("sum(price*qty) as price"))
                ->where([
                    ['payment_id','=',$id],
                    ['debit_credit','=',2],
                ])
                ->first();
                break;
            default :
                $results = $this::
                select(DB::raw("sum((case when debit_credit = 1 then price*qty else price*qty*-1 end)) as price"))
                ->where('payment_id','=',$id)
                ->first();
                break;
        }

        return intval($results->price);
    }

    // 判斷付款金額
    public function checkPrice($id, $price)
    {        
        $a = $this->getPrice($id, 3);
        $b = intval($price);

        switch (true) {
            case ( $a == 0 ) :         //付款單金額為0
                return false;
                break;
            case ( $a < $b ) :         //付款金額大於付款單金額
                return false;
                break;
            default :
                return true;
                break;
        }
    }

    public function pay($req)
    {
        $debit_credit = ( $req['price'] >= 0 ) ? 2 : 1; // 若金額為正數(包含0)，則指定為貸方，反之為借方

        $paymentItem = [
            "payment_id"           =>$req['id'],
            "accounting_subject_id"=>$req['subject'],
            "pay_type"             =>$req['payment'],
            "debit_credit"         =>$debit_credit,
            "summary"              =>$req['summary'],
            "qty"                  =>1,
            "price"                =>abs( $req['price'] ),
            "received_date"        =>$req['date'],
            "note"                 =>$req['note'],
        ];

        return $this->create($paymentItem);
    }

    public function updateData($id, $data)
    {   
        
        // 刪除細項
        $item_id = array_column($data, "id");
        $res=$this::where('payment_id',$id)
        ->whereNotIn('id',$item_id)
        ->delete();

        // 更新細項
        foreach( $data as $k => $v ) {
            // 若金額為負數，則更改借貸類別，例如借方金額為負數則跳到貸方、貸方金額為負數則跳到借方，並將金額轉成正數
            $payment_item = $this::find($v['id']);
            $debit_credit = $payment_item->debit_credit;
            if ( $v['price'] < 0 ) {
                switch ($debit_credit) {
                    case 1 :
                        $debit_credit = 2;
                        break;
                    case 2 :
                        $debit_credit = 1;
                        break;
                }
            }
            
            $payment_item->accounting_subject_id = $v['subject'];
            $payment_item->summary               = $v['summary'];
            $payment_item->qty                   = $v['qty'];
            $payment_item->price                 = $v['price'];
            $payment_item->note                  = $v['note'];
            $payment_item->debit_credit          = $debit_credit;
            $payment_item->save();
        }
    
    }

    public function getPaymentItem($id, $type)
    {
        return $this::select(
            "id",
            "summary",
            DB::raw('0+cast(qty as char) as qty'),
            DB::raw('0+cast(price as char) as price'),
            DB::raw("0+cast((qty*price) as char) as total"),
            "note",
        )
        ->with([
            'subject',
        ])
        ->where([
            ['payment_id',   '=', $id],
            ['debit_credit', '=', $type]
        ])
        ->get()
        ->toArray();
    }

    public function subject()
    {
        return $this->belongsToMany('App\Models\AccountingSubject', 'payment_items', 'id', 'accounting_subject_id')->select('accounting_subjects.id','code','name');
    }

    public function status(){
        return $this->hasMany('App\Models\Administration\Review', 'fk_id', 'payment_id')
        ->select(
            'fk_id',
            'user_id as id',
            DB::raw("(select `name` from `users` where id = reviews.user_id) as name"),
            'rank',
            'status as audit',
            'date',
            'note as reason'
        )
        ->where('type','payment')
        ->orderBy('rank','desc');
    }

    public function del($id) {

        return $this::where([
            ['payment_id', '=', $id]
        ])
        ->delete();
        
    }
}
