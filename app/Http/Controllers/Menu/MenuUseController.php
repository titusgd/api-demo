<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Service;
use App\Services\Menu\MenuUseService;

class MenuUseController extends Controller
{
    public function index()
    {
        $service = new Service();
        return $service->response('999', 'ok');
    }
    public function store()
    {
        $service = new Service();
        return $service->response('999', 'ok');
    }
    public function show()
    {
        $service = new Service();
        return $service->response('999', 'ok');
    }

    public function update(Request $request, $use)
    {
        $service = (new MenuUseService($request->all(), $use))->validateUpdate($request, $use);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service
            ->update($request, $use)
            ->getResponse();

        // -----old -----
        // $service = new MenuUseService($request->all(),$use);
        // $valid = $service->validateUpdate($request,$use);
        // if ($valid) return $valid;
        // return $service->update($request,$use);
        // return $service->setUse();
    }

    public function destroy()
    {
        $service = new Service();
        return $service->response('999', 'ok');
    }
}
