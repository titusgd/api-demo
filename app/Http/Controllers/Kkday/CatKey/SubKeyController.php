<?php

namespace App\Http\Controllers\kkday\CatKey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\CatKey\CatSubKeyService;

class SubKeyController extends Controller
{
    public function index(Request $request)
    {
        return (new CatSubKeyService($request))
            ->list()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new CatSubKeyService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function show()
    {
    }
    public function update(Request $request, $dataId)
    {
        return (new CatSubKeyService($request, $dataId))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }
    public function destroy(Request $request, $dataId)
    {
        return (new CatSubKeyService($request, $dataId))
            // ->validateDestroy()
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function importData(Request $request)
    {
        return (new CatSubKeyService($request))
            ->import()->getResponse();
    }
    // 變更使用狀態
    public function settingStatus(Request $request)
    {
        return (new CatSubKeyService($request))
            ->runValidate('settingStatus')
            ->settingStatus()
            ->getResponse();
    }

    public function settingSort(Request $request)
    {
        return (new CatSubKeyService($request))
            ->runValidate('settingSort')
            ->settingSort()
            ->getResponse();
    }
}
