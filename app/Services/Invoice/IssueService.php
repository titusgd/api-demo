<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\TravelInvoice;;

/**
 * Class InvoiceIssueService.
 */
class IssueService extends InvoiceService
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
                    'merchant_order_no' => 'required|string|max:30',
                    'category' => 'required|in:"B2B","B2C"',
                    'status' => 'required|integer|in:1,2',
                    'buyer_name' => 'string|max:50|required_if:category,B2B',
                    'buyer_ubn' => 'string|max:8|required_if:category,B2B',
                    'buyer_address' => 'string|max:100',
                    'buyer_email' => 'string|max:100',
                    'buyer_phone' => 'string|max:20',
                    'seller_name' => 'required|string|max:50',
                    'total_amt' => 'required|integer',
                    'email_lang' => 'integer',
                    'item_name' => 'required|string|max:286',
                    'item_count' => 'required|string|max:41',
                    'item_unit' => 'required|string|max:20',
                    'item_price' => 'required|string|max:62',
                    'item_amt' => 'required|string|max:62',
                    'tour_name' => 'string|max:50',
                    'tour_no' => 'string|max:20',
                    'tour_date' => 'date',
                    'tax_noted' => 'integer',
                    'comment' => 'string|max:50',
                    'create_status_time' => 'date|required_if:status,2',
                    'create_statusadd' => 'integer',

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
        $req_result['Version'] = '1.1';
        $req_result['TimeStamp'] = time();

        // 儲存
        $travelInvoice = TravelInvoice::create($cond_array);

        $thisId = $travelInvoice->id;

        $result = (new InvoiceService())->invoiceMake($req_result, 'invoice_issue');
        $result = $result->getContent();
        $result = json_decode($result, true);

        if (!empty($result['data']['Status'])) {
            if ($result['data']['Status'] == 'SUCCESS') {
                // 再次儲存
                $travel_array = $result['data'];
                $travelInvoice = TravelInvoice::find($thisId);
                $travelInvoice->invoice_number = $travel_array['InvoiceNumber'];
                $travelInvoice->invoice_trans_no = $travel_array['InvoiceTransNo'];
                $travelInvoice->random_num = $travel_array['RandomNum'];
                $travelInvoice->surplus = intval($travel_array['Surplus']);
                $travelInvoice->check_code = $travel_array['CheckCode'];
                $travelInvoice->display_url = $travel_array['DisplayURL'];
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
