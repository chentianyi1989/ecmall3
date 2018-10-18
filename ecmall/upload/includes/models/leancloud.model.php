<?php

/* APP推送管理 leancloud */
class LeancloudModel extends BaseModel
{
    var $table  = 'push';
    // var $alias  = 'leancloud_alias';
    var $prikey = 'id';
    var $_name  = 'push';
    var $platform = array();  // 平台类型
    var $links = array(); 


    function getPlatform()
    {
        if (empty($this->platform)) {
            $this->platform = array(
                1 => 'iOS' , 
                2 => 'Android',
                3 => Lang::get('all_platform')
            );
        }
        return $this->platform;
    }

    function getLinks(){
        if (empty($this->links)) {
            $this->links = array(
                'user-defined' => Lang::get('user-defined'),
                'deeplink://goto/index' => Lang::get('goto_index'),
                'deeplink://goto/cart' => Lang::get('goto_cart'),
                'deeplink://goto/search' =>Lang::get('goto_search'),
                'deeplink://goto/category/all' => Lang::get('goto_category_all'),
                'ecnative://goto/shop/all' => Lang::get('goto_shop_all'),
                'ecnative://goto/brand/all' => Lang::get('goto_brand_all'),
        //        'deeplink://goto/notice/all' => '公告列表',
                'deeplink://goto/product/all' => Lang::get('goto_product_all'),
        //        'deeplink://goto/product/:id' => '商品详情' ,
                'deeplink://goto/scanner' => Lang::get('goto_scanner'),
                'deeplink://goto/home' => Lang::get('goto_home'),
                'deeplink://goto/setting' => Lang::get('goto_setting'),
                'deeplink://goto/cardpage/index' => Lang::get('goto_cardpage_index'),
                'deeplink://goto/profile' => Lang::get('goto_profile'),
                'deeplink://goto/address/all' => Lang::get('goto_address_all'),
        //        'deeplink://goto/address/new' => '新建收货地址',
        //        'deeplink://goto/address/:id' =>  '编辑收货地址',
                'deeplink://goto/order/all' => Lang::get('goto_order_all'),
        //        'deeplink://goto/order/created' => '代付款订单',
        //        'deeplink://goto/order/paid' => '待发货订单',
        //        'deeplink://goto/order/delivering' => '发货中订单',
        //        'deeplink://goto/order/delivered' => '待评价订单',
        //        'deeplink://goto/order/finished' => '已完成订单',
        //        'deeplink://goto/order/cancelled' => '已取消订单',
        //        'deeplink://goto/order/:id' => '订单详情',
        //        'ecnative://goto/favorite/shop' => '我收藏的店铺',
                'deeplink://goto/favorite/product' => Lang::get('goto_favorite_product'),
                'deeplink://goto/message/all' => Lang::get('goto_message_all'),
                'deeplink://goto/orderMessage/all' => Lang::get('goto_orderMessage_all'),
                'deeplink://goto/cashgift/available' => Lang::get('goto_cashgift_available'),
        //        'deeplink://goto/cashgift/expired' => '已过期红包列表',
        //        'deeplink://goto/cashgift/used' => '已使用红包列表',
                'deeplink://goto/coupon/available' =>  Lang::get('goto_coupon_available'),
        //        'deeplink://goto/coupon/expired' => '已过期优惠券列表',
        //        'deeplink://goto/coupon/used' => '已使用优惠券列表',
                'deeplink://goto/shipping/:id' => Lang::get('goto_shipping_id'),
                'deeplink://goto/score/all' => Lang::get('goto_score_all'),
        //        'deeplink://goto/score/income' => '收入积分',
        //        'deeplink://goto/score/expenditure' =>  '支出积分',
                'deeplink://goto/article' => Lang::get('goto_article'),
                'deeplink://goto/invoice' => Lang::get('goto_invoice'),
        //        'deeplink://search/shop?k=关键字' => '店铺搜索',
                'deeplink://search/product?k=关键字' => Lang::get('search_product_k'),
            );
        }
        return $this->links;
    }

    /**
     *   推送app消息
     *   @author   Pangxp
     *   @param    int   $id  
     *   @return   bool
     */

