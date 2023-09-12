<?php

namespace App\Services\Store;


// services
use App\Services\Service;
use App\Services\Files\ImageUploadService;
// models
use App\Models\Store;
use App\Models\Image;
use App\Models\GroupToken;
// laravel methods
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class StoreInfoService extends Service
{
    private $massage = [
        'id'             => [
            'required' => 'id 01',
            'integer' => 'id 01',
            'exists' => '02 id'
        ],
        'code' => [
            'required' => "01 code",
            'string' => "01 code",
            'max' => "01 code",
            'unique' => "03 code",
        ],
        'store' => [
            'required' => "01 store",
            'string' => "01 store",
        ],
        'address' => [
            'required' => "01 address",
            'string' => "01 address",
        ],
        'phone' => [
            'required' => "01 phone",
            'string' => "01 phone",
        ],
        'representative' => [
            'required' => "01 representative",
            'string' => "01 representative",
        ],
        'use' => [
            'required' => "01 use",
            'boolean' => "01 use",
        ],
        'shopHours' => [
            'week' => [
                'string' => "01 week"
            ],
            'time' => [
                'string' => "01 week"
            ]
        ],
        'showToWebsite' => [
            'boolean' => "01 showToWebsite"
        ],
        'map' => [
            'string' => '01 map'
        ],
        'image' => [
            'string' => '01 image'
        ]

    ];
    private $response;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
    public function getResponse()
    {
        return $this->response;
    }


    public function validCreate($request)
    {
        // $service = new Service();
        $rules = [
            'code'           => 'required|string|max:3|unique:stores,code',
            'store'          => 'required|string',
            'address'        => 'required|string',
            'phone'          => 'required|string',
            'representative' => 'required|string',
            'use'            => 'required|boolean',
            'showToWebsite'  => 'required|boolean'
        ];
        (!empty($request['shopHours']['week'])) && $rules['shopHours.week'] = 'string';
        (!empty($request['shopHours']['time'])) && $rules['shopHours.time'] = 'string';
        (!empty($request['map'])) && $rules['map'] = 'string';
        (!empty($request['image'])) && $rules['image'] = 'string';

        $this->response = Service::validatorAndResponse(
            $request->toArray(),
            $rules,
            Arr::dot($this->massage)
        );
        return $this;
    }

    public function create($request)
    {
        // 整理調整資料
        $input_data = collect($request->all());

        $input_data
            ->put('use_flag', $request['use'])
            ->put('floor', implode(",", $request['floor']["f"]))
            ->put('basement', implode(",", $request['floor']["b"]))
            ->put('show_web', $request['showToWebsite'])
            ->put('week', $request['shopHours']["week"])
            ->put('time', $request['shopHours']["time"])
            ->put('user_id', auth()->user()->id);

        $input_data
            ->forget('shopHours')
            ->forget('showToWebsite')
            ->forget('id')
            ->forget('use');

        $input_data = $input_data->map(function ($item) {
            return ($item === null) ? "" : $item;
        });
        $store = Store::create($input_data->toArray());
        $store_id = $store->id;

        // 圖片上傳
        if (!empty($request->image)) {
            $image_source = $request->image;
            $image_service = new ImageUploadService();
            $image_data = $image_source;
            $image_service->addImage($image_data, "Store", $store_id);
            $image_id = $image_service->getId();
            $store = Store::find($store_id);
            $store->image_id = $image_id;
            $store->save();
        }
        $this->response = Service::response("00", "ok");
        return $this;
    }

    public function validUpdate($request)
    {
        $service = new Service();
        $rules = [
            'id'             => 'required|integer|exists:stores,id',
            'code'           => 'required|string|max:3|unique:stores,code,' . $request['id'],
            'store'          => 'required|string',
            'address'        => 'required|string',
            'phone'          => 'required|string',
            'representative' => 'required|string',
            'use'            => 'required|boolean',
            'showToWebsite'  => 'required|boolean'
        ];
        (!empty($request['shopHours']['week'])) && $rules['shopHours.week'] = 'string';
        (!empty($request['shopHours']['time'])) && $rules['shopHours.time'] = 'string';
        (!empty($request['map'])) && $rules['map'] = 'string';
        (!empty($request['image'])) && $rules['image'] = 'string';

        $this->response = $service
            ->validatorAndResponse(
                $request->toArray(),
                $rules,
                Arr::dot($this->massage)
            );
        return $this;
    }

    public function update($request)
    {
        // 整理調整資料
        $input_data = collect($request->all());

        $input_data
            ->put('use_flag', $request['use'])
            ->put('floor', implode(",", $request['floor']["f"]))
            ->put('basement', implode(",", $request['floor']["b"]))
            ->put('show_web', $request['showToWebsite'])
            ->put('week', $request['shopHours']["week"])
            ->put('time', $request['shopHours']["time"])
            ->put('user_id', auth()->user()->id);

        $input_data
            ->forget('shopHours')
            ->forget('showToWebsite')
            ->forget('id')
            ->forget('use');

        $input_data = $input_data->map(function ($item) {
            return ($item === null) ? "" : $item;
        });
        if (!empty($input_data['image'])) {
            Image::where([['type', '=', 'Store'], ['fk_id', '=', $request->id]])->delete();
            // 圖片上傳
            if (!empty($request->image)) {
                $image_source = $request->image;
                $image_service = new ImageUploadService();
                $image_data = $image_source;
                $image_service->addImage($image_data, "Store", $request->id);
                $image_id = $image_service->getId();
                $store = Store::find($request->id);
                $store->image_id = $image_id;
                $store->save();
            }
        }
        $input_data->forget('image');

        $store = Store::find($request->id);
        $input_data->map(function ($item, $key) use ($store) {
            $store->$key = $item;
        });
        $store->save($input_data->toArray());
        $this->response = Service::response("00", "ok");
        return $this;
    }

    public function list()
    {
        $results = Store::select(
            'id',
            'code',
            'store',
            'representative',
            'address',
            'phone',
            'use_flag as use',
            'floor',
            'basement',
            DB::raw('(select url from images where type="Store" AND images.id = image_id)as image'),
            'map',
            'show_web',
            'week',
            'time'
        )->get();

        $results = $results->map(function ($item, $key) {
            $item = collect($item);
            $item['floor'] = [
                'f' => array_filter(explode(',', $item['floor'])),
                'b' => array_filter(explode(',', $item['basement']))
            ];
            $item['shopHours'] = [
                'week' => $item['week'],
                'time' => $item['time']
            ];
            $item['showToWebsite'] = (bool)$item['show_web'];
            $item['image'] = (!empty($item['image'])) ? env('API_URL') . $item['image'] : '';

            $item['map'] = (!empty($item['map'])) ? $item['map'] : '';
            $item['use'] = (bool)$item['use'];
            unset($item['basement'], $item['week'], $item['time'], $item['show_web']);
            return $item;
        });
        $this->response = Service::response('00', 'ok', $results->toArray());
        return $this;
    }

    public function checkToken($token)
    {
        $datetime  = date('Y-m-d H:i:s');
        $token_list = GroupToken::select('id')
            ->where('token', '=', $token)
            ->where('expired', '>=', $datetime)
            ->get()
            ->toArray();
        return (!empty($token_list)) ? true : false;
    }
}
