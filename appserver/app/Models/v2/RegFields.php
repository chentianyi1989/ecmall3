<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

class RegFields extends BaseModel {

    public static function findAll()
    {
        return self::formatBody(['signup_field' => [] ]);
    }

}