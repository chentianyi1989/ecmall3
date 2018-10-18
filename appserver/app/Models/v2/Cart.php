<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;
use App\Helper\heade;
use DB;


class Cart extends BaseModel
{
    protected $connection = 'shop';
    protected $table = 'cart';
    public $timestamps = false;
    protected $primaryKey = 'rec_id';

    protected $appends = ['id','amount','property','price','attr_stock','attrs'];
    protected $visible = ['id','amount','product','property','price','attr_stock','attrs'];


    /**
     * 添加商品到购物车
     *
     * @access  public
     * @param   integer $goods_id   商品编号
     * @param   integer $num        商品数量
     * @param   json   $property       规格值对应的id json数组
     * @return  Array
     */
    public static function add(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        //找到对应货品
        $property = GoodsProperty::getPropertiesOfGoods($product, $property);
        if(empty($property))
        {
            return self::formatError(self::NOT_FOUND);
        }

        //验证商品是否下架
        $goods = Goods::where(['goods_id' => $product, 'if_show' => 1, 'closed' => 0])->first();

        if (!$goods) {
            return self::formatError(self::BAD_REQUEST, trans('message.good.off_sale'));
        }

        // 检查是不是自己的产品
        if (in_array($uid, UserShop::getStoreOwner($goods->store_id))) {
            return self::formatError(self::BAD_REQUEST, trans('message.products.owner'));
        }

        $sql = Cart::Where(['user_id' => $uid, 'spec_id' => $property, 'goods_id' => $product]);
        $model = $sql->with('product')->first();
        
        //如果购物车内没有这个商品  
        if(empty($model))
        {
            // 检查库存
            if (!GoodsProperty::checkStock($property, $amount)) {
               return self::formatError(self::BAD_REQUEST, trans('message.good.out_storage'));
            }

            // 得到规格的描述文字
            $spec_info = GoodsProperty::where('spec_id', $property)->first();
            $spec_1 = $goods->spec_name_1 ? $goods->spec_name_1 . ':' . $spec_info->spec_1 : $spec_info->spec_1;
            $spec_2 = $goods->spec_name_2 ? $goods->spec_name_2 . ':' . $spec_info->spec_2 : $spec_info->spec_2;
            $specification = $spec_1 . ' ' . $spec_2;

            //得到session_id 使用token来代替
            $token = app('request')->header('X-'.config('app.name').'-Authorization');
            

            $data = [   
                'user_id'       => $uid,
                'session_id'    => md5($token),
                'store_id'      => $goods->store_id,
                'goods_id'      => $product,
                'goods_name'    => $goods->goods_name,
                'goods_image'   => $goods->default_image,
                'spec_id'       => $property,
                'specification' => $specification,
                'price'         => $spec_info->price,
                'quantity'      => $amount,
            ];

           $rec_id = self::insertGetId($data);

           $model = self::where('rec_id', $rec_id)->with('product')->first();

           if (empty($model)) {
                return self::formatError(self::UNKNOWN_ERROR);
            }
        }else{

            // 检查库存
            if (!GoodsProperty::checkStock($property, $amount + $model->quantity)) {
               return self::formatError(self::BAD_REQUEST, trans('message.good.out_storage'));
            }

            $flag = $sql->increment('quantity', $amount);

            if (empty($flag)) {
                return self::formatError(self::UNKNOWN_ERROR);
            }
        }


        return self::formatBody(['cart_goods' => $model]);
    }


    /**
     * 购物车结算
     * @param     int     $shop            // 店铺ID(无)
     * @param     int     $consignee       // 收货人ID
     * @param     int     $shipping        // 快递ID
     * @param     string     $invoice_type    // 发票类型，如：公司、个人
     * @param     string     $invoice_content // 发票内容，如：办公用品、礼品
     * @param     string     $invoice_title   // 发票抬头，如：xx科技有限公司
     * @param     int     $coupon          // 优惠券ID (无)
     * @param     int     $cashgift        // 红包ID
     * @param     int     $comment         // 留言
     * @param     int     $score           // 积分
     * @param     int     $cart_good_id    // 购物车商品id数组
     */

    public static function checkout(array $attributes)
    {
        return self::_checkout($attributes);
    }

