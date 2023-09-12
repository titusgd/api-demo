<?php

namespace App\Http\Controllers\Kkday\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\Booking\Traffic\TrafficService;
use App\Services\Kkday\NameTestService;
class TestController extends Controller
{
    //

    public function traffic(Request $request)
    {
        $service = (new TrafficService($request->toArray()));
        
        // ->getResData();
        // dd($service);
    }
}
