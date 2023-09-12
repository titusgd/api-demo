<?php

namespace App\Services\Invoice;

use Illuminate\Support\Facades\Http;
use Exception;
use App\Services\Service;
use App\Traits\ArrayTrait;

/**
 * Class InvoiceService.
 */
class InvoiceService extends Service
{
    use ArrayTrait;
    protected $MerchantID;
    protected $HashKey;
    protected $HashIV;
    protected $url;
    protected $ver;

    public function __construct()
    {
        $this->MerchantID = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_MERCHANT_ID_DEV') : config('travelinvoice.INVOICE_MERCHANT_ID');
        $this->HashKey = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_HASH_KEY_DEV') : config('travelinvoice.INVOICE_HASH_KEY');
        $this->HashIV = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_HASH_IV_DEV') : config('travelinvoice.INVOICE_HASH_IV');
        $this->ver = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_VER_DEV') : config('travelinvoice.INVOICE_VER');
        $this->url = env('APP_ENV') == 'local' ? config('travelinvoice.INVOICE_URL_DEV') : config('travelinvoice.INVOICE_URL');
    }

    /**
     * 把要傳送的參數轉成字串排列
     * 並用 aes-256-cbc 的方式加密
     * 讓後端 API 確認這些都是合法的參數
     * 再以 curl 的方式去請求 API 來取得結果
     * @param mixed $post_data_array
     * @param string $uri
     * @param integer $searchType: 2:批次查詢折讓參數設定 3:批次查詢作廢單參數設定 4:自訂編號查詢收據參數設定
     */
    public function invoiceMake($post_data_array, $uri, $searchType = 0)
    {

        $post_data_str = http_build_query($post_data_array); // 轉成字串排列
        $key = $this->HashKey; // 旅行社專屬串接金鑰 HashKey 值
        $iv = $this->HashIV; // 旅行社專屬串接金鑰 HashIV 值
        $url = $this->url . '/' . $uri;
        $merchant_ID = $this->MerchantID; // 旅行社統一編號

        if (empty($key) || empty($iv)) {
            return Service::response('01', 'OK', 'HashKey or HashIV are required');
        }

        // post 資料以 aes-256-cbc 方式加密
        $post_data = bin2hex(openssl_encrypt(
            $this->add_padding($post_data_str),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        ));

        $transaction_data_array = array(
            'MerchantID_' => $merchant_ID,
            'PostData_' => $post_data
        );

        // 查詢要用到的參數
        if (!empty($searchType)) {
            $transaction_data_array['SearchType_'] = $searchType;
        }

        // 以 curl post 進行電子收據開立
        $result = $this->curl_work($url, $transaction_data_array);
        $result = parse_str($result, $result_array);
        if (!empty($result_array['Status'])) {
            if ($result_array['Status'] == 'SUCCESS' || strpos($result_array['Status'], 'SUCCESS')) {
                return Service::response('00', 'OK', $result_array);
            } else {
                return Service::response('01', '', $result_array['Status']);
            }
        } else {
            return Service::response('01', '', $result);
        }
    }

    // 以 PKCS#7 標準進行填充
    private function add_padding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    // curl post
    function curl_work($url = "", $parameter = "")
    {

        $response = Http::asForm()->withOptions([
            'verify' => false,
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false,
        ])->post($url, $parameter);

        $result = $response->body();
        return $result;
    }

    public function arrayKeySnakeToBigHump(array $snake_array): array
    {
        if (!empty($snake_array['invoice_id'])) {
            $snake_array['InvoiceID'] = $snake_array['invoice_id'];
            unset($snake_array['invoice_id']);
        }
        if (!empty($snake_array['buyer_ubn'])) {
            $snake_array['Buyer_UBN'] = $snake_array['buyer_ubn'];
            unset($snake_array['buyer_ubn']);
        }
        $result = $this->arrayKeySnakeToHump($snake_array);
        return $result;
    }
    public function arrayKeyBigHumpToSnake(array $snake_array): array
    {
        return $this->arrayKeyHumpToSnake($snake_array);
    }
    public function RunInvoiceMakeV2($post_data_array, $uri, $searchType = 0)
    {
        $post_data_str = http_build_query($post_data_array); // 轉成字串排列
        $key = $this->HashKey; // 旅行社專屬串接金鑰 HashKey 值
        $iv = $this->HashIV; // 旅行社專屬串接金鑰 HashIV 值
        $url = $this->url . '/' . $uri;
        $merchant_ID = $this->MerchantID; // 旅行社統一編號

        if (empty($key) || empty($iv)) return Service::response('01', 'OK', 'HashKey or HashIV are required');

        // post 資料以 aes-256-cbc 方式加密
        $post_data = bin2hex(openssl_encrypt(
            $this->add_padding($post_data_str),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        ));

        $transaction_data_array = array(
            'MerchantID_' => $merchant_ID,
            'PostData_' => $post_data
        );

        // 查詢要用到的參數
        (!empty($searchType)) && $transaction_data_array['SearchType_'] = $searchType;
        $res_data = $this->curl_work($url, $transaction_data_array);
        $json_data = json_decode($res_data,true);
        if(!is_array($json_data)){
            parse_str($res_data, $json_data);
        }
        return $json_data;
        // parse_str($response, $params);
        // return $this->curl_work($url, $transaction_data_array);
    }
}
