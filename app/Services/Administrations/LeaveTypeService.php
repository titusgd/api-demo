<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Models\Administration\LeaveType;
use App\Traits\RulesTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
// use App\Models\Accounting\Invoice;

class LeaveTypeService extends Service
{
    use RulesTrait, ResponseTrait;
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
            case 'add':
                $rules = [
                    'name' => 'required|string|unique:*.leave_types,name',
                    'days' => 'required|integer',
                    'min' => 'required|numeric',
                    'directions' => 'required|string'
                ];
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'id' => 'required|integer|exists:*.leave_types,id',
                    'name' => 'required|string|unique:*.leave_types,name,' . $this->request['id'],
                    'days' => 'required|integer',
                    'min' => 'required|integer',
                    'directions' => 'required|string'
                ];
                $data = $this->request->toArray();
                break;
            case 'sort':
                $rules = [
                    "data" => 'array',
                    "data.*" => 'integer|exists:*.leave_types,id'
                ];

                self::setMessages([
                    'data.array' => '01 data',
                    "data.*.integer" => '01 id',
                    'data.*.exists' => '02 id'
                ]);
                $data['data'] = $this->request->toArray();
                break;
            case 'use':
                $rules = [
                    'id' => 'required|integer|exists:*.leave_types,id',
                    'use' => 'required|boolean',
                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);
        return $this;
    }

    public function list()
    {
        if (!empty($this->response)) return $this;

        $list = (new LeaveType())->getList()->get()->toArray();

        $this->response = Service::response('00', 'ok', $list);
        return $this;
    }

    public function createLeaveType()
    {
        if (!empty($this->response)) return $this;
        $leave_type = new LeaveType();
        $leave_type->name = $this->request['name'];
        $leave_type->days = $this->request['days'];
        $leave_type->min = $this->request['min'];
        $leave_type->direction = $this->request['directions'];
        $leave_type->status = true;
        $leave_type->user_id = auth()->user()->id;
        $leave_type->save();
        $leave_count = LeaveType::count();
        $leave_type->sort = $leave_count;
        $leave_type->save();
        self::setResponse(Service::response('00', 'ok'));
        return $this;
    }

    public function update()
    {
        if (!empty($this->response)) return $this;
        //code...
        $leave_type = LeaveType::find($this->dataId);
        $leave_type->name = $this->request['name'];
        $leave_type->days = $this->request['days'];
        $leave_type->min = $this->request['min'];
        $leave_type->direction = $this->request['directions'];
        $leave_type->status = true;
        $leave_type->user_id = auth()->user()->id;
        $leave_type->save();
        self::setResponse(Service::response('00', 'ok'));
        return $this;
    }
    public function sort()
    {
        if (!empty($this->response)) return $this;
        $sort = $this->request->all();
        $temp_arr = [];
        foreach ($sort as $key => $value) {
            $temp_arr[] = [
                "id" => $value,
                "sort" => $key + 1
            ];
        }
        $data_id = '';
        $query = 'update leave_types set sort = CASE id';
        foreach ($temp_arr as $key => $val) {
            $query .= " when '{$val['id']}' then '{$val['sort']}'";
            $data_id .= "{$val['id']},";
        }
        $data_id = substr($data_id, 0, -1);
        $query .= " END WHERE id IN ({$data_id})";
        DB::connection('*')->update($query);
        self::setResponse(Service::response('00', 'ok'));
        return $this;
    }

    public function use()
    {
        if (!empty($this->response)) return $this;
        $model = LeaveType::find($this->dataId);
        $model->status = $this->request['use'];
        $model->save();
        self::setResponse(Service::response('00', 'ok'));
        return $this;
    }
}
