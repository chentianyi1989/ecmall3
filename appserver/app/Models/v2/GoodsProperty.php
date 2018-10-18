<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

class GoodsProperty extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'goods_spec';
    protected $primaryKey = 'spec_id';
    public    $timestamps = false;

    protected $appends = ['id', 'attr_name', 'attr_price', 'is_multiselect'];
    protected $visible = ['id', 'attr_name', '`', 'is_multiselect'];


    public function goods()
    {
        return $this->belongsTo('App\Models\v2\Goods', 'goods_id', 'goods_id');
    }

    public static function getPropertiesOfSpec($spec_num, $goods_id)
    {
        $spec = self::where('goods_id', $goods_id)->whereNotNull('spec_'.$spec_num)->groupBy('spec_'.$spec_num)->get();
        //设置参数
        foreach ($spec as $key => $value) {
            $value->params = $spec_num;
        }
        return $spec;
    }

    public static function checkStock($id, $quantity)
    {
        $stock = self::where('spec_id', $id)->value('stock');
        if ($stock >= $quantity) {
            return true;
        } else {
            return false;
        }
    }

    public static function getPropertiesOfGoods($goods_id, $property)
    {
         $property_arr = json_decode($property, true);

        //如果商品有属性
        if(!empty($property_arr[0])){
            if(!empty($property_arr[1])){
                $spec_1 = self::where('spec_id', $property_arr[0])->first();
                $spec_2 = self::where('spec_id', $property_arr[1])->first();
                $property = self::where('goods_id', $spec_1->goods_id)->where('spec_1', $spec_1->spec_1)->where('spec_2', $spec_2->spec_2)->value('spec_id');
            }else{
                $property = $property_arr[0];
            }

            if(empty(GoodsProperty::where(['goods_id' => $goods_id, 'spec_id' => $property])->first()))
            {
                 return false;
            }
        }
        //如果商品没有属性
        if(empty($property_arr))
        {
            $property = GoodsProperty::where('goods_id', $goods_id)->value('spec_id');
        }

        return $property;
    }

    //set
    public function setParamsAttribute($spec_num)
    {
        $this->attributes['params'] = ['spec_num' => $spec_num];
    }

    //get
    public function getIdAttribute()
    {
        return $this->attributes['spec_id'];
    }

    public function getAttrNameAttribute()
    {
        extract($this->attributes['params']);
        if(!empty($spec_num)){
            return $this->attributes['spec_'.$spec_num];
        }
        return null;
    }    

    public function getAttrPriceAttribute()
    {
        return 0;
    }    

    public function getIsMultiselectAttribute()
    {
        return false;
    }

}
