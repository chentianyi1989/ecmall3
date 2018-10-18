<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use DB;
class ShopCategory extends BaseModel{

    protected $connection = 'shop';
    protected $table      = 'category_store';
    public    $timestamps = false;

    public static function getShopCatId($id)
    {
        return ShopCategory::where('store_id', $id)->value('cate_id');
    }

    public static function getShopByCatId($id)
    {
        return ShopCategory::where('cate_id', $id)->lists('store_id')->toArray();
    }

}
