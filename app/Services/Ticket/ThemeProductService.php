<?php

namespace App\Services\Ticket;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;

use App\Models\Ticket\TicketThemeProduct;

/**
 * Class ThemeProduct.
 */
class ThemeProductService extends Service
{

    use ResponseTrait;
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
            case "store":
                $rules = [
                    'theme_id' => 'required|integer',
                    'data' => 'required|array',
                    'data.*' => 'integer|exists:*.ticket_product_bases,prod_no',
                ];
                $data = $this->request->toArray();
                break;
            case "destroy":
                $rules = [
                    'prod_no' => 'required|integer',
                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        $prod_no = $this->request['data'];
        foreach($prod_no as $key => $value){
            $themeProduct = new TicketThemeProduct();
            $themeProduct->theme_id = $this->request['theme_id'];
            $themeProduct->prod_no = $value;
            $themeProduct->save();
        }

        self::setOk();
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;

        $themeProduct = TicketThemeProduct::where('theme_id', $this->dataId)
            ->where('prod_no', $this->request['prod_no'])
            ->first();

        $themeProduct->delete();

        self::setOk();
        return $this;
    }
}