    function push($id)
    {
        if (!$id) 
            return false;
        $leancloud_config = $this->getAppLeancloud();
        $leancloud_config = current($leancloud_config);
        if(!$leancloud_config) 
            return false;
        $config = json_decode($leancloud_config['config'], 1);
        if (!$config['app_id'] || !$config['app_key']) {
            return false;
        }
        import('leancloud_client.lib');
        leancloud_client::initialize($config['app_id'], $config['app_key']);
        $pushData = parent::get_info($id);
        if (!$pushData) 
            return false;
        //物流信息定向推送
        if ($pushData['user_id'] > 0) {
            $user_id = $pushData['user_id'];
            $user_info = parent::getRow("SELECT * FROM ". DB_PREFIX . "device WHERE user_id = '$user_id'");
            if ($user_info) {
                $pushData['platform'] = $user_info['platform_type'];
                $device_id = $user_info['device_id'];
            }
        }
        // 推送内容
        $data  = json_encode(['badge' => 'Increment','alert' => $pushData['content'],'title' => $pushData['title'],'link' => $pushData['link'],'action' => $config['package_name']]); 
        // 推送条件
        $where = null; 
        switch($pushData['platform']){
            case '1':
                $where = json_encode(['deviceType' => 'ios']);
                break;
            case '2':
                $where = json_encode(['deviceType' => 'android']);
                break;
            case '3':
                $where = json_encode(['deviceType' => 'all']);
                break;
        }
        //推送时间
        $push_time = strtotime($pushData['push_at']);
        if ( $push_time < time()) {
            $push_time = time();
        }
        if ($pushData['push_type'] == 0) {
            $push_time = null; //即时推送时间不需要
        }else{
            date_default_timezone_set('UTC'); //转换UTC时间给Leancloud
            $push_time = date('Y-m-d\TH:i:s.\0\0\0\Z', $push_time);
        }
        $post = array(
            // "channels" => ['public'], //推送给哪些频道，将作为条件加入 where 对象。
            "data" => json_decode($data,true),// 推送的内容数据，JSON 对象
            "expiration_interval" => 86400, // 消息过期的相对时间，从调用 API 的时间开始算起，单位是「秒」。
            // "expiration_time" => (time() + 86400 * 7), // 消息过期的绝对日期时间
            "prod" => 'prod', // 开发证书（dev）还是生产证书（prod）
            "push_time" => $push_time, // 定期推送时间
        );
        if($device_id){
            $post['deviceToken'] = $device_id;
        }
        if ($where) {
            $post['where'] = json_decode($where,true);// 检索 _Installation 表使用的查询条件，JSON 对象
        }
        $res = leancloud_client::post("/push",$post);
        $res_objectId = $res['objectId'];
        if ($res_objectId) {
            $editData = array(
                'status'   => 1,
                'objectId' => $res_objectId
            );
            parent::edit($id, $editData);
            return true;
        } else {
            return false;
        }
    }

    /**
     *   获取云推送配置 
     */
    function getAppLeancloud()
    {
        $model_config = &m('config');
        $leancloud_config = $model_config->find(array('conditions' => 'code = "leancloud" AND status = 1'));
        if (!$leancloud_config) {
            return false;
        }else{
            return $leancloud_config;
        }
    }

    /**
     *   物流消息推送
     *   @author   Pangxp
     *   @param    int      $order_id  
     *   @param    bool     $is_edit  
     *   @return   bool
     */

    function delivery_msg_push($order_id, $is_edit = false){
        if (!$order_id) 
            return false;

        $leancloud_config = $this->getAppLeancloud();
        if(!$leancloud_config) 
            return false;
        $leancloud_config = current($leancloud_config);
        $model_order = &m('order');
        $order_info = $model_order->get_info($order_id);
        $model_orderextm = &m('orderextm');
        $orderextm_info = $model_orderextm->get_info($order_id);
        if ($order_info && $orderextm_info) {
            $data['user_id'] = $order_info['buyer_id'];
            $data['created_at'] = local_date('Y-m-d H:i:s');
            $data['updated_at'] = local_date('Y-m-d H:i:s');
            $data['push_at'] = local_date('Y-m-d H:i:s');
            if ($is_edit) {
                $data['title'] = '物流单号修改';
                $data['content'] = '您的订单号：'.$order_info['order_sn'].',此订单物流单号已经修改为'.$order_info['invoice_no'];
            }else{
                $data['title'] = '您的订单已发货';
                $data['content'] = '您的订单号：'.$order_info['order_sn'].',已经由'.$orderextm_info['shipping_name'].'发出,物流单号'.$order_info['invoice_no'];
            }
            $data['link'] = 'deeplink://goto/shipping/'.$order_info['order_id'];
            $data['platform'] = 3;
            $data['push_type'] = 0;
            $data['message_type'] = 2;
            $id = parent::add($data);
            if ($id) {
                $push_res = $this->push($id);
            }
            return $push_res;
        }
        return false;
    }

}

?>
