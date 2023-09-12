<?php

namespace App\Services\Hr;

use Illuminate\Http\Request;

use App\Models\Hr\Organize;
use App\Services\Service;

use Exception;

/**
 * Class Organize.
 */
class OrganizeService extends Service
{
    public $validationRules = [
        'name'    => 'required|string',
        'englishName'   => 'required|string',
        'code' => 'required|string',
    ];

    public $validationMsg = [
        'name.required'   => "01 name",
        'name.string'     => "01 name",
        'englishName.required'  => "01 englishName",
        'englishName.string'    => "01 englishName",
        'code.required'   => "01 code",
        'code.string'     => "01 code",
    ];

    public $validationRules_use = [
        'use'   => 'required|boolean',
    ];

    public $validationMsg_use = [
        'use.required'  => "01 use",
        'use.boolean'    => "01 use",
    ];

    public function index()
    {

        $res = $this->build_menu(0);


        // 使用 JSON 查看轉換為多維陣列的結果
        // echo json_encode($tree, JSON_UNESCAPED_UNICODE);

        return Service::response('00', 'OK', $res);
    }

    private function build_menu($menu_id){
        // 查询当前菜单的子菜单
        $submenus = Organize::select(
                    'id',
                    'english_name as englishName',
                    'chinese_name as name',
                    'code',
                    'use'
                )
                    ->where('menu_id', $menu_id)
                    ->orderBy('sn', 'asc')->get();

        // 如果没有子菜单，则返回空数组
        if ($submenus->isEmpty()) {
            return array();
        }

        // 否则，递归处理子菜单
        $menu = array();
        foreach ($submenus as $submenu) {
            $item = array(
                'id' => $submenu->id,
                'name' => $submenu->name,
                'englishName' => $submenu->englishName,
                'code' => $submenu->code,
                'use' => $submenu->use,
                'menu' => $this->build_menu($submenu->id)
            );
            $menu[] = $item;
        }
        return $menu;
    }

    public function create($req)
    {
        try {

            $menu_id = $req['id'] ?? 0;

            $create = Organize::create([
                "chinese_name"   => $req["name"],
                "english_name"  => $req["englishName"],
                'code' => $req["code"],
                'menu_id' => $menu_id,
                "use" => 1
            ]);

            return Service::response('00', 'OK', '');
        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function update($req, $id)
    {
        try {

            $create = Organize::where([
                'id' => $id
            ])
            ->update([
                "chinese_name"   => $req["name"],
                "english_name"  => $req["englishName"],
                'code' => $req["code"]
            ]);

            return Service::response('00', 'OK', '');
        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function delete($id)
    {
        try {

            Organize::where('id', $id)->delete();

            return Service::response('00', 'OK', '');
        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    private function display_org($org, $menu_id = 0, $level = 0)
    {
        if (isset($org[$menu_id])) {
            foreach ($org[$menu_id] as $node) {
                echo str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $node['chinese_name'] . '<br />';
                $this->display_org($org, $node['id'], $level + 1);
            }
        }
    }

    public function sort($data, $id){

        foreach($data as $key => $value){
            $org = Organize::where(
                [
                    'id' => $value,
                    'menu_id' => $id
                ])
                ->update(['sn' => $key]);
        }

        return  Service::response('00', 'OK', '');
    }

    public function use($data){

        Organize::where('id', $data['id'])->update(['use' => $data['use']]);

        return  Service::response('00', 'OK', '');
    }
}
