<?php
namespace App\Http\Controllers\System;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\System\ErrorReportService as Service;
class ErrorReportController extends Controller{
    public function index(Request $request){
        return (new Service($request))
            // ->runValidate('index')
            ->index()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new Service($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    // public function update(Request $request, $id)
    // {
    //     return (new Service($request,$id))
    //         ->runValidate('update')
    //         ->update()
    //         ->getResponse();
    // }
    // public function destroy(Request $request,$id)
    //     {
    //     return (new Service($request,$id))
    //         ->runValidate('destroy')
    //         ->destroy()
    //         ->getResponse();
    // }
    // public function settingSort(Request $request){
    //     return (new Service($request))
    //         ->runValidate('settingSort')
    //         ->settingSort()
    //         ->getResponse();
    // }
    // public function settingUse(Request $request){
    //     return (new Service($request))
    //         ->runValidate('settingUse')
    //         ->settingUse()
    //         ->getResponse();
    // }

}