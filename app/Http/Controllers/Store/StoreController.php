<?php

namespace App\Http\Controllers\Store;

use App\Services\Store\StoreInfoService;
use App\Http\Controllers\Controller;
use App\Models\store;
use Illuminate\Http\Request;


class StoreController extends Controller
{
    public function index(Request $request)
    {
        return (new StoreInfoService($request))
            ->list()
            ->getResponse();
        // ---------- old ----------
        // $service = new StoreInfoService();
        // return $service->getList();
    }


    public function store(Request $request)
    {
        $service = new StoreInfoService($request);
        if($request->id === null){
            return $service->runValidate('store')->store()->getResponse();
        }else{
            
        }
        if ($request->id === null) {
            return $service->runValidate('store')->store()->getResponse();
            // $service->validCreate($request)->getResponse();
            // if (!empty($service->getResponse())) return $service->getResponse();
            // return $service->create($request)->getResponse();
            // ----- old -----
            // $valid = $service->validCreate($request);
            // if ($valid) return $valid;
            // return $service->create($request);
        } else {
            $service->validUpdate($request)->getResponse();
            if (!empty($service->getResponse())) return $service->getResponse();
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
        $service = new StoreInfoService($request);
        return $service->list()->getResponse();
    }
}
