<?php

namespace App\Services\Accounts;

use App\Services\Service;
use Illuminate\Support\Facades\Validator;

class AccessGroupService extends Service
{
    public function validator($req, $id = null)
    {
        $id = (!empty($id)) ? "" : "," . $id;
        $validator = Validator::make($req, [
            "name" => "required|string|unique:access_groups,name" . $id
        ], [
            "name.required" => "name 01",
            "name.string" => "name 01",
            "name.unique" => "name 03"
        ]);
        return $validator;
    }
}
