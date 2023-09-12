<?php

namespace App\Services\Kkday\Booking;

use App\Services\Kkday\KkdayService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

use App\Services\Kkday\QueryProductService;
use App\Services\Kkday\Booking\BookingCustomService;
use App\Services\Kkday\Booking\Traffic\TrafficService;

/**
 * Class BookingService.
 */
class BookingService extends KkdayService
{

    use RulesTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;
    private $booking_filed, $booking_filed_columns;
    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
        $this->booking_filed = $this->getBookingFiledInfo($request);
    }

    public function runValidate()
    {

        $rules = [
            "item_no" => "integer|required",
            "prod_no" => "integer|required",
            "pkg_no" => "integer|required",
            "partner_order_no" => "string|required",
            "s_date" => "date|required",
            "e_date" => "date|required",
            "skus.*.sku_id" => "string|required",
            "skus.*.qty" => "integer|required",
            // "skus.*.price" => "decimal:1|required",
            "skus.*.price" => "numeric|required",
            "state" => "string",
            "buyer_first_name" => "string|required",
            "buyer_last_name" => "string|required",
            "buyer_country" => "string|required",
            "buyer_Email" => "string|required",
            "buyer_tel_country_code" => "string|required",
            "buyer_tel_number" => "string|required",
            "guide_lang" => "string|required",
            // "total_price" => "decimal:1|required",
            "total_price" => "numeric|required",
            "mobile_device.mobile_model_no" => "string",
            "mobile_device.IMEI" => "string",
            "mobile_device.active_date" => "date",
            "pay.type" => "string|required"
        ];
        (in_array('custom', $this->booking_filed_columns)) && $rules['custom'] = 'required';
        (in_array('traffic', $this->booking_filed_columns)) && $rules['traffic'] = 'required';

        $data = $this->request->toArray();
        $data['state'] = 'tw';

        $this->response = self::validate($data, $rules, $this->changeErrorName);

        return $this;
    }


    public function customValidate()
    {
        if (!empty($this->response)) return $this;
        $req = $this->request->toArray();
        $customService = (new BookingCustomService())->parse($req, $this->booking_filed);

        $this->response = $customService;

        return $this;
    }

    public function getResponse(): object
    {
        return $this->response;
    }

    public function trafficValidate()
    {
        if (!empty($this->response) || empty($this->booking_filed['traffic'])) return $this;
        // if (!empty($this->response)) return $this;
        $traffic_service = (new TrafficService(
            $this->request->toArray(),
            $this->booking_filed
        ));
        $this->response  = $traffic_service->runTrafficValidate();
        if (!empty($validation)) return $this;
        return $this;
    }

    private function getBookingFiledInfo($request): array
    {
        $req = $request->all();
        $req_array = [
            "prod_no" => $req['prod_no'],
        ];

        $data = $this->setParams($req)->callApi('post', 'v3/Product/QueryProduct')->getBody();
        (!empty($data['booking_field']['custom'])) && $this->booking_filed_columns[] = 'custom';
        (!empty($data['booking_field']['traffic'])) && $this->booking_filed_columns[] = 'traffic';

        return (!empty($data['booking_field'])) ? $data['booking_field'] : [];
    }
}
