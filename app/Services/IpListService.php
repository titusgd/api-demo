<?php

namespace App\Services;

use App\Services\Service;
use App\Models\IpList;
use App\Models\Administration\Punchtimecard;

class IpListService extends Service
{

    public function validAdd($request)
    {
        $rules = [
            "ip" => 'required|string|unique:ip_lists,ip'
        ];
        $err_msg = [
            'ip.required' => '01 ip',
            'ip.string' => '01 ip',
            'ip.unique' => '03 ip'
        ];
        $valid = Service::validatorAndResponse($request->all(), $rules, $err_msg);
        if ($valid) return $valid;
    }

    public function validUpdate($request)
    {
        $rules = [
            "id" => "required|integer|exists:ip_lists,id",
            "ip" => 'required|string|unique:ip_lists,ip,' . $request->id
        ];
        (!empty($request->note)) && $rules['note'] = 'string';

        $err_msg = [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'ip.required' => '01 ip',
            'ip.string' => '01 ip',
            'ip.unique' => '03 ip',
            'note.string' => '01 note'
        ];
        $valid = Service::validatorAndResponse($request->all(), $rules, $err_msg);
        if ($valid) return $valid;
    }

    public function validList($request)
    {
        $rules = [];
        $err_msg = [];
        $valid = Service::validatorAndResponse($request->all(), $rules, $err_msg);
        if ($valid) return $valid;
    }

    public function validDel($request)
    {
        $rules = [];
        $err_msg = [];
        $valid = Service::validatorAndResponse($request->all(), $rules, $err_msg);
        if ($valid) return $valid;

        // 使用中的ip，無法刪除
        $ip_arr = $request->all();
        $ip_data = IpList::select('ip')->whereIn('id', $ip_arr)->get()->toArray();
        $use = false;
        foreach ($ip_data as $key => $value) {
            if ($use) break;
            $punchTimeCard = PunchTimeCard::select('id')->where('ip', '=', $value)->first();
            ($punchTimeCard) && $use = true;
        }
        if ($use) return Service::response('', '此ip使用中，無法刪除');
    }
    /** add()
     *  新增 ip
     */
    public function add(string $ip, string $note = null)
    {
        $ip_data = new IpList;

        $ip_data->user_id = auth()->user()->id;
        $ip_data->ip = $ip;
        $ip_data->note =  (!empty($note)) ? $note : '';
        $ip_data->save();

        return Service::response('00', 'ok');
    }
    /** update()
     *  更新ip資訊
     */
    public function update($id, string $ip, string $note = null)
    {
        $ip_data = IpList::find($id);

        $ip_data->user_id = auth()->user()->id;
        $ip_data->ip = $ip;
        (!empty($note)) && $ip_data->note = $note;
        $ip_data->save();

        return Service::response('00', 'ok');
    }
    /** list()
     *  ip 列表查詢
     */
    public function list($request)
    {
        $ip_query = IpList::select('id', 'ip', 'note', 'user_id');
        $ip_data = $ip_query->get()->toArray();

        return Service::response('00', 'ok', $ip_data);
    }
    /** dele()
     *  刪除 指定ip列表
     */
    public function del(int $ip_id)
    {
        $del = IpList::where('id', '=', $ip_id)->delete();
        return Service::response('00', 'ok');
    }
}
