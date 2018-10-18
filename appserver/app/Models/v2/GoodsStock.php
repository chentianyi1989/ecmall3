<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

class GoodsStock extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'goods_spec';
    protected $primaryKey = 'spec_id';
    public    $timestamps = false;

    protected $appends = ['id', 'goods_attr', 'goods_attr_price', 'stock_number'];
    protected $visible = ['id', 'goods_attr', 'goods_attr_price', 'stock_number'];


    public function goods()
    {
        return $this->belongsTo('App\Models\v2\Goods', 'goods_id', 'goods_id');
    }

    //attrs
    public function getIdAttribute()
    {
        return $this->attributes['spec_id'];
    }

    public function getGoodsAttrAttribute()
    {
        if(!empty($this->attributes['spec_1']) && !empty($this->attributes['spec_2']))
        {
            $spec_1_id = self::where('spec_1', $this->attributes['spec_1'])->where('goods_id', $this->attributes['goods_id'])->value('spec_id');
            $spec_2_id = self::where('spec_2', $this->attributes['spec_2'])->where('goods_id', $this->attributes['goods_id'])->value('spec_id');

            return $spec_1_id.'|'.$spec_2_id;
        }
        return $this->attributes['spec_id'];
    }    

    public function getGoodsAttrPriceAttribute()
    {
        return $this->attributes['price'];
    }    

    public function getStockNumberAttribute()
    {
        return $this->attributes['stock'];
    }

}
