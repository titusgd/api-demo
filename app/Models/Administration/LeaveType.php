<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\LeaveAnnual;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'days', 'min', 'direction', 'status', 'sort', 'user_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'use' => 'boolean',
        'min' => 'float'
    ];


    // public function validAdd(object $request)
    // {
    //     $service = new Service();
    //     $rules = [
    //         'name' => 'required|string|unique:leave_types,name',
    //         'days' => 'required|integer',
    //         'min' => 'required|numeric',
    //         'directions' => 'required|string'
    //     ];

    //     $valid = $service->validatorAndResponse($request->all(), $rules, [
    //         'name.required' => '01 name',
    //         'name.string' => '01 name',
    //         'name.unique' => '03 name',
    //         'days.required' => '01 days',
    //         'days.integer' => '01 days',
    //         'min.required' => '01 min',
    //         'min.numeric' => '01 min',
    //         'directions.required' => '01 directions',
    //         'directions.string' => '01 directions'
    //     ]);

    //     if ($valid) return $valid;
    // }

    // public function validUpdate(object $request)
    // {
    //     $service = new Service();
    //     $rules = [
    //         'id' => 'required|integer|exists:leave_types,id',
    //         'name' => 'required|string|unique:leave_types,name,' . $request->id,
    //         'days' => 'required|integer',
    //         'min' => 'required|integer',
    //         'directions' => 'required|string'
    //     ];

    //     $valid = $service->validatorAndResponse($request->all(), $rules, [
    //         'id.required' => '01 id',
    //         'id.integer' => '01 id',
    //         'id.exists' => '02 id',
    //         'name.required' => '01 name',
    //         'name.string' => '01 name',
    //         'name.unique' => '03 name',
    //         'days.required' => '01 days',
    //         'days.integer' => '01 days',
    //         'min.required' => '01 min',
    //         'min.integer' => '01 min',
    //         'min.numeric' => '01 min',
    //         'directions.required' => '01 directions',
    //         'directions.string' => '01 directions'
    //     ]);

    //     if ($valid) return $valid;
    // }

    // public function validUse(object $request)
    // {
    //     $service = new Service();

    //     $valid = $service->validatorAndResponse($request->all(), [
    //         'id' => 'required|integer|exists:leave_types,id',
    //         'use' => 'required|boolean',
    //     ], [
    //         'id.required' => '01 id',
    //         'id.integer' => '01 id',
    //         'id.exists' => '02 id',
    //         'use.request' => '01 use',
    //         'use.boolean' => '01 use',
    //     ]);

    //     if ($valid) return $valid;
    // }

    // public function validSort(object $request)
    // {
    //     $service = new Service;
    //     $data = ["data" => $request->all()];

    //     $valid = $service->validatorAndResponse($data, [
    //         "data" => 'array',
    //         "data.*" => 'integer|exists:leave_types,id'
    //     ], [
    //         'data.array' => '01 data',
    //         "data.*.integer" => '01 id',
    //         'data.*.exists' => '02 id'
    //     ]);
    //     return $valid;
    // }

    // public function createLeaveType(string $name, int $days, float $min, string $direction)
    // {
    //     $service = new Service();
    //     $this->create([
    //         'name' => $name,
    //         'days' => $days,
    //         'min' => $min,
    //         'direction' => $direction,
    //         'status' => true,
    //         'user_id' => auth()->user()->id
    //     ]);
    //     return $service->response('00', 'ok');
    // }

    // public function updateLeaveType(int $id, string $name, int $days, float $min, string $direction)
    // {
    //     $service = new Service;
    //     $update_arr = [
    //         'min' => $min,
    //         'direction' => $direction
    //     ];

    //     if ($id != 1) {
    //         $update_arr['name'] = $name;
    //         $update_arr['days'] = $days;
    //     }

    //     $update = $this->where('id', '=', $id)->update($update_arr);
    //     return $service->response('00', 'ok');
    // }


    // public function getList()
    // {
    //     $service = new Service;
    //     $data = $this->select(
    //         'id',
    //         'name',
    //         'days',
    //         'min',
    //         'direction as directions',
    //         'status as use'
    //     )
    //         ->orderBy('sort', 'asc')
    //         ->get()->toArray();
    //     // TODO:取得 - 特休可修天數
    //     // $leave_annual = LeaveAnnual::select('pai_day')
    //     //     ->where('user_id', '=', auth()->user()->id)
    //     //     ->where('start', 'like', date('Y') . '%')
    //     //     ->get()->toArray();
    //     // $sum = 0;
    //     // foreach ($leave_annual as $val) {
    //     //     $sum += $val['pai_day'];
    //     // }
    //     // $data[0]['days'] = $sum;

    //     return $service->response('00', 'ok', $data);
    // }

    public function getList()
    {
        return self::select(
            'id',
            'name',
            'days',
            'min',
            'direction as directions',
            'status as use',
            'sort'
        )->orderBy('sort', 'asc');
    }

    // public function setSort(object $request)
    // {
    //     $service = new Service;
    //     $sort = $request->all();
    //     $temp_arr = [];
    //     foreach ($sort as $key => $value) {
    //         $temp_arr[] = [
    //             "id" => $value,
    //             "sort" => $key + 1
    //         ];
    //     }
    //     $leave_type_id = '';

    //     // 批次更新
    //     $query = 'update leave_types set sort = CASE id';
    //     foreach ($temp_arr as $key => $val) {
    //         $query .= " when '{$val['id']}' then '{$val['sort']}'";
    //         $leave_type_id .= "{$val['id']},";
    //     }

    //     $leave_type_id = substr($leave_type_id, 0, -1);
    //     $query .= " END WHERE id IN ({$leave_type_id})";
    //     // update
    //     DB::update($query);

    //     return $service->response('00', 'ok');
    // }

    // /** updateUse
    //  *  假別管理 啟用停用
    //  *
    //  */
    // public function updateUse(int $leave_id, bool $leave_status)
    // {
    //     $service = new Service();
    //     $leave_type = $this->find($leave_id);

    //     $leave_type->status = $leave_status;

    //     $leave_type->save();

    //     return $service->response('00', 'ok');
    // }
}
