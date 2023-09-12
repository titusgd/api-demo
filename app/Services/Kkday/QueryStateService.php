<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;
use App\Traits\ResponseTrait;
use App\Models\Kkday\State;
use Illuminate\Support\Facades\DB;

/**
 * Class SearchService.
 */
class QueryStateService extends KkdayService
{
    use ResponseTrait;
    public function index()
    {
        $data = $this->callApi('get', 'v3/Product/QueryState')->getBody();
        return KkdayService::response('00', 'OK', $data);
    }

    public function stateList()
    {
        $this->response = KkdayService::response(
            '00',
            'OK',
            State::select('code', 'name')->get()->toArray()
        );
        return $this;
    }

    public function runImport()
    {
        $data = $this->getStatuesFromApi();
        if ($data['result'] == "00") {
            foreach ($data['states'] as $key => $state) {
                State::updateOrCreate(
                    ['code' => $state['code']],
                    [
                        'name' => $state['name']
                    ]
                );
            }
            $this->response = KkdayService::response('00', 'ok');
        } else {
            $this->response = KkdayService::response('999', 'api 呼叫失敗!');
        }

        return $this;
    }

    private function getStatuesFromApi()
    {
        return $this->callApi('get', 'v3/Product/QueryState')->getBody();
    }
}
