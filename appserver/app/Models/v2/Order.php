<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;
use App\Helper\Token;
use DB;

class Order extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order';
    protected $primaryKey = 'order_id';
    public    $timestamps = false;

    protected $appends = ['id', 'sn', 'total', 'payment', 'shipping', 'invoice', 'coupon', 'score', 'use_score', 'cashgift', 'consignee', 'status', 'created_at', 'updated_at', 'canceled_at', 'paied_at', 'shipping_at', 'finish_at','discount_price','promos'];
    protected $visible = ['id', 'sn', 'total', 'goods', 'payment', 'shipping', 'invoice', 'coupon', 'score', 'use_score', 'cashgift', 'consignee', 'status', 'created_at', 'updated_at', 'canceled_at', 'paied_at', 'shipping_at', 'finish_at','discount_price','promos', 'shop'];

    // ECM 订单状态
    const STATUS_CREATED     = 0; // 待付款
    const STATUS_PAID        = 1; // 已付款
    const STATUS_DELIVERING  = 2; // 发货中
    const STATUS_DELIVERIED  = 3; // 已收货，待评价
    const STATUS_FINISHED    = 4; // 已完成
    const STATUS_CANCELLED   = 5; // 已取消

    /* 订单状态 */
    const ORDER_SUBMITTED = 10; // 针对货到付款而言，他的下一个状态是卖家已发货
    const ORDER_PENDING   = 11; // 等待买家付款
    const ORDER_ACCEPTED  = 20; // 买家已付款，等待卖家发货
    const ORDER_SHIPPED   = 30; // 卖家已发货
    const ORDER_FINISHED  = 40; // 交易成功
    const ORDER_CANCELED  = 0; // 交易已取消

    public static $reasonLists  = ['不想要了', '支付不成功', '价格较贵', '缺货', '等待时间过长', '拍错了', '订单信息填写错误', '其它'];

    public static function getReasonList()
    {
        $data = [];
        foreach (self::$reasonLists as $key => $value) {
            $data[] = [
                'id' => $key + 1,
                'name' =>  $value
            ];
        }

        return self::formatBody(['reasons' => $data]);
    }

    public static function getReasonByID($id)
    {
        $id = $id - 1;
        return self::$reason[$id];
    }

    public static function findUnpayedBySN($sn)
    {
        return self::where(['order_sn' => $sn, 'status' => Order::ORDER_PENDING])->first();
    }

    public static function getList(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        $model = self::where(['buyer_id' => $uid])->with('shop');

        if (isset($status)) {
            switch ($status) {

                case self::STATUS_CREATED:
                    $model->where('status', self::ORDER_PENDING);
                    break;

                case self::STATUS_PAID:
                    $model->where('status', self::ORDER_ACCEPTED);
                    break;

                case self::STATUS_DELIVERING:
                    $model->where(function ($query) {
                        $query->where('status', self::ORDER_SHIPPED);
                    });
                    break;

                case self::STATUS_DELIVERIED:
                    $model->where('status', self::ORDER_FINISHED);
                    $model->where('evaluation_status', 0);
                    break;

                case self::STATUS_FINISHED:
                    $model->where('status', self::ORDER_FINISHED);
                    break;
            }
        }

        $total = $model->count();

        $data = $model->with('goods')
              ->orderBy('add_time', 'DESC')
              ->paginate($per_page)->toArray();


        return self::formatBody(['orders' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getInfo(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        if ($model = self::where(['buyer_id' => $uid, 'order_id' => $order])->with(['goods', 'shop'])->first()) {
            $model->toArray();


            return self::formatBody(['order' => $model]);
        }

        return self::formatError(self::NOT_FOUND);
    }

    public static function confirm(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();
        //判断订单是否有效
        if (!$order = Order::where(['order_id' => $order, 'buyer_id' => $uid, 'status' => self::ORDER_SHIPPED])->first()) {
            return self::formatError(self::NOT_FOUND);
        }

        //修改订单状态
        $order->status = self::ORDER_FINISHED;
        $order->finished_time = time();
        if ($order->save())
        {
            return self::formatBody(['order' => $order->toArray()]);
        }
        return self::formatError(self::UNKNOWN_ERROR);
    }

    public static function price(array $attributes)
    {
        return Cart::_checkout($attributes, 'pre_price');
    }

    public static function cancel(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();
        //判断订单是否有效
        if (!$order = Order::where(['order_id' => $order, 'buyer_id' => $uid, 'status' => self::ORDER_PENDING])->first()) {
            return self::formatError(self::NOT_FOUND);
        }

        //修改订单状态
        $order->status = self::ORDER_CANCELED;
        if ($order->save())
        {
            // 增加库存
            $order_goods = OrderGoods::where('order_id', $order->order_id)->get();

            foreach ($order_goods as $key => $item) {
                GoodsProperty::where('spec_id', $item->spec_id)->increment('stock', $item->quantity);
                $goods_statistic =  GoodsStatistics::where('goods_id', $item->goods_id)->first();
                if(empty($goods_statistic)) continue;
                if($goods_statistic->orders > 0){
                     GoodsStatistics::where('goods_id', $item->goods_id)->decrement('orders');
                }                
                if($goods_statistic->sales > $item->quantity){
                     GoodsStatistics::where('goods_id', $item->goods_id)->decrement('sales', $item->quantity);
                }
            }

            OrderLog::addLog($order->order_id, $order->buyer_name, '待付款', '已取消', $reason);
            return self::formatBody(['order' => $order->toArray()]);
        }
        return self::formatError(self::UNKNOWN_ERROR);
    }

    public static function review(array $attributes, $items)
    {
        extract($attributes);
        $uid = Token::authorization();
        extract($attributes);

        //判断订单是否有效
        if (!$order = Order::where(['order_id' => $order, 'buyer_id' => $uid, 'status' => self::ORDER_FINISHED])->first()) {
            return self::formatError(self::NOT_FOUND);
        }

        foreach ($items as $key => $value) {
            //判断订单商品评价状态
            if ($order_goods = OrderGoods::where(['order_id' => $order->order_id, 'goods_id' => $value['goods'], 'evaluation' => 0])->first()) {
                //添加评价
                $order_goods->evaluation = $value['grade'];
                $order_goods->comment = strip_tags($value['content']);
                $order_goods->credit_value = ($value['grade'] > 1) ? ($value['grade'] > 2 ? 1 : 0) : -1;

                $order_goods->save();

                //变更店铺总分数
                $shop = Shop::where(['store_id' => $order->seller_id])->first();
                $shop->credit_value = $shop->credit_value + $order_goods->credit_value;
                $shop->save();

                // 更新统计
                GoodsStatistics::updateStat($order_goods->goods_id, 'comments');
            }
        }

        //变更订单评价状态
        $order->anonymous = $is_anonymous;
        $order->evaluation_status = 1;
        $order->evaluation_time = time();
        $order->save();
        return self::formatBody();

    }

    public static function subtotal()
    {
        $uid = Token::authorization();

        $data = [
            'created'    => self::where(['buyer_id' => $uid])->where('status', self::ORDER_PENDING)->count(),
            'paid'       => self::where(['buyer_id' => $uid])->where('status', self::ORDER_ACCEPTED)->count(),
            'delivering' => self::where(['buyer_id' => $uid])->where('status', self::ORDER_SHIPPED)->count(),
            'deliveried' => self::where(['buyer_id' => $uid])->where('status', self::ORDER_FINISHED)->where('evaluation_status', 0)->count(),
            'finished'   => self::where(['buyer_id' => $uid])->where('status', self::ORDER_FINISHED)->count(),
            'cancelled'  => self::where(['buyer_id' => $uid])->where('status', self::ORDER_CANCELED)->count(),
        ];

        return self::formatBody(['subtotal' => $data]);
    }

    public static function getBuyer($order_id)
    {   
        $model = Order::with('buyer')->where('order_id', $order_id)->first();
        if(!empty($model)){
            return $model->buyer;
        }
        return null;
    }

    /**
     * 得到新订单号
     * @return  string
     */
    public static function get_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }


    public static function local_mktime($hour = NULL , $minute= NULL, $second = NULL,  $month = NULL,  $day = NULL,  $year = NULL)
    {
        /**
        * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
        * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
        **/
        $time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;

        return $time;
    }


    private static function convertOrderStatus($status, $evaluation_status=0)
    {
        switch ($status) {
            case self::ORDER_SUBMITTED:
                return self::STATUS_DELIVERING;
                break;

            case self::ORDER_PENDING:
                return self::STATUS_CREATED;
                break;

            case self::ORDER_ACCEPTED:
                return self::STATUS_PAID;
                break;

            case self::ORDER_SHIPPED:
                if ($evaluation_status) {
                    return self::STATUS_DELIVERIED;
                }
                return self::STATUS_DELIVERING;
                break;

            case self::ORDER_FINISHED:
                if ($evaluation_status) {
                    return self::STATUS_FINISHED;
                }
                return self::STATUS_DELIVERIED;
                break;

            case self::ORDER_CANCELED:
                return self::STATUS_CANCELLED;
                break;
        }
    }


    //with
    public function buyer()
    {
        return $this->belongsTo('App\Models\v2\Member','buyer_id','user_id');
    }

    public function goods()
    {
        return $this->hasMany('App\Models\v2\OrderGoods','order_id','order_id');
    }

    public function shop()
    {
        return $this->hasOne('App\Models\v2\Shop','store_id','seller_id');
    }

    //getter
    public function getIdAttribute()
    {
        return $this->attributes['order_id'];
    }

    public function getSnAttribute()
    {
        return $this->attributes['order_sn'];
    }

    public function getTotalAttribute()
    {
        return ($this->attributes['order_amount']);
    }

    public function getPaymentAttribute()
    {
        return [
            'name' => $this->attributes['payment_name'],
            'code' => $this->attributes['payment_code'],
            'desc' => $this->attributes['payment_name'],
        ];
    }

    public function getShippingAttribute()
    {
       $shipping = OrderExtm::where('order_id', $this->attributes['order_id'])->first();
       if(!empty($shipping)){
            return [
                'code' => $shipping->shipping_id,
                'name' => $shipping->shipping_name,
                'desc' => null,
                'price' => $shipping->shipping_fee,
                'tracking' => $this->attributes['invoice_no'],
            ];
       }
       return [];
    }

    public function getInvoiceAttribute()
    {
        return null;
    }

    public function getCouponAttribute()
    {
        return null;
    }

    public function getScoreAttribute()
    {
        return null;
    }

    public function getUseScoreAttribute()
    {
        return null;
    }

    public function getCashgiftAttribute()
    {
        return null;
    }

    public function getConsigneeAttribute()
    {
        $consignee = OrderExtm::where('order_id', $this->attributes['order_id'])->first();
        
        return [
            'name'      => $consignee->consignee,
            'mobile'    => $consignee->phone_mob,
            'tel'       => $consignee->phone_tel,
            'zip_code'  => $consignee->zipcode,
            'regions'   => Region::getRegionName($consignee->region_id),
            'address'   => $consignee->address,
        ];
    }

    public function getStatusAttribute()
    {
        return self::convertOrderStatus($this->attributes['status'], $this->attributes['evaluation_status']);
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['add_time'];
    }

    public function getUpdatedAtAttribute()
    {
        return $this->attributes['add_time'];
    }

    public function getCanceledAtAttribute()
    {
        return OrderLog::getLogTime($this->attributes['order_id'], '已取消');
    }

    public function getPaiedAtAttribute()
    {
        return $this->attributes['pay_time'] ? $this->attributes['pay_time'] : null;
    }

    public function getShippingAtAttribute()
    {
        return $this->attributes['ship_time'] ? $this->attributes['ship_time'] : null;
    }

    public function getFinishAtAttribute()
    {
        return $this->attributes['finished_time'] ? $this->attributes['finished_time'] : null;
    }

    public function getDiscountPriceAttribute()
    {
        return $this->attributes['discount'];
    }

    public function getPromosAttribute()
    {   
        if(!empty($this->attributes['discount'])){
            return [
                ['promo' => 'coupon_reduction' , 'price' => $this->attributes['discount']],
            ];
        }
        return [];
    }

}
