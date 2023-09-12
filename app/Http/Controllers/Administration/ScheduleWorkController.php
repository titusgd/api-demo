<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Administrations\ScheduleWorkService as Service;

class ScheduleWorkController extends Controller
{
    public function addScheduleItem(Request $request)
    {
        return (new Service($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function list(Request $request)
    {
        $req = collect(json_decode($request->data), true);
        return (new Service($req))
            ->runValidate('list')
            ->list()
            ->getResponse();
    }
}
