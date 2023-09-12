<?php

namespace App\Services;

class Error
{
    private static $message = [
        "id.required" => "01 id",
        "id.integer" => "01 id",
        "use.required" => "01 use",
        "use.boolean" => "01 use",
        "name.string"=>"01 name",
        "type.required" => "01 type",
        "type.string" => "01 type",
    ];
    public static function message()
    {
        return self::$message;
    }
    public function setErrorMessage($name_array){

    }
}
