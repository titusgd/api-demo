<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ticket\ThemeService;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        return (new ThemeService($request))
            ->index()
            ->getResponse();
    }

    public function store(Request $request)
    {
        return (new ThemeService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function update(Request $request, $data_id)
    {
        return (new ThemeService($request, $data_id))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }

    public function sort(Request $request)
    {
        return (new ThemeService($request))
            ->runValidate('sort')
            ->sort()
            ->getResponse();
    }

    public function use(Request $request)
    {
        return (new ThemeService($request))
            ->runValidate('use')
            ->use()
            ->getResponse();
    }
}
