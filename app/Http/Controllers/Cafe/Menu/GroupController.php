<?php

namespace App\Http\Controllers\Cafe\Menu;

use App\Models\Cafe\Menu\Group;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Cafe\Menu\GroupService;

class GroupController extends Controller
{
    public function index()
    {
        return (new GroupService())->whereGroup()->getResponse();
    }

    public function store(Request $request)
    {
        $service = (new GroupService)->validGroupName($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->addGroup($request)->getResponse();
    }
    public function show(Group $group)
    {
        //
    }

    public function update(Request $request, Group $group)
    {
        $service = (new GroupService)->validUpdate($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->update($request)->getResponse();
    }

    public function destroy(Request $request, $group)
    {
        $service = (new GroupService)->validDelete($group);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->delete($group)->getResponse();
    }

    public function sort(Request $request)
    {
        return (new GroupService)
            ->sort($request)
            ->getResponse();
    }
}
