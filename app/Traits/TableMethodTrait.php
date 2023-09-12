<?php

namespace App\Traits;

use App\Services\Files\ImageUploadService;

trait TableMethodTrait
{
    /**sort()
     * 排序
     * @param Model $model 資料模型
     * @param array $sort_data 排序資料
     * @param string $where_column_name 索引查詢欄位名稱
     * @return void
     */
    public function sort(Object $model, array $sort_data, String $where_column_name): void
    {
        // 總量
        $count = $model->count();
        // 全部刷為最後一個
        $model->where('id', '>', 0)->update(['sort' => $count]);
        // 依照陣列刷新排序
        foreach ($sort_data as $key => $item) {
            $model->where($where_column_name, '=', $item)
                ->update(['sort' => ($key + 1)]);
        }
    }
    /**use()
     * 變更使用狀態
     * @param Model $model 資料模型
     * @param int $id 
     * @param bool status
     * @return void
     */
    public function use(Object $model, int $id, bool $status)
    {
        $model = $model->find($id);
        $model->status = $this->request['use'];
        $model->save();
    }
    /**delAndReSort()
     * 刪除資料及圖片
     * @param object $model 資料模型
     * @param int $id 待刪除id
     * @return void 
     */
    public function del($model, $id, $column_name = 'id')
    {
        // 取得欲刪除資料
        $del_data = $model->where($column_name, '=', $id)->first();
        if (!$del_data) return;
        $model->where($column_name, '=', $id)->delete();
    }
    /**delAndReSort()
     * 刪除資料及圖片
     * @param object $model 資料模型
     * @param int $id 待刪除id
     * @param string $column_name 索引欄位名稱
     * @param bool $del_img 是否移除圖片
     * @param string $image_type 圖片類別
     * @return void 
     */
    public function delAndReSort($model, $id, $column_name = 'id', $del_img = false, $image_type = '')
    {
        // 取得欲刪除資料
        $del_data = $model->where($column_name, '=', $id)->first();
        if (!$del_data) return;
        if ($del_img && ($del_data['image_id'] > 0)) {
            $image_service = new ImageUploadService();
            $image_service->deleteImageFile($id, $image_type);
        }
        $model->where($column_name, '=', $id)->delete();
        $model->where('sort', '>=', $del_data['sort'])->decrement('sort');
    }
}
