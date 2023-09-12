<?php

namespace App\Services\CTBC;

use App\Services\Service;

/**
 * Class CTBCService.
 */
class CTBCService extends Service
{

    public $validationRules = [
        'merchant_id' => 'required|string',
        'terminal_id' => 'required|string',
        'lidm' => 'required|string',
        'purch_amt' => 'required|string',
        'tx_type' => 'required|string',
        'option' => 'required|string',
        'key' => 'required|string',
        'merchant_name' => 'required|string',
        'auth_res_url' => 'required|string',
        'order_detail' => 'required|string',
        'auto_cap' => 'required|string',
        'customize' => 'string',
        'debug' => 'required|string',
    ];

    public $validationMsg = [
        'merchant_id.required' => '01 merchant_id',
        'merchant_id.string' => '01 merchant_id',
        "terminal_id.required"   => "01 terminal_id",
        "terminal_id.string"     => "01 terminal_id",
        "lidm.required"   => "01 lidm",
        "lidm.string"     => "01 lidm",
        "purch_amt.required" => "01 purch_amt",
        "purch_amt.string" => "01 purch_amt",
        "tx_type.required" => "01 tx_type",
        "tx_type.string" => "01 tx_type",
        "option.required" => "01 option",
        "option.string" => "01 option",
        'key.required' => "01 key",
        'key.string' => "01 key",
        'merchant_name.required' => "01 merchant_name",
        'merchant_name.string' => "01 merchant_name",
        'auth_res_url.required' => "01 auth_res_url",
        'auth_res_url.string' => "01 auth_res_url",
        'order_detail.required' => "01 order_detail",
        'order_detail.string' => "01 order_detail",
        'auto_cap.required' => "01 auto_cap",
        'auto_cap.string' => "01 auto_cap",
        'customize.string' => "01 customize",
        'debug.required' => "01 debug",
        'debug.string' => "01 debug",
    ];

    public function index($req){

        include(base_path('app/Services/CTBC/auth_mpi_mac.php'));

        // dd($req);

        $reponse = auth_in_mac($req['merchant_id'],
                                $req['terminal_id'],
                                $req['lidm'],
                                $req['purch_amt'],
                                $req['tx_type'],
                                $req['option'],
                                $req['key'],
                                $req['merchant_name'],
                                $req['auth_res_url'],
                                $req['order_detail'],
                                $req['auto_cap'],
                                $req['customize'],
                                $req['debug']
                            );

        return $reponse;

    }
}
