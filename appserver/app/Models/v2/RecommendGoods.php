<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class RecommendGoods extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'recommended_goods';
    public    $timestamps = false;

    public static function getRecommendGoods()
    {
        return RecommendGoods::whereIn('recom_id', RecommendCategory::getRecommendCategoryId())->lists('goods_id')->toArray();
    }

}
