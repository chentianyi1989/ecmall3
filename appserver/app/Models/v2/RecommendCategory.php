<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class RecommendCategory extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'recommend';
    public    $timestamps = false;

    public static function getRecommendCategoryId()
    {
        $recommend_category_ids = [];
       
        if ($recommend = Configs::where(['code' => 'wap.recommend', 'status' => 1])->first()) {
           $recommend_config = json_decode($recommend->config, true);
           if (!empty($recommend_config['recom_id'])) {
               $recommend_category_ids = [$recommend_config['recom_id']];
           }
        }
        return $recommend_category_ids;
    }

}
