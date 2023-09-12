<?php

namespace App\Services\Kkday\Booking;

use App\Services\Kkday\KkdayService;;

/**
 * Class QueryAmountService.
 */
class QueryAmountService extends KkdayService
{
    public $validationRules = [
        's_date' => 'string|required',
        'e_date' => 'string|required',
    ];

    public $validationMsg = [
        's_date.string' => '01 prod_no',
        's_date.required' => '01 prod_no',
        'e_date.string' => '01 state',
        'e_date.required' => '01 state',
    ];

    public function index($req){

        $data = $this->setParams($req)->callApi('post', 'v3/Booking/QueryAmount')->getBody();

        return KkdayService::response('00', 'OK', $data);

    }
}
