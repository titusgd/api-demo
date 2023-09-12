<?php

namespace App\Http\Controllers\Accounting;

use App\Models\Accounting\DayStatement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Accounting\DayStatementService;

class DayStatementController extends Controller
{
    public function index(Request $request)
    {
        $req = collect(json_decode($request->data, true));
        $service = new DayStatementService();

        $valid = $service->validIndex($req);
        if ($valid) return $valid;

        return $service->getList($req['year'], $req['month']);
    }
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        try {
            $service = new DayStatementService;
            $valid_date = $service->validDate($request);
            if ($valid_date) return $valid_date;
            return $service->createData($request);
        } catch (\Exception $e) {
            return $service->response('999', 'system error');
        }
    }
    public function show(DayStatement $dayStatement)
    {
        //
    }
    public function edit(DayStatement $dayStatement)
    {
        //
    }
    public function update(Request $request, DayStatement $dayStatement)
    {
        //
    }

    public function destroy(Request $request)
    {
        try {
            $service = new DayStatementService();
            $valid = $service->isDayStatementId($request->id);
            if ($valid) return $valid;
            $service->deleteData($request->id);
            return $service->response('00', 'ok');
        } catch (\Exception $e) {
            return $service->response('999', 'system error');
        }
    }

    public function detail(Request $request)
    {
        $req = collect(json_decode($request->data, true));
        $service = new DayStatementService();
        $valid = $service->isDayStatementId($req['id']);
        if ($valid) return $valid;

        $service->detail($req['id']);
    }
}
