<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResourceCertificate;
use App\Services\Hr\HRCertificateService;

class HRCertificateController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRCertificateService;
    }

    public function index(Request $request){

        $data = json_decode($request->get('data') ?? '', true);

        $data = (new HRCertificateService())->index($data);

        return $data;
    }

    public function store(Request $request){

        $req = $request->all();

        $fields = [
            'name',
            'place',
            'number',
            'note',
            'type.id',
            'type.code',
            'type.name',
            'expiryDate.start',
            'expiryDate.end',
        ];

        foreach ($fields as $field) {
            if (empty($req[$field])) {
                unset($req[$field]);
            }
        }

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

        $fields = [
            'name',
            'place',
            'number',
            'note',
            'type.id',
            'type.code',
            'type.name',
            'expiryDate.start',
            'expiryDate.end',
        ];

        foreach ($fields as $field) {
            if (empty($req[$field])) {
                unset($req[$field]);
            }
        }

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);
    }

    public function destroy($id){

        return $this->service->delete($id);
    }

}
