<?php
namespace App\Traits;
/** CountTimeTrait
 *  取得程式執行時間
*/
trait CountTimeTrait{
    private $count_time=0;
    /** time_start()
     *  程式開始時間。
     *  @return void
     */
    public function time_start()
    {
        $this->count_time = microtime(true);
    }
    /** time_end()
     *  程式結束時間。
     *  @return void
     */
    public function time_end()
    {
        $this->count_time = microtime(true) - $this->count_time;
    }
    /** getCountTime()
     *  取得程式執行時間。
     *  @return string
     */
    public function getCountTime()
    {
        return $this->count_time;
    }
}