    /**
     * checkout
     * @param  array  $attributes 
     * @param  string $type       cart,fastbuy,pre_price
     * @return [type]             [description]
     */
    public static function _checkout(array $attributes, $type = 'cart')
    {
        extract($attributes);
        $uid = Token::authorization();
        //得到session_id 使用token来代替
        $token = app('request')->header('X-'.config('app.name').'-Authorization');
        $carts = [];

        switch ($type) {
            case 'cart':

                /* 获取购物车内需要下单的商品 */
                $cart_good_ids = json_decode($cart_good_id);
                foreach ($cart_good_ids as $cart_id) {
                    if (!$cart = self::where(['store_id' => $shop, 'user_id' => $uid, 'rec_id'=> $cart_id])->first()) {
                        return self::formatError(self::BAD_REQUEST, trans('message.cart.cart_goods_error'));
                    }

                    $carts[] = $cart;
                }
                break;
            
            case 'fastbuy':

                $goods = Goods::where(['goods_id' => $product])->first();

                if (empty($goods)) {
                    return self::formatError(self::BAD_REQUEST, trans('message.good.off_sale'));
                }

                // 检查是不是自己的产品
                if (in_array($uid, UserShop::getStoreOwner($goods->store_id))) {
                    return self::formatError(self::BAD_REQUEST, trans('message.products.owner'));
                }

                //找到对应货品
                $property = GoodsProperty::getPropertiesOfGoods($product, $property);
                if(empty($property))
                {
                    return self::formatError(self::NOT_FOUND);
                }

                $spec_info = GoodsProperty::where('spec_id', $property)->first();

                $spec_1 = $goods->spec_name_1 ? $goods->spec_name_1 . ':' . $spec_info->spec_1 : $spec_info->spec_1;
                $spec_2 = $goods->spec_name_2 ? $goods->spec_name_2 . ':' . $spec_info->spec_2 : $spec_info->spec_2;

                $specification = $spec_1 . ' ' . $spec_2;


                // 模拟加入购物车 并不存储数据
                $cart_goods = new Cart;
                $cart_goods->user_id = $uid;
                $cart_goods->session_id = md5($token);
                $cart_goods->store_id = $goods->store_id;
                $cart_goods->goods_id = $goods->goods_id;
                $cart_goods->goods_name = $goods->goods_name;
                $cart_goods->goods_image = $goods->default_image;
                $cart_goods->spec_id = $property;
                $cart_goods->specification = $specification;
                $cart_goods->price = $spec_info->price;
                $cart_goods->quantity = $amount;

                $carts[] = $cart_goods;

                break;

            case 'pre_price':
                /* 获取商品 */
                if (!$order_products = json_decode($order_product, true)) {
                    return self::formatError(self::NOT_FOUND);
                }
                foreach ($order_products as $product) {

                    if (!isset($product['goods_id']) || !isset($product['num'])) {
                        return self::formatError(self::NOT_FOUND);
                    }

                    // 获取商品信息
                    if (!$goods = Goods::where(['goods_id' => $product['goods_id']])->first()) {
                        return self::formatError(self::NOT_FOUND);
                    }
                    $property = json_encode($product['property']);
                    $property = GoodsProperty::getPropertiesOfGoods($product['goods_id'], $property);
                    if (empty($property)) {
                        return self::formatError(self::BAD_REQUEST, trans('message.cart.property_error'));
                    }
                    
                    $spec_info = GoodsProperty::where('spec_id', $property)->first();

                    // 模拟加入购物车 并不存储数据
                    $cart_goods = new Cart;
                    $cart_goods->user_id = $uid;
                    $cart_goods->session_id = md5($token);
                    $cart_goods->store_id = $goods->store_id;
                    $cart_goods->goods_id = $goods->goods_id;
                    $cart_goods->goods_name = $goods->goods_name;
                    $cart_goods->goods_image = $goods->default_image;
                    $cart_goods->spec_id = $property;
                    $cart_goods->specification = '';
                    $cart_goods->price = $spec_info->price;
                    $cart_goods->quantity = $product['num'];

                    $carts[] = $cart_goods;
                }

                break;
        }

        if (count($carts)) {
            //检查库存
            $outstock_goods = [];
            $goods_total = 0;
            $goods_quantity_total = 0;
            foreach ($carts as $cart) {
                if (!GoodsProperty::checkStock($cart->spec_id, $cart->quantity))
                {
                    $outstock_goods[] = $cart->goods_id;
                }
                $goods_total += $cart->price * $cart->quantity;
                $goods_quantity_total += $cart->quantity;
                if (!Goods::checkStatus($cart->goods_id)) {
                   return self::formatError(self::BAD_REQUEST, trans('message.good.off_sale'));
                }
            }

            if (!empty($outstock_goods)) {
                return self::formatError(self::BAD_REQUEST, trans('message.good.out_storage'));
            }

            //检查收货地址
            if (isset($consignee) && $consignee) {
                if ( !$address_model = UserAddress::where('addr_id', $consignee)->first()) {
                    return self::formatError(self::BAD_REQUEST, trans('message.address.error'));
                }
            }else{
                $address_model = false;
            }

            //检查快递信息
            if (isset($shipping) && $shipping) {
                if (!$shipping_model = Shipping::where(['shipping_id' => $shipping, 'store_id' => $shop, 'enabled' => 1])->first()) {
                    return self::formatError(self::BAD_REQUEST, trans('message.shipping.404'));
                }
            }else{
                $shipping_model = false;
            }


            //检查优惠劵 非必填
            if (!empty($coupon)) {
                if (!$coupon_model = Coupon::checkCoupon($shop, $uid, $coupon, $goods_total))
                {
                    return self::formatError(self::BAD_REQUEST, trans('message.coupon.error'));
                }
            } else {
                $coupon_model = false;
            }

            //创建订单
            $order = new Order;

            $order->order_sn = self::genOrderSN();
            $order->type = 'material';
            $order->extension = 'normal';
            $order->seller_id = $shop;
            $order->seller_name = Shop::where('store_id', $shop)->value('store_name');
            $order->buyer_id = $uid;
            $order->buyer_name = Member::where('user_id', $uid)->value('user_name');
            $order->buyer_email = Member::where('user_id', $uid)->value('email');
            $order->status = Order::ORDER_PENDING;
            $order->add_time = time();
            $order->postscript = isset($comment) ? $comment : '';
            $order->evaluation_status = 0;
            $order->payment_id = null;
            $order->payment_name = null;
            $order->payment_code = '';
            $order->pay_time = 0;
            $order->finished_time = 0;
            $order->invoice_no = '';
            $order->ship_time = '';


            //还差 优惠码减金额 优惠码没有使用记录 还有优惠码过期时间 是否使用过了什么的判断
            $order->goods_amount = $goods_total;
            $order->discount = 0;

            /* 配送费用=首件费用＋超出的件数*续件费用 */
            if(!empty($shipping_model))
            {
                $shipping_fee = $shipping_model->first_price + ($goods_quantity_total - 1) * $shipping_model->step_price;
            }else{
                $shipping_fee = 0;
            }
            //减优惠码金额
            if ($coupon_model) {
                $order->order_amount = $shipping_fee + $goods_total - $coupon_model->coupon_value;
                if($order->order_amount < 0)
                {
                    $order->order_amount = 0;
                    $coupon_model->coupon_value = $shipping_fee + $goods_total;
                }

                $order->discount = $coupon_model->coupon_value;

                $promos = [
                    ['promo' => 'coupon_reduction' , 'price' => $coupon_model->coupon_value],
                ];
            } else {
                $order->order_amount = $shipping_fee + $goods_total;
                $order->discount = 0;
                $promos = [];
            }
            //订单0元 直接进入已经付款
            if($order->order_amount == 0)
            {
                $order->status = Order::ORDER_ACCEPTED;
                $order->pay_time = time();
            }

            if ($type == 'pre_price') {


                return self::formatBody([
                                'order_price' => [
                                    'total_price'               => $order->order_amount,                        // 订单总价
                                    'product_price'             => $goods_total,  // 商品总价格
                                    'shipping_price'            => $shipping_fee,                               // 运费
                                    'discount_price'            => 0,                                           // 买了多件商品扣的钱
                                    'promos'                    => $promos                                      // 订单优惠信息
                                ]
                            ]);
            }

            if ($order->save()){
                //写入商品
                foreach ($carts as $key => $cart) {
                    $order_goods = new OrderGoods;
                    $order_goods->order_id = $order->order_id;
                    $order_goods->goods_id = $cart->goods_id;
                    $order_goods->goods_name = $cart->goods_name;
                    $order_goods->spec_id = $cart->spec_id;
                    $order_goods->specification = $cart->specification;
                    $order_goods->price = $cart->price;
                    $order_goods->quantity = $cart->quantity;
                    $order_goods->goods_image = $cart->goods_image;
                    $order_goods->evaluation = 0;
                    $order_goods->comment = '';
                    $order_goods->credit_value = 0;
                    $order_goods->is_valid = 1;
                    if (!$order_goods->save()){
                        self::rollBack($order->order_id);
                    }
                }

                //写入地址 和快递信息
                $order_extm = new OrderExtm;
                $order_extm->order_id = $order->order_id;
                $order_extm->consignee = $address_model->consignee;
                $order_extm->region_id = $address_model->region_id;
                $order_extm->region_name = $address_model->region_name;
                $order_extm->address = $address_model->address;
                $order_extm->zipcode = $address_model->zipcode;
                $order_extm->phone_tel = $address_model->phone_tel;
                $order_extm->phone_mob = $address_model->phone_mob;
                $order_extm->shipping_id = $shipping_model->shipping_id;
                $order_extm->shipping_name = $shipping_model->shipping_name;
                $order_extm->shipping_fee = $shipping_fee;
                if (!$order_extm->save()){
                    self::rollBack($order->order_id);
                }

                //让优惠码失效
                if ($coupon_model) {
                    DB::connection('shop')->table('coupon_sn')
                        ->where('coupon_sn', $coupon_model->coupon_sn)
                        ->decrement('remain_times');
                }


                //减库存 更新下单次数统计
                foreach ($carts as $cart) {
                    GoodsProperty::where('spec_id', $cart->spec_id)->decrement('stock', $cart->quantity);
                    GoodsStatistics::updateStat($cart->goods_id, 'orders');
                    GoodsStatistics::updateStat($cart->goods_id, 'sales', $cart->quantity);
                }

                //清空购物车（从购物车结算）
                foreach ($carts as $cart) {
                    Cart::where(['store_id' => $shop, 'user_id' => $uid, 'rec_id'=> $cart->rec_id ])->delete();
                }

                return self::formatBody(['order' => Order::with('shop')->find($order->id)]);
            }

        } else {
            //购物车是空的
            return self::formatError(self::NOT_FOUND);
        }

    }

