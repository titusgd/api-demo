<?php
namespace App\Http\Controllers\PettyCash;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\PettyCash\PettyCashListService;

class PettyCashListController extends Controller
{
    public function add(Request $request){

        $service = new PettyCashListService($request);
        
        // 檢查輸入欄位
        $vali=$service->validate();
        if($vali !==true ) return $vali;
        
        $creat = $service->create();
        return $creat ;

    }

    public function detail(Request $request){

        $service = new PettyCashListService($request);
        
        if($service->validateDetail()) return $service->validateDetail();

        return $service->searchList();
    }

    public function update(Request $request){
        $req = $request->all();
        $service = new PettyCashListService($request);
        
        $vali = $service->updateVali($request->all());
        if($vali) return $vali;
        
        $update = $service->update($req);
        return $update;
    }

}
