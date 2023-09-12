<?php

namespace App\Http\Controllers\Administration;

use App\Models\Administration\LeaveType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Administrations\LeaveTypeService;

class LeaveTypeController extends Controller
{
    public function add(Request $request)
    {
        return (new LeaveTypeService($request))
            ->runValidate('add')
            ->createLeaveType()
            ->getResponse();
    }

    public function update(Request $request)
    {
        return (new LeaveTypeService($request, $request['id']))
            ->runValidate('update')
            ->update()
            ->getResponse();

        // $model = new LeaveType;

        // $valid = $model->validUpdate($request);
        // if ($valid) return $valid;

        // return $model->updateLeaveType(
        //     $request->id,
        //     $request->name,
        //     $request->days,
        //     $request->min,
        //     $request->directions,
        // );
    }

    public function list(Request $request)
    {
        return (new LeaveTypeService($request))
            ->list()
            ->getResponse();
    }

    public function sort(Request $request)
    {

        return (new LeaveTypeService($request))
            ->runValidate('sort')
            ->sort()
            ->getResponse();
        // $model = new LeaveType;

        // $valid = $model->validSort($request);

        // if ($valid) return $valid;

        // $sort = $model->setSort($request);

        // return $sort;
    }

    public function use(Request $request)
    {
        return (new LeaveTypeService($request,$request['id']))
            ->runValidate('use')
            ->use()
            ->getResponse();
        // $models = new LeaveType;
        // $validUse = $models->validUse($request);
        // if ($validUse) return $validUse;
        // $leave_type_update_use = $models->updateUse($request->id, $request->use);
        // return $leave_type_update_use;
    }
}
