<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponseTrait;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        $res = [
            "code"=>"105",
            "msg"=>"logout"
        ];
        return response()->json($res,200,$headers,JSON_UNESCAPED_UNICODE);
        // return response()->json(["code"=>"XXX","msg"=>"not login"],200,["content-type" => "application/json;charset=UTF-8"]);
        // if($request->expectsJson()){
        //     return $this->errorResponse(
        //         $exception->getMessage(),
        //         Response::HTTP_UNAUTHORIZED
        //     );
        // }else{
        //     return redirect()->guest($exception->redirectTo() ?? route('login'));
        // }
    }
}
