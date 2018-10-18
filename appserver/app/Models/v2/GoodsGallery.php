<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;


class GoodsGallery extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'goods_image';
    public    $timestamps = false;

    /**
     * 商品图片
     * @param  [type] $id [description]
     * @return [type]           [description]
     */
    public static function getPhotosById($id)
    {   
        $goods_images = [];

        $model = self::where('goods_id', $id)->get();

        if (!$model->IsEmpty())
        {
            foreach ($model as $value) {
                $goods_images[] = formatPhoto($value->image_url, $value->thumbnail);
            }
        }

        return $goods_images;
    }
    
}
