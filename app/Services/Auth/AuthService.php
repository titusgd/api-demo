<?php

namespace App\Services\Auth;

use App\Models\Account\User;

use App\Services\Service;
use Illuminate\Support\Facades\Validator;

class AuthService extends Service
{
    public function validator($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "id" => "required|string",
                "pw" => "required|string"
            ],
            [
                'id.required' => 'id',
                'pw.required' => 'pw'
            ]
        );

        return $validator;
    }

    public function dataFormat($req)
    {
        $data = [
            "code" => $req["id"],
            "password" => $req["pw"],
        ];
        return $data;
    }

    public function validLoginData($request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            "id" => "required|string|max:16",
            "pw" => "required|string"
        ], [
            'id.required' => '01 id',     // 必填
            'pw.required' => '01 pw',     // 必填
            'id.string' => '01 id',       // 型態
            'pw.string' => '01 pw'        // 型態
        ]);
        if ($valid) return $valid;
    }

}
