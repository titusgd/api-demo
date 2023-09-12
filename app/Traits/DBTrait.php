<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DBTrait
{

    public function createBatch($table_name, $column, $data)
    {
        // INSERT INTO `web_settings` ( `category`, `item`, `value_type`, `restriction`, `default_value`, `created_at`, `updated_at`) VALUES
        // ( 'page', 'dark', 'boolean', '', '0', NULL, NULL),
        // ( 'page', 'menuTop', 'boolean', '', '0', NULL, NULL),
        // ( 'page', 'shortcut', 'boolean', '', '1', NULL, NULL),
        // ( 'table', 'fontSize', 'number', 'max:100|min:1', '14', NULL, NULL),
        // ( 'table', 'rows', 'number', 'max:100|min:1', '20', NULL, NULL),
        // ( 'table', 'padding', 'boolean', '', '0', NULL, NULL),
        // ( 'print', 'fontSize', 'number', 'max:100|min:0', '14', NULL, NULL);
        $col = '';
        foreach ($column as $val) {
            $col .= '`' . $val . '`,';
        }
        $col = substr($col, 0, -1);

        $data_str = '';
        foreach ($data as $val) {
            $data_str .= '(';
            foreach ($val as $val2) {
                $data_str .= ($val2 != null) ? '\'' . $val2 . '\',' : '';
                $data_str .= ($val2 == null) ? ',' : '';
                // if($val2 != null){
                //     $data_str .= '\''.$val2.'\',';
                // }
                // if($val2 == null){
                //     $data_str .= ',';
                // }
            }
            $data_str = substr($data_str, 0, -1);
            $data_str .= '),';
        }

        $data_str = substr($data_str, 0, -1);
        $query =  "INSERT INTO `{$table_name}` ({$col}) VALUES {$data_str};";
        // $db = DB::reconnect('restaurant');

        DB::insert(
            DB::raw($query)
        );
        // return $db;
    }

    /** updateBatch()
     *  批次更新
     *  例:
     *  updateBatch('accesses',[['id'=>1,'name'=>'名字1','group_id'=>'3'],['id'=>2,'name'=>'名字2','group_id'=>'3']])
     * 
     *  @param string $table_name 資料表名稱
     *  @param array $update_data 更新資料內容 
     *  @return void
     */
    public function updateBatch($table_name, $update_data)
    {
        $temp_col = [];
        foreach ($update_data as $val) {
            $temp_col = array_merge($temp_col, array_keys($val));
        }
        $temp_col = array_unique($temp_col);
        // 排除'id'
        $temp_col = array_diff($temp_col, ['id']);
        $query = 'UPDATE ' . $table_name . ' SET ';
        foreach ($temp_col as $val) {
            $query .=  $val . ' = CASE id';
            foreach ($update_data as $val2) {
                $query .= ' WHEN ' . $val2['id'] . ' THEN \'' . $val2[$val] . '\'';
            }
            $query .=  ' END,';
        }

        $query = substr($query, 0, -1);
        $update_id = implode(',', array_column($update_data, 'id'));
        $query .= " WHERE id IN ({$update_id})";

        DB::update(DB::raw($query));
    }

    // /** deleteData($table_name,$where_array)
    //  *  @brief 刪除資料
    //  *  @param string $table_name 資料表名稱
    //  *  @param array $where_array 查詢條件，目前可使用where、orWhere、whereIn、whereNotIn 例:['where'=>[['id','=','2'],['name','like','aa']...]]
    //  *  @return void
    //  */
    // public function deleteData($table_name, $where_array)
    // {
    //     $table = DB::table($table_name);
    //     // ->where('id','=','9')
    //     $addwhere = function ($condition, $where_type) use (&$table) {
    //         foreach ($condition as $val) {
    //             $table = $table->$where_type($val[0], $val[1], $val[2]);
    //         }
    //     };
    //     // ->whereIn('id,[1,2,3,4])
    //     $addwhereIn = function ($condition, $whereType) use (&$table) {
    //         foreach ($condition as $val) {
    //             $table = $table->$whereType($val[0], array_filter($val[1]));
    //         }
    //     };
    //     foreach ($where_array as $key => $val) {
    //         switch ($key) {
    //             case "where":
    //                 $addwhere($val, $key);
    //                 break;
    //             case "whereIn":
    //                 $addwhereIn($val, $key);
    //                 break;
    //             case "orWhere":
    //                 $addwhere($val, $key);
    //                 break;
    //             case "whereNotIn":
    //                 $addwhereIn($val, $key);
    //                 break;
    //         }
    //     }
    //     $table->delete();
    // }

}
