<?php

namespace App\Http\Controllers\Cafe\Menu;

// ----- methods -----
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Cafe\Menu\FolderService;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        $service = (new FolderService($request))->validateIndex();
        if ($service->getResponse()) return $service->getResponse();
        return $service->whereFolder()->getResponse();
    }
    public function store(Request $request)
    {
        $service = (new FolderService($request))->validateStore();
        if ($service->getResponse()) return $service->getResponse();
        return $service->addFolder()->getResponse();
    }
    public function update(Request $request, $folder_id)
    {
        $service = (new FolderService($request))->validateUpdate($folder_id);
        if ($service->getResponse()) return $service->getResponse();
        return $service->update($folder_id)->getResponse();
    }
    public function destroy(Request $request, $folder_id)
    {
        $service = (new FolderService($request))->validDelete($folder_id);
        if ($service->getResponse()) return $service->getResponse();
        return $service->delete($folder_id)->getResponse();
    }
    public function sort(Request $request)
    {
        return (new FolderService($request))->sort()->getResponse();
    }
}
