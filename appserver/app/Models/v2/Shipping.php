<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;
use App\Services\Shopex\Logistics;

class Shipping extends BaseModel
{
    protected $connection = 'shop';
    protected $table = 'shipping';
    public $timestamps = false;

    protected $appends = ['id', 'code', 'name', 'desc', 'price', 'fee', 'is_additional'];
    protected $visible = ['id', 'code', 'name', 'desc', 'price', 'fee', 'is_additional'];

    public static $attrs;

    public static function findAll(array $attributes)
    {
        self::$attrs = $attributes;
        extract($attributes);

        if( !isset($shop) || !isset($products) || json_decode($products, true) == null )
        {
            return self::formatError(self::BAD_REQUEST);
        }

        $model = Shipping::where(['store_id'=> $shop, 'enabled' => 1])->orderBy('sort_order', 'ASC')->get();

        if(count($model) > 0){
            $model = $model->toArray();
            return self::formatBody(['vendors' => $model]);
        }

        return self::formatError(self::BAD_REQUEST, trans('message.shipping.error'));
    }


    public static function shipFee($goods, $shipping_id)
    {
        //格式化，拿到需要的goods_id 和数量
        $products = [];
        foreach ($goods as $key => $value) {
            $products[$key]['goods_id'] = $value['goods_id'];
            $products[$key]['num'] = $value['num'];
        }
        $products = json_encode($products);
        self::$attrs = ['products' => $products];

        $model = Shipping::where(['shipping_id'=> $shipping_id, 'enabled' => 1, ''])->first();

        if(!empty($model)){
            return $model->fee;
        }
        return false;
    }

    //物流信息查询
    public static function getDeliveyInfo(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        if ($order = Order::where('order_id', $order_id)->where('buyer_id',$uid)->first()) {

            $format_data = Logistics::info($order_id);
            if (empty($format_data)) {
                $format_data = [];
            }

            $shipping_name = OrderExtm::where('order_id', $order_id)->value('shipping_name');

            return self::formatBody(['status' => $format_data, 'vendor_name' => $shipping_name, 'code' => $order->invoice_no ]);

        }
        return self::formatError(self::NOT_FOUND);
    }

    private static function calculateFee($num, $first_fee, $step_fee)
    {
        $num = ceil($num);
        if($num == 1)
        {
            return $first_fee;

        }else if($num > 1)
        {
            return $first_fee + ($num -1) * $step_fee;
        }

        return 0;
    }

    //getter
    public function getIdAttribute()
    {
        return $this->attributes['shipping_id'];
    }

    public function getCodeAttribute()
    {
        return null;
    }

    public function getNameAttribute()
    {
        return $this->attributes['shipping_name'];
    }

    public function getDescAttribute()
    {
        return $this->attributes['shipping_desc'];
    }

    public function getPriceAttribute()
    {
        $price['first'] = $this->attributes['first_price'];
        $price['step']  = $this->attributes['step_price'];

        return $price;
    }

    public function getFeeAttribute()
    {
        $uid = Token::authorization();
        extract(self::$attrs);
        $num = 0;

        //如果传products对象 json后数组
        if(isset($products)){
            $products = json_decode($products, true);
            foreach ($products as $product) {
                $num += $product['num'];
            }
        }

        return self::calculateFee($num, $this->attributes['first_price'], $this->attributes['step_price']);
    }

    public function getIsAdditionalAttribute()
    {
        return false;
    }
}