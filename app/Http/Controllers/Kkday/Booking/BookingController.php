<?php

namespace App\Http\Controllers\Kkday\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Kkday\QueryProductService;
use App\Services\Kkday\Booking\OrderService;
use App\Services\Kkday\Booking\BookingService;
use App\Services\Kkday\Booking\BookingCustomService;
use App\Services\Kkday\KkdayService;

class BookingController extends Controller
{

    public $prod_no;
    public $pkg_no;
    public $s_date;

    public function __construct(Request $request)
    {

        $this->prod_no = $request->get('prod_no');
        $this->pkg_no = $request->get('pkg_no');
        $this->s_date = $request->get('s_date');
    }

    public function index(Request $request)
    {

        $service = (new BookingService($request))->runValidate()->trafficValidate()->customValidate();
        if (!empty($service->getResponse())) return $service->getResponse();

    }

    public function order(Request $request){

        $bookingService = (new BookingService($request))->runValidate()->trafficValidate()->customValidate();
        if (!empty($bookingService->getResponse())) {
            $booking = $bookingService->getResponse();
            $booking = json_decode($booking->getContent(), true);
            if($booking['code'] == '00'){
                $orderService = (new OrderService())->getOrder($booking['data']);
                return $orderService;
            }
        }
    }


    public function parseCustom(Request $request)
    {

        $req = $request->all();

        $req_array = [
            "prod_no" => $req['prod_no'],
        ];

        $req = $req['custom'];
        $queryProduct = (new QueryProductService())->index($req_array);
        $res = $queryProduct->getContent();

        $booking_field = json_decode($res, true);
        $booking_field = $booking_field['data']['booking_field'];

        if (!empty($booking_field['custom'])) {
            $custom_field = (new BookingCustomService())->parse($req, $booking_field['custom']);
            return $custom_field;
        } else {
            return response()->json(['01', '', 'custom is empty']);
        }
    }
}
