<?php

namespace App\Http\Controllers\Administration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Administrations\LeaveFormService;

class LeaveFormController extends Controller
{
    public function list(Request $request)
    {
        $service = new LeaveFormService();
        $valid = $service->validList($request);
        if ($valid) return $valid;
        $list = $service->getList($request);
        return $list;
    }

    public function audit(Request $request){
        $service = new LeaveFormService();
        $valid = $service->validAudit($request);
        if ($valid) return $valid;

        $audit = $service->audit($request);

        return $audit;
    }
}
