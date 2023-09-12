<?php

namespace App\Http\Controllers\Administration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Administrations\SheduleService;
use App\Services\Administrations\SheduleService as ScheduleService;
// use App\Models\shedule;
use App\Traits\ExecutionTimeTrait;

class SheduleController extends Controller
{
    use ExecutionTimeTrait;
    public function list(Request $request)
    {
        // self::timeStart();
        $service = (new ScheduleService())->validList($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        // dd(self::timeEnd()->getExecutionTime());
        return $service->createList($request)->getResponse();

        // // ---------- old ----------
        // $serv = new SheduleService;
        // $vali = $serv->validList($request);
        // return ($vali) ? $vali : $serv->getList($request);
    }

    public function update(Request $request)
    {
        // 資料驗證
        $service = (new SheduleService())->validUpdate($request->all());
        if (!empty($service->getResponse())) return $service->getResponse();

        // $serv = new SheduleService;
        // $vali = $serv->validUpdate($request->all());
        // if ($vali) return $vali;
        // exit();
        // 新增、更新判斷
        $id = $service->checkData($request);
        (!$service->checkData($request)) ? $service->addData($request) : $service->updateData($request, $id);
        return $service->getResponse();
        // $response = 
        // return (!$service->checkData($request)) ? $service->addData($request) : $service->updateData($request, $id)->getResponse();
        // ---------- old ----------
        // // 資料驗證
        // $serv = new SheduleService;
        // $vali = $serv->validUpdate($request->all());
        // if ($vali) return $vali;
        // // exit();
        // // 新增、更新判斷
        // $id = $serv->checkData($request);
        // return (!$serv->checkData($request)) ? $serv->addData($request) : $serv->updateData($request, $id);
    }

    public function person(Request $request)
    {
        $service = (new SheduleService())->validPerson($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->personList($request->year, $request->month)->getResponse();
        // ---------- old ----------
        // $serv = new SheduleService;
        // $vali = $serv->validPerson($request);
        // if ($vali) return $vali;
        // $person_list = $serv->getPersonList($request->year,$request->month);
        // return $person_list;
        // ---------- old ----------
        // $serv = new SheduleService;
        // $vali = $serv->validPerson($request);
        // if ($vali) return $vali;
        // $person_list = $serv->getPersonList($request->year,$request->month);
        // return $person_list;
    }
}
