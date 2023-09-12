<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Token;
use App\Models\TokenLog;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $token = $request->bearerToken() ?? '';
        if($token){
            $toDay = date('Y-m-d H:i:s`');
            $token = Token::where('expired', '>=', $toDay)->where('token', $token)->first();

            if(!$token){
                return response()->json(['code' => '08', 'msg' => 'invalid token', 'data' => []]);
            }

            $ip_address = $request->ip();
            $token_log = new TokenLog();
            $token_log->ip_address = $ip_address;
            $token_log->token = $token->token;
            $token_log->save();

            return $next($request);

        } else {
            return response()->json(['code' => '08', 'msg' => 'missing token', 'data' => []]);
        }

    }
}
