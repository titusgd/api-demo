<?php

namespace App\Services\Ticket;

use App\Models\Ticket\TicketProductBase;
use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;

use App\Models\Ticket\TicketTheme;
use App\Models\Ticket\TicketThemeProduct;

/**
 * Class Theme.
 */
class ThemeService extends Service
{

    use ResponseTrait;
    use RulesTrait;
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
            case "store":
                $rules = [
                    'name' => 'required|string',
                ];
                $data = $this->request->toArray();

                break;
            case "update":
                $rules = [
                    'name' => 'required|string',
                ];
                $data = $this->request->toArray();

                break;
            case "sort":
                $rules = [
                    'data' => 'required|array',
                ];
                $data = $this->request->toArray();
                break;
            case 'use':
                $rules = [
                    'id' => 'required|integer|exists:*.ticket_themes,id',
                    'use' => 'required|boolean',
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
        $res = TicketTheme::orderBy('sort')->get();

        $data = [];

        foreach ($res as $key => $value) {

            $lists = [];
            $themeProduct = TicketThemeProduct::where('theme_id', $value->id)->get();
            foreach ($themeProduct as $k => $v) {
                $product = TicketProductBase::where('prod_no', $v->prod_no)->first();
                $lists[$k] = $product->toArray();
                $tag = $lists[$k]['tag'];
                $lists[$k]['tag'] = json_decode($tag);
                $countries = $lists[$k]['countries'];
                $lists[$k]['countries'] = json_decode($countries);
                unset($lists[$key]['created_at']);
                unset($lists[$key]['updated_at']);
            }

            $data[$key] = [
                'id' => $value->id,
                'name' => $value->name,
                'use' => $value->status,
                'list' => $lists,
            ];
        }

        self::setOk($data);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;

        $theme = new TicketTheme();
        $theme->name = $this->request['name'];
        $theme->status = 1;
        $theme->save();

        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;

        $theme = TicketTheme::find($this->dataId);
        $theme->name = $this->request['name'];
        $theme->save();

        self::setOk();
        return $this;
    }

    public function sort()
    {
        if (!empty(self::getResponse())) return $this;

        $data = $this->request['data'];

        foreach ($data as $key => $value) {
            $model = TicketTheme::find($value);
            $model->sort = $key;
            $model->save();
        }

        self::setOk();
        return $this;
    }

    public function use()
    {
        if (!empty(self::getResponse())) return $this;

        $model = TicketTheme::find($this->request['id']);
        $model->status = $this->request['use'] == true ? 1 : 0;
        $model->save();

        self::setOk();
        return $this;
    }
}
