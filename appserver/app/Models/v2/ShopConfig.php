<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Header;

class ShopConfig extends BaseModel {


    public static function getSiteInfo()
    {
        $logo = config('app.shop_url').'/data/files/mall/settings/site_logo.gif';
        if(self::url_exists($logo) === false)
        {
            $logo = config('app.shop_url').'/data/system/logo.gif';
        }

        return self::formatBody(['site_info' => [
            'name' => 'Ecmall',
            'desc' => '这是一个使用 ecmall 搭建的网上商城',
            'logo' => formatPhoto($logo),
            'opening' => true,
            'telephone' => '',
            'share_url' => env('SHARE_URL'),
            'terms_url' => env('TERMS_URL'),
            'about_url' => env('ABOUT_URL'),
        ]]);
    }

    /**
     * 判断资源是否存在
     * @url http://www.ecmall.com
     */
    public static function url_exists($url) {
        $ch = curl_init(); 
        curl_setopt ($ch, CURLOPT_URL, $url); 
        //不下载
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        //设置超时
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
        if($http_code == 200) {
            return true;
        }
        return false;
    }

}
