<?php

namespace App\Http\Controllers\AbsenceReCord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\AbsenceReCord\AbsenceReCordService;

class AbsenceRecodeController extends Controller
{
    public function leaveRecord(Request $request)
    {
        $service = new AbsenceReCordService();
        $valid = $service->validLeaveRecord($request);
        if ($valid) return $valid;
        return $service->getList($request);
    }

    public function punchTimeCard(Request $request)
    {
        $service = new AbsenceReCordService();
        $valid = $service->validPunchTimeCard($request);
        if ($valid) return $valid;

        $data_list = $service->punchTimeCardList2($request->year, $request->month, $request->all);
        return $data_list;
    }
}
