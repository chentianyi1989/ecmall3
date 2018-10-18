<?php
//
namespace App\Services\Delivery;

use App\Helper\Token;

class Kuaidi100
{
    /**
     * $typeCom 快递公司
     * $typeNu  快递单号
     */
    public static function getDelivey($typeCom ,$typeNu)
    {
        $url = 'http://www.kuaidi100.com/query?type=' . $typeCom . '&postid=' . $typeNu;

        //使用curl模式发送数据
        $data = curl_request($url);

        if(is_array($data) && isset($data['data'])){

            //按照协议格式化
            $format_data = [];
            foreach ($data['data'] as $key => $point) {
                $format_data[$key]['datetime'] = strtotime($point['time']);
                $format_data[$key]['content'] = $point['context'];
            }

            return $format_data;
        }

        return null;
    }


    public static function getCodeByName($shipping_name)
    {
        $typeCom = false;

        if(empty($shipping_name))
        {
            return false;
        }

        switch ($shipping_name) {
            case '中国邮政':
            case 'EMS 国内邮政特快专递':
            case 'EMS':
            case '中国邮政EMS':
                $typeCom = 'ems';
                break;
            case '申通快递':
            case '申通':
                $typeCom = 'shentong';
                break;
            case '圆通速递':
            case '圆通':
                $typeCom = 'yuantong';
                break;
            case '顺丰速运':
            case '顺丰':
                $typeCom = 'shunfeng';
                break;
            case '韵达快递':
            case '韵达':
                $typeCom = 'yunda';
                break;
            case '德邦物流':
                $typeCom = 'debangwuliu';
                break;
            case '全峰快递':
                $typeCom = 'quanfengkuaidi';
                break;
            case '天天快递':
                $typeCom = 'tiantian';
                break;
            case '中通速递':
            case '中通':
                $typeCom = 'zhongtong';
                break;
            case '增益速递':
                $typeCom = 'zengyisudi';
                break;
            case '汇通快运':
            case '百世汇通':
                $typeCom = 'huitongkuaidi';
                break;
            case '宅急送':
                $typeCom = 'zhaijisong';
                break;
            case '增益速递':
                $typeCom = 'zengyisudi';
                break;
            case '邮局平邮':
            case '平邮':
            case '邮政快递包裹':
                $typeCom = 'youzhengguonei';
                break;

            default:
                break;
        }

        return $typeCom;
    }
}
