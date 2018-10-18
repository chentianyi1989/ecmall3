<?php

/**
 *    天工收银支付方式插件
 *
 *    @author    Pangxp
 *    @usage    none
 */

class YunqiPayment extends BasePayment
{
	/* 天工网关地址 */
	var $_gateway 	= 	'https://api.teegon.com/charge/pay';
    var $_code      =   'yunqi';

    /**
     *    获取支付表单
     *
     *    @author    Pangxp
     *    @param     array $order  待支付的订单信息
     *    @return    array
     */
    function get_payform($order)
    {
        $appkey = $this->_config['yunqi_appkey'];
        $appsecret = $this->_config['yunqi_appsecret'];

        $params = array(

            /* 基本信息 */
            'order_no'          => $order['order_sn'],
            'channel'           => $order['yunqi_paymethod'] == 'wxpay' ? $order['yunqi_paymethod'] : 'alipay',
            'amount'            => $order['order_amount'],
            'notify_url'        => $this->_create_notify_url($order['order_id']),
            'return_url'        => $this->_create_return_url($order['order_id']),
            'metadata'          => md5($order['order_id'].$order['order_amount'].$appsecret).'and'.$order['order_sn'],
            'client_ip'         => $_SERVER["REMOTE_ADDR"],
            'client_id'         => $appkey,
            'charset'           => CHARSET,

            /* 业务参数 */
            'subject'           => $this->_get_subject($order),
        );
        /* 签名 */
        $params['sign']         =   $this->sign($params);

        return $this->_create_payform('GET', $params);
    }

    /**
     *    返回通知结果
     *
     *    @author    Pangxp
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_notify($order_info, $strict = false)
    {
        if (empty($order_info))
        {
            $this->_error('order_info_empty');

            return false;
        }

        /* 初始化所需数据 */
        $notify =   $this->_get_notify();

        /* 验证通知是否可信 */
        $sign_result = $this->verify_sign($notify, $order_info['order_id']);
        if (!$sign_result)
        {
            /* 若本地签名与网关签名不一致，说明签名不可信 */
            $this->_error('sign_inconsistent');

            return false;
        }

        /*----------本地验证开始----------*/
        /* 检查支付的金额是否相符 */
        if ($order_info['order_amount'] != $notify['amount'])
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }
        //至此，说明通知是可信的，订单也是对应的，可信的

        /* 按通知结果返回相应的结果 */

        if ( $notify['is_success'] == true ) {
            $order_status = ORDER_ACCEPTED;
        }else{
            $this->_error('undefined_status');
            return false;
        }

        return array(
            'target'    =>  $order_status,
        );
    }

    //yunqi 加密算法
    public function sign($para_temp){
        $appsecret = $this->_config['yunqi_appsecret'];
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->para_filter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->arg_sort($para_filter);
        //生成加密字符串
        $prestr = $this->create_string($para_sort);
        $prestr = $appsecret . $prestr . $appsecret;
        return strtoupper(md5($prestr));
    }


    private function para_filter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    private function arg_sort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    private function create_string($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.$val;
        }

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     *    验证签名是否可信
     *
     *    @author    Pangxp
     *    @param     array $notify
     *    @return    bool
     */
    function verify_sign($notify, $order_id)
    {
        /* 天工返回的原始参数不完全  无法进行完整的sign验签 所以在此以另一种方式简易验签 */
        // $appkey = $this->_config['yunqi_appkey'];
        $appsecret = $this->_config['yunqi_appsecret'];
        $metadata = explode('and', $notify['metadata']);
        $safe = md5($order_id.$notify['amount'].$appsecret);

        return ($safe == $metadata[0]);
    }
}

?>