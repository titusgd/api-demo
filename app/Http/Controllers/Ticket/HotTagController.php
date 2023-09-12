<?php
namespace App\Http\Controllers\Ticket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Ticket\HotTagService as Service;

class HotTagController extends Controller{
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
    public function destroy(Request $request,$id)
        {
        return (new Service($request,$id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function settingSort(Request $request){
        return (new Service($request))
            ->runValidate('settingSort')
            ->settingSort()
            ->getResponse();
    }
    public function settingUse(Request $request){
        return (new Service($request))
            ->runValidate('settingUse')
            ->settingUse()
            ->getResponse();
    }

    public function uploadImage(Request $request){
        return (new Service($request))
        ->runValidate('uploadImage')
        ->uploadImage()
        ->getResponse();
    }

    public function publicIndex(Request $request){
        return (new Service($request))
        ->publicIndex()
        ->getResponse();
    }
}
