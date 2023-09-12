<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\Invoice;

/**
 * Class InvoiceIssueService.
 */
class AllowanceIssueService extends InvoiceService
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
                    'invoice_no' => 'required|string|max:9',
                    'item_name' => 'required|string|max:286',
                    'item_count' => 'required|string|max:41',
                    'item_unit' => 'required|string|max:20',
                    'item_price' => 'required|string|max:62',
                    'item_amt' => 'required|string|max:62',
                    'buyer_email' => 'string|max:100',
                    'seller_name' => 'required|string|max:50',
                    'total_amt' => 'required|integer',
                    'status' => 'required|integer|in:0,1',
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

        $req_result = $this->arrayKeySnakeToBigHump($this->request->toArray());
        $req_result['Version'] = "1.0";
        $req_result['TimeStamp'] = time();

        $result = (new InvoiceService())->invoiceMake($req_result, 'allowance_issue');
        $result = $result->getContent();
        $result = json_decode($result, true);

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
