<?php

namespace App\Services\Accounting;

use App\Services\Service;
use App\Models\Accounting\Invoice;

class InvoiceService extends Service
{

    private $type = [
        "1" => "已開立",
        "2" => "已作廢"
    ];

    /** create()
     *  發票資訊寫資料庫
     *  @param array $arr 發票寫入資料
     *  @return integer 發票PK id
     */
    public function create($arr)
    {
        $arr["type"] = $this->getTypeCode($arr["type"]);
        $invoice = Invoice::create($arr);
        return $invoice['id'];
    }

    /** checkInvoice()
     *  確認發票是否存在，存在true,不存在 false。
     *  @param stirng $str 發票號碼
     *  @return boolean
     */
    public function checkInvoice($str)
    {
        $invoices = Invoice::where("invoice", "=", $str)->get();
        $invoice_lenght = count($invoices);
        return ($invoice_lenght == 0) ? false : true;
    }

    /** getTypeCode()
     *  取得type代碼
     *  @param string $str 發票狀態
     *  @return integer
     */
    public function getTypeCode($str)
    {
        return array_search($str, $this->type);
    }
    /** getTypeStr()
     *  取得type 文字狀態
     *  @param integer $sum 發票狀態代碼
     *  @return string 
     */
    public function getTypeStr($sum)
    {
        return $this->type[$sum];
    }
}
