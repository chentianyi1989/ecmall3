<?php 
namespace App\Services\Shopex;

use Log;
use App\Models\v2\ShopConfig;

class Logistics
{
    public static function info($order_id)
    {
        $token = '123f4dc3ffdd769dd95d2ed1a3290911';

        //获取物流信息参数
        $param = array(
            'order_id' => $order_id
        );

        $ac = self::get_ac($param,$token);//验证签名

        $param['certi_ac'] = $ac;//签名值放入参数中

        $time = time();

        $api = config('app.shop_url') . "/api/ecmobile.php?action=logistics_trace&time={$time}";

        $response = curl_request($api, 'POST', $param);

        if ($response['result'] == 'success' && isset($response['info'])) {
            
            $format = [];

            if (!empty($response['info'])) {
                foreach ($response['info'] as $key => $value) {
                    $format[] = [
                        'datetime' => $value['AcceptTime'],
                        'content' => $value['AcceptStation']
                    ];
                }
            }

            return array_reverse($format);
        }

        return false;

    }

    //验证方法
    public static function get_ac($params, $token){
        ksort($params);
        $str = '';
        foreach($params as $key=>$value){
            if($key!='certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
    }

}