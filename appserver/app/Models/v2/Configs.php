<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

use App\Helper\Token;
use App\Helper\XXTEA;
use App\Services\Cloud\Client;
use App\Services\Other\JSSDK;

class Configs extends BaseModel
{
    protected $connection = 'shop';
    protected $table = 'config';
    public  $timestamps   = true;


    public static function getList()
    {
        $data = self::where('status', 1)->get();
        $config = ['config' => self::formatConfig($data), 'feature' => Features::getList(), 'platform' => self::getApplicationPlatform()];
        return self::formatBody(['data' => base64_encode(XXTEA::encrypt($config, 'getprogname()'))]);
    }

    public static function checkConfig($code)
    {
        switch ($code) {
            case 'sms':
                if (config('app.sms_channel') == 'Cloud') {
                    return self::initLeanCloud();
                }
                break;
        }

        return true;
    }

    public static function verifyConfig(array $params, $config)
    {
        if (!isset($config->config)) {
            return false;
        }

        $data = json_decode($config->config, true);

        foreach ($params as $key => $value) {
            if (!isset($data[$value])) {
                return false;
            }
        }

        return $data;
    }

    private static function initLeanCloud()
    {
        if (!$cloud = Configs::where('code', 'leancloud')->first()) {
            return self::formatError(3001, trans('message.cloud.config'));
        }

        if (!$cloud->status) {
            return self::formatError(3002, trans('message.cloud.notopen'));
        }

        $cloud_config = json_decode($cloud->config);
        if ($cloud_config && isset($cloud_config->app_id) && isset($cloud_config->app_key)) {
            Client::initialize($cloud_config->app_id, $cloud_config->app_key);
            return true;
        } else {
            return self::formatError(3001, trans('message.cloud.config'));
        }
    }

    private static function getApplicationPlatform()
    {
        return [
            'type'      => self::B2B2C,
            'vendor'    => self::ECMALL,
            'version'   => '2.3.66'
        ];
    }

     private static function formatConfig($data)
    {
        $body = null;
        foreach ($data as $value) {
            $arr = json_decode($value->config, true);

            //wxpay.web jssdk
            if( $value->code == 'wxpay.web' && $value->status){
                if (!empty($value->config)) {
                    $jssdk = new JSSDK($arr['app_id'], $arr['app_secret']);
                    $arr = $jssdk->GetSignPackage();
                }
            }

            if(is_array($arr)){
                $body[$value->code] = $arr;
            }
        } 

        // $body['authorize'] = false;

        // $response = Authorize::info();    
        // if ($response['result'] == 'success') 
        // {
        //     // 旗舰版授权...
        //     if ($response['info']['authorize_code'] == 'NDE') 
        //     {
        //         $body['authorize'] = true;
        //     }
        // }       

        //安全处理
        unset($body['alipay.app']);
        unset($body['wxpay.app']);
        unset($body['unionpay.app']);
        unset($body['leancloud']['master_key']);
        unset($body['ecmall_service_cert_auth']);
        unset($body['wap.recommend']);

        return $body;
    }

}
