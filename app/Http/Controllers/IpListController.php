<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\IpList;
use App\Services\IpListService;

class IpListController extends Controller
{
    public function add(Request $request)
    {
        $service = new IpListService();
        $valid = $service->validAdd($request);
        if ($valid)  return $valid;

        return $service->add($request->ip,$request->note);
    }

    public function update(Request $request)
    {
        $service = new IpListService();
        $valid = $service->validUpdate($request);
        if ($valid)  return $valid;

        $update = $service->update($request->id,$request->ip,$request->note);

        return $update;
    }
    
    public function list(Request $request)
    {
        $service = new IpListService();
        $valid = $service->validList($request);
        if ($valid)  return $valid;

        return $service->list($request->ip,$request->note);
    }

    public function del(Request $request)
    {
        $service = new IpListService();
        $valid = $service->validDel($request);
        if ($valid)  return $valid;

        return $service->del($request->ip);

    }
}
