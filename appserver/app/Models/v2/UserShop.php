<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class UserShop extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'user_priv';
    public    $timestamps = false;

    public static function getStoreOwner($store_id)
    {
        return self::where('store_id', $store_id)->lists('user_id')->toArray();
    }
}