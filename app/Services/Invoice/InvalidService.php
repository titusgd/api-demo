<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\Invoice;

/**
 * Class InvoiceIssueService.
 */
class InvalidService extends InvoiceService
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
                    'invalid_reason' => 'required|string|max:10',
                    'status' => 'required|integer|in:0,1',
                    'buyer_email' => 'string|max:100',
                    'seller_name' => 'required|string|max:50',
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

        $result = (new InvoiceService())->invoiceMake($req_result, 'invoice_invalid');
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
