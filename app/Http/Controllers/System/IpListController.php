<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\System\IpListService as Service;

class IpListController extends Controller
{
    public function store(Request $request)
    {
        return ((new Service($request)))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function update(Request $request, $id)
    {
        return ((new Service($request, $id)))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }
    public function index(Request $request)
    {
        return ((new Service($request)))
            ->index()
            ->getResponse();
    }
    public function destroy(Request $request, $id)
    {
        return (new Service($request, $id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function show(Request $request, $id)
    {
        return (new Service($request, $id))
            ->runValidate('show')
            ->show()
            ->getResponse();
    }
}
