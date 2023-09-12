<?php

namespace App\Http\Controllers\Store;

use App\Services\Store\StoreInfoService;
use App\Http\Controllers\Controller;
use App\Models\store;
use Illuminate\Http\Request;


class StoreController extends Controller
{
    public function index()
    {
        return (new StoreInfoService())
            ->list()
            ->getResponse();
        // ---------- old ----------
        // $service = new StoreInfoService();
        // return $service->getList();
    }


    public function store(Request $request)
    {
        $service = new StoreInfoService();
        if ($request->id === null) {
            $service->validCreate($request)->getResponse();
            if(!empty($service->getResponse())) return $service->getResponse();
            return $service->create($request)->getResponse();
            // ----- old -----
            // $valid = $service->validCreate($request);
            // if ($valid) return $valid;
            // return $service->create($request);
        } else {
            $service->validUpdate($request)->getResponse();
            if(!empty($service->getResponse())) return $service->getResponse();
            return $service->create($request)->getResponse();

            // ----- old -----
            // $valid = $service->validUpdate($request);
            // if ($valid) return $valid;
            // return $service->update($request);
        }
    }


    public function show(store $store)
    {
        //
    }

    public function update(Request $request, store $store)
    {
    }


    public function destroy(store $store)
    {
        //
    }

    public function getList(Request $request)
    {
        $service = new StoreInfoService();
        return $service->list()->getResponse();
    }
}
