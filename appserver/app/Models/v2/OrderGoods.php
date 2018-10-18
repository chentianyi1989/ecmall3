<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;


class OrderGoods extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_goods';
    protected $primaryKey = 'rec_id';
    public    $timestamps = false;

    protected $appends = ['id', 'product', 'property', 'product_price', 'attachment', 'total_amount', 'total_price', 'is_reviewed'];
    protected $visible = ['id', 'product', 'property', 'product_price', 'attachment', 'total_amount', 'total_price', 'is_reviewed'];


    /**
    * 获取商品好评率
    * @access public
    * @param integer $goods_id
    * @return integer
    */
    public static function getCommentRateById($goods_id)
    {
        $model = self::where('goods_id', $goods_id);
        $total = $model->where('evaluation', '>', 0)->count();
        $favour = $model->where('evaluation', 3)->count();
        
        if($total > 0){
            return round(($favour/$total)*100).'%'; 
        }
        return '0%';
    }

    public function getIdAttribute()
    {
        return $this->attributes['spec_id'];
    }

    public function getProductAttribute()
    {
        return Goods::findOne($this->attributes['goods_id']);
    }

    public function getPropertyAttribute()
    {
        return preg_replace("/(?:\[)(.*)(?:\])/i", '', $this->attributes['specification']);
    }

    public function getProductPriceAttribute()
    {
        return $this->attributes['price'];
    }

    public function getAttachmentAttribute()
    {
        return null;
    }

    public function getTotalAmountAttribute()
    {
        return $this->attributes['quantity'];
    }

    public function getTotalPriceAttribute()
    {
        return number_format($this->attributes['price'] * $this->attributes['quantity'], 2, '.', '');
    }

    public function getIsReviewedAttribute()
    {
        if($this->attributes['evaluation'] > 0){
            return true;
        }
        return false;
    }
}