    /**
     * 清空购物车
     * 用戶退出時自動清空
     * @param   int     $type   类型：默认普通商品
     */
    public static function clear()
    {
        $uid = Token::authorization();
        Cart::where('user_id',$uid)->delete();

        return self::formatBody();
    }

    /**
     * 刪除購物車商品
     * @return [type] [description]
     */
    public static function remove(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        Cart::where('user_id', $uid)->where('rec_id', $good)->delete();

        return self::formatBody();
    }


    /**
     * 修改購物車商品数量
     * @return [type] [description]
     */
    public static function updateAmount(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();
        if ($model = Cart::Where(['user_id' => $uid, 'rec_id' => $good])->first()) {
            $model->quantity = $amount;

            // 检查库存
            if (!GoodsProperty::checkStock($model->spec_id, $amount)) {
                return self::formatError(self::BAD_REQUEST,trans('message.good.out_storage'));
            }

            if (!$model->save())
            {
                return self::formatError(self::UNKNOWN_ERROR);
            }

            return self::formatBody();

        }
        return self::formatError(self::NOT_FOUND);
    }


    /**
     * 購物車列表
     * @return [type] [description]
     */
    public static function getList()
    {
        $uid = Token::authorization();

        $data = [];
        $stores = self::select('store_id')->distinct()->where('user_id', $uid)->get();
        foreach ($stores as $key => $store) {
                $total_price = 0;
                $data[$key]['shop'] = Shop::where('store_id', $store->store_id)->first();
                $data[$key]['total_amount'] = self::where('store_id', $store->store_id)->where('user_id', $uid)->sum('quantity');

                //购物车内货品
                $products = self::where('store_id', $store->store_id)->where('user_id', $uid)->with('product')->get();

                foreach ($products as $product) {
                    $total_price += $product->price * $product->quantity;
                }

                $data[$key]['goods'] = $products->toArray();
                $data[$key]['total_price'] = $total_price;
        }

        return self::formatBody(['goods_groups' => $data]);
    }

