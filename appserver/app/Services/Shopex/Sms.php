<?php 
namespace App\Services\Shopex;

use Log;
use Cache;
use App\Models\v2\ShopConfig;

class Sms
{
    public static function requestSmsCode($mobile)
    {
        $token = '123f4dc3ffdd769dd95d2ed1a3290911';

        $code = self::generate_verify_code(6);

        $template = env('SMS_TEMPLATE', '您本次的验证码为：#CODE#,请不要把验证码泄露给其他人，如非本人操作可不用理会');

        //发送短信参数
        $param = array(
           'phone' => $mobile,//电话号码
           'message' => str_replace('#CODE#', $code, $template), //短信内容
           'sendType' => 'notice',
        );

        $ac = self::get_ac($param, $token);//验证签名

        $param['certi_ac'] = $ac;//签名值放入参数中

        $time = time();

        $api = config('app.shop_url') . "/api/ecmobile.php?action=send_sms&time={$time}";

        $response = curl_request($api, 'POST', $param);

        if ($response['result'] == 'success') {
            Cache::put('smscode:'.$mobile, $code, 30);
            return true;
        }

        Log::error('验证码发送失败', ['mobile' => $mobile, 'code' => $code]);

        return false;
    }

    public static function verifySmsCode($mobile, $code)
    {
        if (Cache::get('smscode:'.$mobile) == $code) {
            Cache::forget('smscode:'.$mobile);
            return true;
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

    public static function generate_verify_code($num = 4) {
        if(!$num) {
            return false;
        }
        
        $num = intval($num);

        $pool = '0123456789';
        $shuffled = str_shuffle($pool);

        $code = substr($shuffled, 0, $num);

        return $code;
    }

}