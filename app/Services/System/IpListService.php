<?php

namespace App\Services\System;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Traits\TableMethodTrait;
use Illuminate\Support\Facades\DB;

use App\Models\IpList as Model;

class IpListService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    use TableMethodTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'ip' => 'required|string|ip',
                    'note' => 'required|string'
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'ip.ip' => '209 ip',
                    'ip.required' => '01 ip',
                    'ip.string' => '01 ip',
                    'note.required' => '01 note',
                    'note.string' => '01 note',

                ]);
                break;
            case 'update':
                $rules = [
                    'id' => 'required|integer|exists:*.ip_lists,id',
                    'ip' => 'required|string|ip|unique:*.ip_lists,id,' . $this->dataId,
                    'note' => 'required|string'
                ];
                $data = $this->request->toArray();
                $data['id'] = $this->dataId;
                self::setMessages([
                    'id.required' => '01 id',
                    'id.integer' => '01 id',
                    'id.exists' => '03 id',
                    'ip.ip' => '209 ip',
                    'ip.required' => '01 ip',
                    'ip.string' => '01 ip',
                    'ip.unique' => '03 ip',
                    'note.required' => '01 note',
                    'note.string' => '01 note',
                ]);
                break;
            case 'destroy':
                $rules = [
                    'id' => 'required|integer|exists:*.ip_lists,id',
                ];
                $data = $this->request->toArray();
                $data['id'] = $this->dataId;
                break;
            case 'show':
                $rules = [
                    'id' => 'required|integer|exists:*.ip_lists,id',
                ];
                $data = $this->request->toArray();
                $data['id'] = $this->dataId;
                break;
            case 'method':
                $rules = [
                    // code
                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);
        return $this;
    }

    public function index()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $data = Model::get();
        $data = $data->map(function ($item) {
            return $item->formate();
        });

        self::setOk($data);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $model = new Model;
        $model->ip = $this->request['ip'];
        $model->note = $this->request['note'];
        $model->user_id = auth()->user()->id;
        $model->save();
        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $model = Model::find($this->dataId);
        $model->ip = $this->request['ip'];
        $model->note = $this->request['note'];
        $model->user_id = auth()->user()->id;
        $model->save();
        self::setOk();
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;

        self::del((new Model), $this->dataId, 'id');
        self::setOk();

        return $this;
    }
    public function settingSort()
    {
        if (!empty(self::getResponse())) return $this;

        self::sort((new Model), $this->request['data'], 'id');
        self::setOk();

        return $this;
    }

    public function settingUse()
    {
        if (!empty(self::getResponse())) return $this;

        self::use((new Model), $this->request['id'], $this->request['use']);
        self::setOk();

        return $this;
    }

    public function show()
    {
        if (!empty(self::getResponse())) return $this;
        $data = Model::find($this->dataId);
        $data = $data->formate();
        self::setOk($data);
        return $this;
    }
}
