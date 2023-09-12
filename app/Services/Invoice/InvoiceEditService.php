<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\TravelInvoice;;

/**
 * Class InvoiceIssueService.
 */
class InvoiceEditService extends InvoiceService
{

    use RulesTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'invoice_number' => 'required|string|max:9',
                    'category' => 'required|in:"B2B","B2C"',
                    'buyer_name' => 'string|max:50|required_if:category,B2B',
                    'buyer_ubn' => 'string|max:8|required_if:category,B2B',
                    'buyer_address' => 'string|max:100',
                    'buyer_mobile_phone' => 'string|max:20',
                    'tour_name' => 'string|max:50',
                    'tour_no' => 'string|max:20',
                    'tour_date' => 'date',
                    'tax_noted' => 'integer',
                    'comment' => 'string|max:50',

                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);

        return $this;
    }

    public function store()
    {

        if (!empty($this->response)) return $this;

        $cond_array = $this->request->toArray();
        $req_result = $this->arrayKeySnakeToBigHump($cond_array);
        $req_result['Version'] = '1.0';
        $req_result['TimeStamp'] = time();

        // 取得原始資料
        $travelInvoice = TravelInvoice::where('invoice_number', $cond_array['invoice_number'])->first();

        $thisId = $travelInvoice->id;

        $result = (new InvoiceService())->invoiceMake($req_result, 'invoice_edit');
        $result = $result->getContent();
        $result = json_decode($result, true);

        if (!empty($result['data']['Status'])) {
            if ($result['data']['Status'] == 'SUCCESS') {
                // 更新資料
                $travel_array = $result['data'];
                $travelInvoice = TravelInvoice::find($thisId);
                $travelInvoice->invoice_number = $travel_array['InvoiceNumber'];
                if(!empty($travel_array['BuyerName'])){
                    $travelInvoice->buyer_name = $travel_array['BuyerName'];
                }
                if(!empty($travel_array['BuyerUBN'])){
                    $travelInvoice->buyer_ubn = $travel_array['BuyerUBN'];
                }
                if(!empty($travel_array['BuyerAddress'])){
                    $travelInvoice->buyer_address = $travel_array['BuyerAddress'];
                }
                if(!empty($travel_array['BuyerMobilePhone'])){
                    $travelInvoice->buyer_phone = $travel_array['BuyerMobilePhone'];
                }
                if(!empty($travel_array['TourName'])){
                    $travelInvoice->tour_name = $travel_array['TourName'];
                }
                if(!empty($travel_array['TourNo'])){
                    $travelInvoice->tour_no = $travel_array['TourNo'];
                }
                if(!empty($travel_array['TourDate'])){
                    $travelInvoice->tour_date = $travel_array['TourDate'];
                }
                if(!empty($travel_array['TaxNoted'])){
                    $travelInvoice->tax_noted = $travel_array['TaxNoted'];
                }
                if(!empty($travel_array['Comment'])){
                    $travelInvoice->comment = $travel_array['Comment'];
                }
               $travelInvoice->check_code = $travel_array['CheckCode'];
                $travelInvoice->save();
            }
        }

        $this->response = InvoiceService::response($result['code'], $result['msg'], $result['data']);

        return $this;
    }

    public function getResponse(): object
    {
        return $this->response;
    }
    public function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }
}
