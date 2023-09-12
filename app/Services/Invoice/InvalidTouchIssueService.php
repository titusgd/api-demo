<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\Invoice;

/**
 * Class InvoiceIssueService.
 */
class InvalidTouchIssueService extends InvoiceService
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
                    'invalid_status' => 'required|integer|in:1,2',
                    'invalid_no' => 'required|string|max:20',
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

        $result = (new InvoiceService())->invoiceMake($req_result, 'invalid_touch_issue');
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
