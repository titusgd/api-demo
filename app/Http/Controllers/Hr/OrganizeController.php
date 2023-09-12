<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Organize;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\Organizer;
use App\Services\Hr\OrganizeService;

class OrganizeController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new OrganizeService;
    }

    public function index()
    {

        return (new OrganizeService())->index();
    }

    public function store(Request $request){

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ($valid) {
            return $valid;
        }

        return $this->service->create($req);

    }

    public function update(Request $request, $id){

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);


    }

    public function destroy($id){

        return $this->service->delete($id);
    }


    public function sort(Request $request, $id){

        $req = $request->all();

        $data = $req['data'];

        return $this->service->sort($data, $id);
    }

    public function use(Request $request, $id){

        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules_use, $this->service->validationMsg_use);
        if ($valid) {
            return $valid;
        }

        $data = [
            'id' => $id,
            'use' => $req['use']
        ];

        return $this->service->use($data);
    }
}
