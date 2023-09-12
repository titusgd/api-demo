<?php

namespace App\Traits;

use App\Models\Account\Notice;
use App\Models\Account\NoticeUser;
use Illuminate\Support\Facades\DB;

trait NotifyTrait
{
    public $link = [];
    /** getNoticeModel
     *  取得Notice模組
     */
    public function getNoticeModel()
    {
        return new Notice;
    }
    /** getNoticeUserModel
     *  取得NoticeUser模組
     */
    public function getNoticeUserModel()
    {
        return new NoticeUser;
    }
    /** addNotice
     *  新增通知
    */
    public function addNotice(string $title, string $content, array $recipient, bool $close_type = false)
    {
        $notice = $this->createNotice(
            $title,
            $content,
            $close_type,
        );

        $notice_user = $this->createNoticeUser(
            $notice->id,
            $recipient
        );
        return $notice;
    }
    /** updateNoticeLink
     *  更新連結
     */
    public function updateNoticeLink(int $notice_id, string $link_str)
    {
        $notice = Notice::find($notice_id);
        $notice->link = $link_str;
        $notice->save();
    }
    /** updateNoticeCloseType
     *  更新關閉狀態
     *  
     */
    public function updateNoticeCloseType(int $notice_id, bool $close_type)
    {
        $notice = Notice::find($notice_id);
        $notice->close_type = $close_type;
        $notice->save();
    }
    /** updateNoticeTypeAndFkId()
     *  更新關聯 type 與 外建 
     */
    public function updateNoticeTypeAndFkId($notice_id, $type, $fk_id)
    {
        $notice = Notice::find($notice_id);
        $notice->type = $type;
        $notice->fk_id = $fk_id;
        $notice->save();
    }
    /** updateNoticeUserTypeAndFkId()
     *  更新關聯 type 與 外建 
     */
    public function updateNoticeUserTypeAndFkId($notice_id, $type, $fk_id)
    {
        NoticeUser::where('notice_id', '=', $notice_id)->update(['type' => $type, 'fk_id' => $fk_id]);
    }
    /** closeNotice
     *  關閉 主表通知
     */
    public function closeNotice($notice_id, $type = null, $fk_id = null)
    {
        $query = Notice::where('id', '=', $notice_id);
        (!empty($type)) && $query = $query->where('type', '=', $type);
        (!empty($fk_id)) && $query = $query->where('fk_id', '=', $fk_id);

        $query = $query->update(['close_type' => 1]);
    }
    /** closeNoticeAndNoticeUser()
     *  關閉所有通知 notices notice_users
     */
    public function closeNoticeAndNoticeUser($type, $fk_id, $user_id)
    {
        $notice_user = NoticeUser::select('notice_id')
            ->where('type', '=', $type)
            ->where('fk_id', '=', $fk_id)
            ->where('recipient_id', '=', $user_id)
            ->first();

        if (!empty($notice_user)) {
            $update_notice_user = NoticeUser::where('notice_id', '=', $notice_user->notice_id)
                ->update(['close_type' => 1]);
            $update_notice = Notice::where('id', '=', $notice_user->notice_id)->update(['close_type' => 1]);
        }
    }

    /** createNotice 
     *  新增 notice data
     *  @return object notice data value
     */
    public function createNotice(string $title, string $content, string $link = null, bool $close_type = false)
    {
        $notice = new Notice();
        $notice->store_id = auth()->user()->store_id;
        // 預設為:系統
        $notice->user_id = '0';
        $notice->title = $title;
        $notice->content = $content;
        if (!empty($link)) {
            $notice->link = $link;
        }
        // close_type (default)false : 未結案 | true:結案
        $notice->close_type = $close_type;
        $notice->type = '';
        $notice->fk_id = 0;
        $notice->save();
        return $notice;
    }

    /** createNoticeUser
     *  新增 notice_users 資料
     *  
     */
    public function createNoticeUser(int $notice_id, array $users, int $forwarder_id = null)
    {
        $data = [];
        $date_time = new \DateTime();
        // 發信預設為:系統
        array_push($users, '0');
        $users = array_unique($users);
        foreach ($users as $key => $val) {
            $data[$key]['notice_id'] = $notice_id;          // 預設為系統
            $data[$key]['recipient_id'] = $val;             // 收件者
            $data[$key]['forwarder_id'] = $forwarder_id;
            $data[$key]['close_type'] = false;
            $data[$key]['created_at'] = $date_time;
            $data[$key]['updated_at'] = $date_time;
            $data[$key]['type'] = '';
            $data[$key]['fk_id'] = 0;
        }

        $notice_user = new NoticeUser();
        $notice_user->insert($data);
    }
    /** selectNoticeUserWhereInId
     *  使用notice_id(int)、recipient_id(array)查詢 notice_users 資料表中。
     *  @param int $notice_id `notice`.id
     *  @param array user_id [value1,value2...
     *  @return object notice_users data
     */
    public function selectNoticeUserWhereInId(array $users, int $notice_id): object
    {
        $notice_users = NoticeUser::select('*')->where('notice_id', '=', $notice_id)->whereIn('recipient_id', $users)->get();
        return $notice_users;
    }

    // public function getLink()
    // {
    //     return $this->link;
    // }

    // public function setLink(array $link)
    // {
    //     $this->link = $link;
    // }
}
