<?php

namespace App\Services\Accountants;

// ----- models -----
use App\Models\Accountant\CurrencyExchange;
// ----- methods -----
use App\Services\Service;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

class CurrencyExchangeService extends Service
{
    use RulesTrait;

    private $response;
    private $request;
    private $currencyExchangeId;
    private $changeErrorName;
    function __construct(Request $request, $currencyExchangeId = null)
    {
        $this->request = collect($request->all());
        $this->request->put('name_ch', $request->input('nameCh', ''));
        $this->request->forget('nameCh');
        $this->changeErrorName = ['name_ch' => 'nameCh'];
        (!empty($currencyExchangeId)) && $this->currencyExchangeId = $currencyExchangeId;
        return $this;
    }


    public function validateStore(): self
    {
        // 驗證規則
        $rules = [
            'name_ch' => 'required|string|unique:currency_exchanges,name_ch',
            'code' => 'required|string|unique:currency_exchanges,code',
        ];

        (!empty($this->request->get('exchange'))) && $rules['exchange'] = 'required|numeric';

        // 建立驗證錯誤訊息
        $messages = self::createMessages($rules, $this->changeErrorName)
            ->toDot()
            ->getMessages();

        // 驗證並回傳錯誤訊息
        $this->response = Service::validatorAndResponse(
            $this->request->all(),
            $rules,
            $messages
        );

        if (!empty($response)) return $this;
        // other...

        return $this;
    }

    public function responseOk(array $data = null): void
    {
        $this->response = ($data === null) ? Service::response('00', 'ok') : Service::response('00', 'ok', $data);
    }
    public function validateUpdate(): self
    {
        $rules = [
            'id' => 'required|integer|exists:currency_exchanges,id',
            'name_ch' => 'required|string|unique:currency_exchanges,name_ch,' . $this->currencyExchangeId,
            'code' => 'required|string|unique:currency_exchanges,code,' . $this->currencyExchangeId,

        ];
        (!empty($this->request->get('exchange'))) && $rules['exchange'] = 'required|numeric';

        $messages = self::createMessages($rules, $this->changeErrorName)
            ->toDot()
            ->getMessages();
        $request = collect($this->request->all());
        $request->put('id', $this->currencyExchangeId);
        $this->response = Service::validatorAndResponse(
            $request->toArray(),
            $rules,
            $messages
        );
        if (!empty($response)) return $this;
        // other...
        return $this;
    }

    public function validateShow(): self
    {
        $this->response = Service::validatorAndResponse(
            ['id' => $this->currencyExchangeId],
            ['id' => 'required|integer|exists:currency_exchanges,id'],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id'
            ]
        );
        if (!empty($response)) return $this;
        // other...
        return $this;
    }

    public function show(): self
    {
        $data = CurrencyExchange::select('id', 'name_ch as nameCh', 'code', 'exchange')
            ->where('id', '=', $this->currencyExchangeId)
            ->first();
        $this->responseOk($data);
        return $this;
    }

    public function add(): self
    {
        if ($this->response) return $this;
        $currencyExchange = new CurrencyExchange();
        $currencyExchange->name_ch = (!empty($this->request['name_ch'])) ? $this->request['name_ch'] : '';
        $currencyExchange->code = (!empty($this->request['code'])) ? $this->request['code'] : '';
        $currencyExchange->exchange = (!empty($this->request['exchange'])) ? $this->request['exchange'] : 0;
        $currencyExchange->updater_id = auth()->user()->id;
        $currencyExchange->save();
        $this->responseOk();
        return $this;
    }
    public function update(): self
    {
        if ($this->response) return $this;
        $data = collect($this->request->all());
        $data->put('updater_id', auth()->user()->id);
        CurrencyExchange::where('id', '=', $this->currencyExchangeId)
            ->update($data->toArray());
        $this->responseOk();
        return $this;
    }

    public function list(): self
    {
        $list = CurrencyExchange::select('id', 'name_ch as nameCh', 'code', 'exchange')->get();
        $this->response = Service::response('00', 'ok', $list->toArray());
        return $this;
    }

    public function validateDestroy(): self
    {
        $this->response = Service::validatorAndResponse(
            ['id' => $this->currencyExchangeId],
            ['id' => 'required|integer|exists:currency_exchanges,id'],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id'
            ]
        );
        if (!empty($response)) return $this;
        // other...
        return $this;
    }

    public function delete(): self
    {
        if ($this->response) return $this;
        CurrencyExchange::where('id', '=', $this->currencyExchangeId)->delete();
        $this->responseOk();
        return $this;
    }

    // get & set
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }
    public function getRequest(): Request
    {
        return $this->request;
    }
    public function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }
    public function getResponse(): object
    {
        return $this->response;
    }
}
