<?php

namespace App\Services\Accounting;

// model
use App\Services\Service;
use App\Models\AccountingSubject;
use App\Models\Accounting\DayStatement;
use App\Models\Accounting\DayStatementData;
// method
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class LedgerService extends Service
{
    private $res;
    public function getResponse()
    {
        return $this->res;
    }
    // prior_period:上期累計金額 | this_period:本期金額 | this_period_total:本期累計金額
    // public $prior_period, $this_period, $this_period_total;

    public function validIndex($request)
    {
        $page = $request['page'];
        $data = json_decode($request['data'], true);
        $rules = [
            'subject' => 'required|integer|exists:accounting_subjects,id',
            'date.start' => 'required|date',
            'date.end' => 'required|date',
            'detail' => 'required|boolean',
            'count' => 'required|integer',
            'page' => 'required|integer',
        ];

        $message = [
            'subject' => [
                'required' => '01 subject',
                'integer' => '01 subject',
                'exists' => '02 subject'
            ],
            'date' => [
                'start' => [
                    'required' => '01 date.start',
                    'date' => '01 date.start'
                ],
                'end' => [
                    'required' => '01 date.end',
                    'date' => '01 date.end'
                ]
            ],
            'detail' => [
                'required' => '01 detail',
                'boolean' => '01 detail'
            ],
            'count' => [
                'required' => '01 count',
                'integer' => '01 count'
            ],
            'page' => [
                'required' => '01 page',
                'integer' => '01 page'
            ]

        ];

        $this->res = Service::validatorAndResponse($data, $rules, Arr::dot($message));
        // if (!empty($this->res)) return $this->res;

        return $this;
    }

    public function getList($request)
    {
        if(!empty(self::getResponse())) return $this;
        $page = collect([]);
        $page->put('total', 1)->put('countTotal', 1)->put('page', 1);
        $res_data = collect([]);
        // 上期累計金額        
        $prior_period = [
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ];

        // 本期金額
        $this_period = [
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ];

        // 本期累計金額
        $this_period_total = [
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ];
        $req_data = collect(json_decode($request['data'], true));

        $data = DayStatementData::select(
            'id',
            'day_statement_id',
            'debit_credit',
            'summary',
            'price',
            'accounting_subject_id',
            'pay_type',
            DB::raw('(select summons from day_statements where day_statements.id = day_statement_id) as main'),
            'created_at',
            'code'
        )
            ->where('accounting_subject_id', '=', $req_data->get('subject'))
            ->whereBetween('created_at', [$req_data['date']['start'], $req_data['date']['end']])
            ->paginate($req_data['count'], ['*'], 'page', $req_data['page'])
            ->toArray();

        $page['total'] = (!empty($data['last_page'])) ? $data['last_page'] : 1; //總頁數
        $page['countTotal'] = (!empty($data['total'])) ? $data['total'] : 1; //總筆數
        $page['page'] = (!empty($data['current_page'])) ? $data['current_page'] : 1; //當前頁次

        $temp_arr = collect([]);
        $temp_arr = $temp_arr->merge($this->dayStatementFormat($data['data']));

        $res_data->put('priorPeriod', $prior_period)
            ->put('thisPeriod', $this_period)
            ->put('thisPeriodTotal', $this_period_total)
            ->put('list', $temp_arr->toArray());
        $this->res = Service::response_paginate('00', 'ok', $res_data, $page);
        return $this;
    }

    private function dayStatementFormat($data)
    {
        $temp_arr = collect([]);
        foreach ($data as $key => $item) {
            $date = Carbon::parse($item['created_at']);
            $temp_arr->push([
                'date' => $date->format('Y/m/d'),
                'invoice' => $item['code'],
                'summons' => $item['main'],
                'summary' => $item['summary'],
                'debit' => ($item['debit_credit'] == 1) ? $item['price'] : 0,
                'credit' => ($item['debit_credit'] == 2) ? $item['price'] : 0,
                'balance' => 0      // 餘額
            ]);
        }
        return $temp_arr->toArray();
    }
}
