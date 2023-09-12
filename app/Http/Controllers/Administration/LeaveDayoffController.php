<?php

namespace App\Http\Controllers\Administration;

use App\Models\Administration\LeaveDayoff;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Administrations\LeaveDayoffService;

class LeaveDayoffController extends Controller
{
    public function add(Request $request)
    {
        $service = new LeaveDayoffService();

        $check_user = $service->checkUserFlag();
        if($check_user) return $check_user;
        
        $valid = $service->validAdd($request);
        if ($valid) return $valid;
        
        $add_dayoff = $service->addLeaveDayoff($request);
        return $add_dayoff;
    }
    
    public function list(Request $request)
    {
        $service = new LeaveDayoffService();
        $valid = $service->validList($request);
        if ($valid) return $valid;

        $list = $service->getList($request->date,$request->count);
        return $list;
    }

    public function del(Request $request){
        $service = new LeaveDayoffService();
        $valid = $service->validDel($request);
        if ($valid) return $valid;
        
        $del = $service->delLeaveDayoff($request->id);
        return $del;
    }

    public function update(Request $request){

        $service = new LeaveDayoffService();
        $valid = $service->validUpdate($request);
        if ($valid) return $valid;
        
        $update = $service->updateLeaveDayoff($request);
        return $update;
    }
}
