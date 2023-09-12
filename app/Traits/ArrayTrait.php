<?php
namespace App\Traits;
use Illuminate\Support\Str;

trait ArrayTrait{
    /**
     * arrayKeySnakeToHump(array $snake_array)
     * 將陣列的key，蛇型命名轉換為大駝峰命名法。
     */
    public function arrayKeySnakeToHump(array $snake_array): array
    {
        return collect($snake_array)
            ->mapWithKeys(function ($item, $key) {
                return [Str::studly($key) => $item];
            })
            ->toArray();
    }
    /**
     * arrayKeyHumpToSnake(array $hump)
     * 將陣列的key，大駝峰命名轉換為蛇型命名法。
     */
    public function arrayKeyHumpToSnake(array $hump): array
    {
        return collect($hump)
            ->mapWithKeys(function ($item, $key) {
                return [Str::snake($key) => $item];
            })
            ->toArray();
    }
}