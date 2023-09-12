<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Models\Administration\ScheduleStartDate;
use App\Traits\RulesTrait;
use App\Traits\ResponseTrait;

class ScheduleStartDateService extends Service
{
    use RulesTrait;
    use ResponseTrait;
    private $schedule_start_date_id;
    private $request;
    private $store_id;
    public function __construct($request)
    {
        $this->request = collect($request);
    }
    public function setScheduleStartDateId(int $schedule_start_date_id): self
    {
        $this->schedule_start_date_id = $schedule_start_date_id;
        return $this;
    }
    public function getScheduleStartDateId(): int
    {
        return $this->schedule_start_date_id;
    }

    public function setStoreId(int $store_id): self
    {
        $this->store_id = $store_id;
        return $this;
    }
    public function getStoreId(): int
    {
        return $this->store_id;
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'store_id' => 'required|exists:stores,id',
                    'date' => 'required|date',
                ];
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'id' => 'required|exists:schedule_start_dates,id',
                    'date' => 'required|date'
                ];
                $data = $this->request->toArray();
                $data['id'] = $this->schedule_start_date_id;
                break;
            case 'list':
                $rules = [
                    'store_id' => 'required|exists:stores,id'
                ];
                $data = $this->request->toArray();
                $data['store_id'] = $this->store_id;
                break;
        }

        $this->response = self::validate($data, $rules);

        return $this;
    }

    public function store()
    {
        if (!empty($this->response)) return $this;
        $this->request->put('updater_id', auth()->user()->id);
        $data = collect($this->request->toArray());
        $data->put('start_date',$data['date'])->forget('date');
        // dd($this->request->toArray(),$data->toArray());
        $schedule_start_date = ScheduleStartDate::create($data->toArray());
        (!empty($schedule_start_date['id'])) && $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function update()
    {
        if (!empty($this->response)) return $this;
        ScheduleStartDate::where('id', $this->schedule_start_date_id)
            ->update($this->request->toArray());
        $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function list()
    {
        if (!empty($this->response)) return $this;
        $schedule_start_date =
            ScheduleStartDate::select('id', 'start_date as date', 'created_at')
            ->where('store_id', '=', $this->store_id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
        $this->response = Service::response('00', 'ok', $schedule_start_date);
        return $this;
    }
}
