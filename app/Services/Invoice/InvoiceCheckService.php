<?php

namespace App\Services\Invoice;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

use App\Services\Service;
use App\Models\TravelInvoice;

/**
 * Class InvoiceCheckService.
 */
class InvoiceCheckService extends Service
{

    private $merchantId;
    private $hashKey;
    private $hashIV;
    private $url;
    private $cond;

    public function __construct()
    {

        $this->merchantId = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_MERCHANT_ID_DEV') : config('travelinvoice.INVOICE_MERCHANT_ID');
        $this->hashKey = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_HASH_KEY_DEV') : config('travelinvoice.INVOICE_HASH_KEY');
        $this->hashIV = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_HASH_IV_DEV') : config('travelinvoice.INVOICE_HASH_IV');
        $this->url = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_URL_DEV') : config('travelinvoice.INVOICE_URL');

        $this->cond = [
            'StartDate' => Carbon::today()->format('Y-m-d'),
            'EndDate' => Carbon::today()->addDays(90)->format('Y-m-d'),
            'SearchType' => '1',
            'Version' => '2.0',
            'TimeStamp' => time(),
        ];
    }

    /**
     * 檢查收據
     */
    public function check(): void
    {

        $travelInvoice = TravelInvoice::where('create_status_time', Carbon::today()->format('Y-m-d'))->get();
        Log::info('=== Invoice check start ===');

        foreach ($travelInvoice as $key => $value) {
            Log::info('== ' . $value['merchant_order_no'] . ' : '  . $this->cond['StartDate'] . ' == start ==');

            if (!$value['invoice_number']) {
                $condArray = $this->cond;
                $condArray['MerchantOrderNo'] = $value['merchant_order_no'];

                $invoice = $this->invoiceMaker($condArray, 'invoice_searchall');
                $invoice = json_decode($invoice, true);

                if ($invoice['Status'] === 'SUCCESS') {
                    TravelInvoice::where('merchant_order_no', $value['merchant_order_no'])
                        ->update([
                            'invoice_number' => $invoice['ReturnInvoice'][0]['InvoiceNumber'],
                            'random_num' => $invoice['ReturnInvoice'][0]['RandomNum'],
                            'buyer_name' => $invoice['ReturnInvoice'][0]['BuyerName'],
                            'buyer_ubn' => $invoice['ReturnInvoice'][0]['BuyerUBN'],
                        ]);
                    Log::info('=== ' . $invoice['ReturnInvoice'][0]['InvoiceNumber'] . ' == success ==');
                } else {
                    Log::info('=== ' . $invoice['Status'] . ' == failed ==');
                }
            } else {
                Log::info($value['merchant_order_no'] . ' Invoice Number: ' . $value['invoice_number'] . ' already exists');
            }
        }

        Log::info('=== Invoice end ===');
    }

    /**
     * 開立發票
     *
     * @param array  $postDataArray
     * @param string $uri
     * @param int    $searchType
     *
     * @return string
     */
    public function invoiceMaker(array $postDataArray, string $uri, int $searchType = 0): string
    {
        $postDataStr = http_build_query($postDataArray); // 轉成字串排列
        $key = $this->hashKey; // 旅行社專屬串接金鑰 HashKey 值
        $iv = $this->hashIV; // 旅行社專屬串接金鑰 HashIV 值
        $url = $this->url . '/' . $uri;
        $merchantId = $this->merchantId; // 旅行社統一編號

        // post 資料以 aes-256-cbc 方式加密
        $post_data = bin2hex(openssl_encrypt(
            $this->addPadding($postDataStr),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        ));

        $transactionDataArray = [
            'MerchantID_' => $merchantId,
            'PostData_' => $post_data,
        ];

        // 查詢要用到的參數
        if (!empty($searchType)) {
            $transactionDataArray['SearchType_'] = $searchType;
        }

        // 以 curl post 進行電子收據開立
        $result = $this->curlWork($url, $transactionDataArray);

        return $result;
    }

    /**
     * 填充 PKCS#7 標準
     *
     * @param string $string
     * @param int    $blockSize
     *
     * @return string
     */
    private function addPadding(string $string, int $blockSize = 32): string
    {
        $len = strlen($string);
        $pad = $blockSize - ($len % $blockSize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    /**
     * curl post
     *
     * @param string $url
     * @param array  $parameter
     *
     * @return string
     */
    function curlWork(string $url = "", array $parameter = []): string
    {

        $response = Http::asForm()->withOptions([
            'verify' => false,
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false,
        ])->post($url, $parameter);

        $result = $response->body();

        return $result;
    }

    // 如果我要預約開立收據成立
    public function touchInvoiceIssue()
    {

        $toDay = Carbon::today()->format('Y-m-d');
        $travelInvoice = TravelInvoice::where(function($query){
                $query->where('invoice_number', '')
                ->orWhereNull('invoice_number');
            })
            ->where('create_status_time', '<=', $toDay)
            ->get();

        Log::info('=== 手動收據 start ===');
        foreach ($travelInvoice as $key => $value) {

            // 先以查詢收據號碼
            $condSearchAll = [
                'Version' => '2.0',
                'TimeStamp' => time(),
                'MerchantOrderNo' => $value['merchant_order_no']
            ];

            $invoiceSearchAll = $this->invoiceMaker($condSearchAll, 'invoice_searchall', 4);
            $invoiceSearchAll = json_decode($invoiceSearchAll, true);
            Log::info('=== ' . $value['merchant_order_no'] . ' == start ==');
            Log::info('=== ' . $invoiceSearchAll['Status'] . ' == 狀態 IN ==');
            if($invoiceSearchAll['Status'] =='SUCCESS'){
                Log::info('=== ' . $invoiceSearchAll['Status'] . ' == 狀態 InIng ==');
                $returnInvoice = $invoiceSearchAll['ReturnInvoice'][0];
                if(!$returnInvoice['InvoiceNumber']){

                    $condTouch = [
                        'Version' => '1.0',
                        'TimeStamp' => time(),
                        'InvoiceID' => $returnInvoice['InvoiceTransNo'],
                        'MerchantOrderNo' => $returnInvoice['MerchantOrderNo'],
                        'TotalAmt' => $returnInvoice['TotalAmt'],
                        'Status' => '1'
                    ];

                    $invoiceIssue = $this->invoiceMaker($condTouch, 'invoice_touch_issue');
                    $invoiceIssue = parse_str($invoiceIssue, $touchInvoiceIssue);

                    // 更新收據號碼
                    if($touchInvoiceIssue['Status'] == 'SUCCESS'){
                        Log::info('=== ' . $touchInvoiceIssue['InvoiceNumber'] . ' == invoice number ==');
                        $travelInvoice = TravelInvoice::where('merchant_order_no', $returnInvoice['MerchantOrderNo'])
                                                        ->update([
                                                            'invoice_number' => $touchInvoiceIssue['InvoiceNumber'],
                                                            'random_num' => $touchInvoiceIssue['RandomNum'],
                                                        ]);
                    }
                } else {
                    Log::info('=== ' . $returnInvoice['InvoiceNumber'] . ' == invoice number 寫入 ==');
                    TravelInvoice::where('merchant_order_no', $returnInvoice['MerchantOrderNo'])
                        ->update([
                            'invoice_number' => $returnInvoice['InvoiceNumber']
                        ]);
                }
            }
        }
        Log::info('=== 手動收據 end ===');
    }
}