    public static function TotalPrice()
    {
        $uid = Token::authorization();

        $goods =  self::where('user_id', $uid)->orderBy('rec_id','DESC')->get();
        $total = 0;
        foreach ($goods as $key => $good) {
            $total += ($good['goods_number'] * $good['goods_price']);
        }
        return (float)$total;
    }

    private static function genOrderSN()
    {
        // 选择一个随机的方案
        mt_srand((double) microtime() * 1000000);
        $timestamp = time() - date('Z');
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $order = Order::where('order_sn', $order_sn)->first();

        if (empty($order))
        {
            return $order_sn;
        }

        // 如果有重复的，则重新生成
        return self::genOrderSN();
    }
    //getter
    public function product()
    {
        return $this->belongsTo('App\Models\v2\Goods', 'goods_id', 'goods_id');
    }

    //getter
    public function getIdAttribute()
    {
        return $this->attributes['rec_id'];
    }

    public function getAmountAttribute()
    {
        return $this->attributes['quantity'];
    }

    public function getPropertyAttribute()
    {
       return $this->attributes['specification'];
    }

    public function getAttrsAttribute()
    {
        return $this->attributes['spec_id'];
    }

    public function getPriceAttribute()
    {
        return GoodsProperty::where('spec_id', $this->attributes['spec_id'])->value('price');
    }

    public function getAttrstockAttribute()
    {
        return GoodsStock::where('spec_id', $this->attributes['spec_id'])->value('stock');
    }

}