<?php

namespace App\Traits;

use App\Models\Administration\Review;

trait ReviewTrait
{
    private $review_status_list = [
        // 1  => 'pending',        // 未處理 | 未審核
        // 12 => "fail",           // 未通過 | 未核准
        // 15 => 'approval',       // 核准
        1  => 'pending',        // 未處理 | 未審核
        2 => "fail",           // 未通過 | 未核准
        3 => 'approval',       // 核准
    ];

    private $rank_data = [
        'application' => [
            "3" => [
                "1" => "23",     // 老闆娘
                "2" => "22",     // 老闆
                "3" => "18"      // 會計
            ]
        ],
        'paymentVoucher' => [
            '3' => [
                "1" => "23",     // 老闆娘
                "2" => "22",     // 老闆
                "3" => "18",    // 會計
            ],
            '2' =>
            [
                "1" => "23",     // 老闆娘
                "2" => "18"     // 會計
            ]
        ],
        'leaveDayoff' => [
            "3" => [
                "1" => "23",     // 老闆娘
                "2" => "22",     // 老闆
                "3" => "17",     // 管理者
            ]
        ]
    ];

    /** getReviewModel()
     *  取得review 的model
     *  @return object
     */
    public function getReviewModel()
    {
        return new Review;
    }


    /** addReview()
     *  新增 `reviews` 資料,status 預設為 1.未審核
     *  @param int $status 1:未審核 | 2:未通過 | 3:通過
     *  @return array reviews.id
     */
    public function addReview(array $rank, int $fk_id, string $type, string $status = "1", string $note = "")
    {
        $add_result = $this->createReview($rank, $fk_id, $type, $status, $note);
        return $add_result;
    }

    /** createReview()
     *  新增 `reviews` 資料,status 預設為 1.未審核
     */
    public function createReview(array $rank, int $fk_id, string $type, string $status = "1", string $note = "")
    {
        $temp = [];
        foreach ($rank as $key => $val) {
            // for ($i = 0; $i < $rank; $i++) {
            $review  = new Review;
            $review->rank = $key;
            $review->fk_id = $fk_id;
            $review->type = $type;
            $review->status = $status;
            $review->note = $note;
            $review->user_id = $val;
            $review->date = null;
            $review->save();
            array_push($temp, $review->id);
        }
        return $temp;
    }

    /** numberToStatus()
     *  狀態:代碼轉文字
     */
    public function numberToStatus(int $number): string
    {
        return $this->review_status_list[$number];
    }
    /** statusToNumber()
     *  狀態:文字轉代碼
     */
    public function statusToNumber(string $str): int
    {
        return array_search($str, $this->review_status_list);
    }

    /** getReviewStatusList()
     *  取得狀態列表
     */
    public function getReviewStatusList(): array
    {
        return $this->review_status_list;
    }
    /** getReviewList()
     *  取得簽核人員列表
     */
    public function getReviewList()
    {
        return $this->rank_data;
    }

    public function getReviewRank($type, $rank)
    {
        return $this->rank_data[$type][$rank];
    }

    public function rank_data()
    {
        return $this;
    }

    public function selectReviewsUserList(string $type, int $fk_id, bool $to_array = true): array
    {
        $reviews = Review::select('id', 'user_id', 'status', 'rank')
            ->where('fk_id', '=', $fk_id)
            ->where('type', '=', $type)
            ->orderBy('rank', 'desc')
            ->get();
        if ($to_array == true) $reviews = $reviews->toArray();

        return $reviews;
    }
    public function selectReviewRank(string $type, int $fk_id, int $user_id, bool $to_array = true)
    {
        $reviews = Review::select('id', 'user_id', 'status', 'rank','note')
            ->where('fk_id', '=', $fk_id)
            ->where('type', '=', $type)
            ->where('user_id', '=', $user_id)
            ->orderBy('rank', 'desc')
            ->first();
        if ($to_array == true && (!empty($reviews))) $reviews = $reviews->toArray();

        return $reviews;
    }

    public function updateReview(string $type, int $fk_id, string $status, $note = null)
    {
        // 資料查詢
        $review = $this->selectReviewRank($type, $fk_id, auth()->user()->id);
        // 如果人員資料存在 則進行狀態變更
        if (!empty($review)) {
            $review_update = Review::find($review['id']);
            $review_update->status = $this->statusToNumber($status);
            $review_update->date = date("Y-m-d H:i:s");
            if (!empty($note)) $review_update->note = $note;
            $review_update->save();
        }
    }

    public function cancelReview(string $type, int $fk_id)
    {
        // 資料查詢
        $review = $this->selectReviewRank($type, $fk_id, auth()->user()->id);
        // 如果人員資料存在 則進行狀態變更
        if (!empty($review)) {
            $review_update = Review::find($review['id']);
            $review_update->status = 1;
            $review_update->date = null;
            $review_update->save();            
        }
    }
}
