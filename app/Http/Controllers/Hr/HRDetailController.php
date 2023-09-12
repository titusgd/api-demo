<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResources;
use App\Services\Hr\HRService;


class HRDetailController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRService;
    }

    public function index(Request $request){

        $data = json_decode($request->get('data') ?? '', true);

        $data = (new HRService())->detail($data['id']);

        return $data;
    }



}
