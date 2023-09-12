<?php

namespace App\Http\Controllers\Menu;

use App\Models\Menu\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Menu\MenuService;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        return (new MenuService($request->all()))
            ->list()
            ->getResponse();
        // $service = new MenuService($request->all());
        // return $service -> list();
    }

    public function store(Request $request)
    {
        $service = (new MenuService($request->all()))->validateStore();
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service
            ->createMenu()
            ->setResponse($service->response('00', 'ok'))
            ->getResponse();

        // $service = new MenuService($request->all());
        // $valid = $service->validateStore();
        // if ($valid) return $valid;

        // $service->createMenu();
        // return $service->response('00', 'ok');
    }
    public function update(Request $request, $menu)
    {
        $service = (new MenuService($request->all(), $menu))->validateUpdate();
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service
            ->updateMenu()
            ->setResponse($service->response('00', 'ok'))
            ->getResponse();

        // $service = new MenuService($request->all(), $menu);
        // $valid = $service->validateUpdate();
        // if ($valid) return $valid;

        // $service->updateMenu();
        // return $service->response('00', 'ok');
    }

    public function destroy(Request $request, $menu)
    {
        $service = (new MenuService($request->all(), $menu))->validateDelete();
        if ($service->getResponse()) return $service->getResponse();
        return $service
            ->del()
            ->setResponse($service->response('00', 'ok'))
            ->getResponse();

        // $service = new MenuService($request->all(), $menu);
        // $valid = $service->validateDelete();
        // if ($valid) return $valid;

        // $service->del();
        // return $service->response('00', 'ok');
    }

    public function show(){}
}
