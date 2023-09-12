<?php

namespace App\Services\Invoice;

use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Models\TravelInvoice;

/**
 * Class InvoiceIssueService.
 */
class SearchInvoiceService extends InvoiceService
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
        $travelInvoice = TravelInvoice::where($cond_array)->first();
        if(!$travelInvoice){

            $this->response =  InvoiceService::response('01', '', $cond_array['invoice_number'] . ' not found.');
            return $this;
        }

        $req_result = $this->arrayKeySnakeToBigHump($cond_array);
        $req_result['Version'] = "1.1";
        $req_result['TimeStamp'] = time();
        $req_result['RandomNum'] = $travelInvoice->random_num;

        $result = (new InvoiceService())->invoiceMake($req_result, 'invoice_search');
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
