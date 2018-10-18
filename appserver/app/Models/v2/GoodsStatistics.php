<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;

class GoodsStatistics extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'goods_statistics';
    public    $timestamps = false;

    public static function getSales($id)
    {
        return GoodsStatistics::where('goods_id', $id)->value('sales');
    }

    public static function getComments($id)
    {
        return GoodsStatistics::where('goods_id', $id)->value('comments');
    }

    public static function updateStat($id, $key, $value = 1)
    {
    	$model = GoodsStatistics::where('goods_id', $id);
    	
        if ($model->count() == 0) {
            $model = new GoodsStatistics;
            $model->goods_id = $id;
            $model->views = 0;
            $model->collects = 0;
            $model->carts = 0;
            $model->orders = 0;
            $model->sales = 0;
            $model->comments = 0;
            $model->save();
        } 

        $model->increment($key, $value);

        return true;
    }
}
