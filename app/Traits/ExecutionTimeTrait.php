<?php

namespace App\Traits;
/**ExecutionTimeTrait
 * 計算執行時間
 */
trait ExecutionTimeTrait
{
    private $start_time, $end_time, $execution_time;
    /**timeStart()
     * 計時開始
     * @return $this
     */
    public function timeStart()
    {
        $this->start_time = microtime(true);
        return $this;
    }
    /**timeEnd()
     * 計時結束
     * @return $this
     */
    public function timeEnd()
    {
        $this->end_time = microtime(true);
        return $this;
    }
    /**getExecutionTime()
     * 取得計時時間
     * @return string 回傳時間或是錯誤訊息
     */
    public function getExecutionTime()
    {
        if(empty($this->start_time)) return "error : no start time.";
        if(empty($this->end_time)) return "error : no end time.";
        $this->execution_time = $this->end_time - $this->start_time;
        return $this->execution_time;
    }
}
