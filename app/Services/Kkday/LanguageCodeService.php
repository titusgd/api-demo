<?php

namespace App\Services\Kkday;
// models
use App\Models\Kkday\LanguageCode;
// methods
use App\Services\Kkday\KkdayService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;

class LanguageCodeService extends KkdayService
{
    use RulesTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
        $this->request
            ->put('description_ch', $request->input('descriptionCh', ''))
            ->put('description_en', $request->input('descriptionEn', ''));

        $this->request
            ->forget('descriptionCh')
            ->forget('descriptionEn');

        $this->changeErrorName = [
            'description_ch' => 'descriptionCh',
            'description_en' => 'descriptionEn',
        ];
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'type' => 'required|string'
                ];
                (!empty($this->request['description_ch'])) && $rules['description_ch'] = 'required|string';
                (!empty($this->request['description_en'])) && $rules['description_en'] = 'required|string';
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'id' => 'required|exists:kkdays_airport_type_codes,id',
                    'type' => 'required|string'
                ];
                (!empty($this->request['description_ch'])) && $rules['description_ch'] = 'required|string';
                (!empty($this->request['description_en'])) && $rules['description_en'] = 'required|string';
                $data = $this->request->toArray() + ['id' => $this->dataId];
                break;
            case 'destroy':
                $rules = [
                    'id' => 'required|exists:kkdays_airport_type_codes,id',
                ];
                $data = ['id' => $this->dataId];
                break;
        }

        $this->response = self::validate($data, $rules, $this->changeErrorName);

        return $this;
    }

    public function list()
    {
        $this->response = KkdayService::response(
            '00',
            'ok',
            LanguageCode::select(
                'id',
                'type',
                'description_ch as descriptionCh',
                'description_en as descriptionEn',
            )->get()
                ->map(function ($item) {
                    $item['descriptionEn'] = ($item->descriptionEn === null) ? '' : $item->descriptionEn;
                    $item['descriptionCh'] = ($item->descriptionCh === null) ? '' : $item->descriptionCh;
                    return $item;
                })
                ->toArray()
        );
        return $this;
    }
    public function store()
    {
        if (!empty($this->response)) return $this;
        LanguageCode::create($this->request->toArray());
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function update()
    {
        if (!empty($this->response)) return $this;
        LanguageCode::where('id', '=', $this->dataId)
            ->update($this->request->toArray());
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function destroy()
    {
        if (!empty($this->response)) return $this;
        LanguageCode::where('id', '=', $this->dataId)->delete();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function import()
    {
        $data = KkdayService::loadJsonData(base_path() . '/app/Services/Kkday/LanguageCode.json');
        $now = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) {
            $data[$key]['description_ch'] = $value['description'];
            $data[$key]['created_at'] = $now;
            $data[$key]['updated_at'] = $now;
            unset($data[$key]['description']);
        }
        LanguageCode::insert($data);
        $this->response = KkdayService::response('00', 'ok');

        return $this;
    }

    public function getResponse(): object
    {
        return $this->response;
    }
    public function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }
}
