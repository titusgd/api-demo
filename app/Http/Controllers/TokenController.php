<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Token;

class TokenController extends Controller
{
    // get token
    public function get_token(){

        $toDay = date('Y-m-d H:i:s');
        $token = str_replace('-', '', Str::uuid());
        $expired = date('Y-m-d H:i:s', strtotime($toDay .' +30 minutes'));

        $token_save = new Token();
        $token_save->token = $token;
        $token_save->expired = $expired;
        $token_save->save();

        $data = [
            'token' => $token,
            'expired' => $expired,
            'maxAge' => 1800,
        ];

        return response()->json(['code' => "00", 'msg' => 'ok', 'data' => $data]);
    }
}
