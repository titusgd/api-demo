<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use App\Services\Service;

trait RulesTrait
{

    private $messages = [];
    // 錯誤代碼設定
    private $errorKey = [
        'required' => '01',
        'required_if' => '01',
        'string' => '01',
        'integer' => '01',
        'numeric' => '01',
        'max' => '01',
        'digits' => '01',
        'min' => '01',
        'date' => '01',
        'datetime' => '01',
        'boolean' => '01',
        'exists' => '02',
        'unique' => '03',
        'in' => '01',
        'nullable' => '01',
        'date_format' => '01',
        'after_or_equal' => '01',
        'before' => '01',
        'decimal' => '01',
        'array' => '01'
    ];
    public function validate(array $data, array $rules, array $changeErrorName = null)
    {
        $messages = $this->messages;
        (empty($this->messages)) && $messages = $this->createMessages($rules, $changeErrorName)
            ->toDot()
            ->getMessages();

        // 原始 20230821
        //     $messages = $this->createMessages($rules, $changeErrorName)
        //         ->toDot()
        //         ->getMessages();
        
        return (new Service())->validatorAndResponse(
            $data,
            $rules,
            $messages
        );
    }
    /**
     * createMessages()
     * @param array $rules the rules.
     * @param array $changeKey change message key name.
     */
    public function createMessages(array $rules, array $changeKey = null): self
    {
        $errorKey = $this->errorKey;

        foreach ($rules as $key => $rule) {
            $rule = collect(explode('|', $rule))
                ->map(function ($item) use ($key, &$messages, $errorKey, $changeKey) {
                    try {
                        list($item) = explode(':', $item);
                        $messages[$key][$item] = $errorKey[$item] . ' ' . ((!empty($changeKey[$key])) ? $changeKey[$key] : $key);
                    } catch (\Exception $e) {
                        dd("error", $e, ["data" => [$messages, $item, $key]]);
                    }
                });
        }
        $this->messages = $messages;
        return $this;
    }
    /**
     * toDot() 轉換為dot格式
     */
    public function toDot(): self
    {
        $this->messages = Arr::dot($this->messages);
        return $this;
    }
    // ---------- get and set ----------
    public function setMessages(array $messages): self
    {
        $this->messages = $messages;
        return $this;
    }
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getErrorKey(): array
    {
        return $this->errorKey;
    }

    public function setErrorKey(array $array): self
    {
        $this->errorKey = $array;
        return $this;
    }
}
