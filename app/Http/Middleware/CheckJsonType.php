<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckJsonType
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
        $content = $request->getContent();
        $json_code = json_decode($content, true);
        return ($json_code === null) ? response(['code' => '04','msg' => 'json','data' => []], 200) : $next($request);
        // if ($json_code === null) {
        //     return response([
        //         'code' => '04',
        //         'msg' => 'json',
        //         'data' => []
        //     ], 200);
        // } else {
        //     return $next($request);
        // }
    }
}
