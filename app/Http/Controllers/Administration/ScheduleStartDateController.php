<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Administrations\ScheduleStartDateService as Service;
class ScheduleStartDateController extends Controller
{
    public function store(Request $request){
        return (new Service($request))
        ->runValidate('store')
        ->store()
        ->getResponse();
    }

    public function update(Request $request,$id){
        return (new Service($request))
        ->setScheduleStartDateId($id)
        ->runValidate('update')
        ->update()
        ->getResponse();
    }

    public function list(Request $request,$store_id){
        //  $req = collect(json_decode($request->data),true);
        // return (new Service($req))
        return (new Service($request))
        ->setStoreId($store_id)
        ->runValidate('list')
        ->list()
        ->getResponse();
    }
}