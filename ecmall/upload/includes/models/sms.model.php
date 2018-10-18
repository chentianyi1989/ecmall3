<?php

/**
 *    短信发送模型
 *
 *    @author    Pangxp
 *    @usage    none
 */
class SmsModel extends BaseModel
{

    function __construct()
    {
        import("oauth2.lib");
        $openapi_key = array(
            'key'=>OPENAPI_KEY,
            'secret'=>OPENAPI_SECRET,
            'site'=>OPENAPI_SITE,
            'oauth'=>OPENAPI_OAUTH
        );
        $this->smsOauth = new oauth2($openapi_key);
        $this->memModel = &m("member");
    }

    /**
     *    发送短信预处理
     *
     *    @author    Pangxp
     *    @param     string   $phone  接收短信手机号
     *    @param     string   $message  发送内容
     *    @param     string   $sendType  短信类型 fan-out 营销  notice 通知
     *    @param     string   $msg  
     *    @return    boolean
     */
    function send($phone, $message, $sendType = '', &$msg)
    {
        if (!$phone) {
            $msg = Lang::get('sms_phone_empty');
            return false;
        }
        if (!$message) {
            $msg = Lang::get('sms_content_empty');
            return false;
        }
        $yunqi_member = $this->memModel->get("passport_type = 'yunqi'");
        if (!$yunqi_member) {
            $msg = Lang::get('to_active_yunqi_account');
            return false;
        }
        // 获取短信签名
        $sms_sign = $this->get_sms_sign(); 
        //判断是否含有全角
        if ($sms_sign) {
            $sms_sign=$this->checkReg($sms_sign);
            if (!$sms_sign) {
                $msg = Lang::get('contain_illegal_characters');
                return false;
            }
        }
        if (!$sms_sign) {
            $msg = Lang::get('sms_sign_cannot_be_empty');
            return false;
        }
        // 营销短信 自动加上 退订回N
        $unsubscribe_lang = Lang::get('unsubscribe');
        if ($sendType == 'fan-out' && !stripos($message, $unsubscribe_lang)) {
            $message .= $unsubscribe_lang;
        }
        $message = $message.'【'. $sms_sign .'】';
        $contents = array(
            0 => array(
                'phones' => $phone,
                'content' => $message
            )
        );
        return  $this->send_sms($contents, $sendType, $msg);

        
    }

    /**
     *    发送短信
     *
     *    @author    Pangxp
     *    @param     array   $contents  手机号和短信内容组织的数组
     *    @param     string   $sendType
     *    @param     mixed   $msg
     *    @return    boolean
     */
    function send_sms($contents, $sendType = '', &$msg)
    {
        $yunqi_member = $this->memModel->get("passport_type = 'yunqi'");
        if (!$yunqi_member) {
            $msg = Lang::get('to_active_yunqi_account');
            return false;
        }
        // $params['shopexid'] = '88170101445805';  // 测试使用
        $params['shopexid'] = $yunqi_member['passport_uid'];
        // $params['token'] = 'b81db8a638cfd485291ba60260f0d353'; // 测试使用
        $params['token'] = $yunqi_member['passport_yunqi_code'];
        // $params['source'] = '423524'; //  测试使用
        $params['source'] = SOURCE_ID; //  注意这个参数设置修改
        $params['certi_app'] = 'sms.newsend';
        $params['sendType'] = ($sendType == 'fan-out') ? 'fan-out' : 'notice';
        $params['contents'] = json_encode($contents);
        $params['certi_ac'] = $this->make_shopex_ac($params, SOURCE_TOKEN); 
        $type = 'api/smsv2/send';
        if ( @constant('DEBUG_API') ) 
            error_log(date("c")."\t".print_r(array('params' => $params),1)."\t\n",3,ROOT_PATH."/temp/logs/sms_".date("Y-m-d").".log");
        $r = $this->smsOauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $res = $this->smsOauth->request()->post($type, $params, $time);
        $response = $res->parsed();
        if ( @constant('DEBUG_API') ) 
            error_log(date("c")."\t".print_r(array('response' => $response),1)."\t\n",3,ROOT_PATH."/temp/logs/sms_".date("Y-m-d").".log");
        if($response['res']=='succ') {
            $msg = Lang::get('sms_send_succ');
            return true;
        }elseif($response['res']=='fail'){
            $msg = $response['info'];
            return false;
        }
        $msg = Lang::get('sms_send_fail');
        return false;
    }

    /**
     *    短信签名
     *
     *    @author    Pangxp
     *    @return    string
     */
    function get_sms_sign()
    {
        // return '系统通知'; // 测试使用
        $model_setting = &af('settings');
        $setting = $model_setting->getAll(); //载入系统设置数据
        return $setting['sms_sign'];
    }

    /**
     *    判断是否含有全角
     *
     *    @author    Pangxp
     *    @param     string
     *    @return    boolean
     */
    function checkReg($params){
        $arr = array(
            '【', '】', 
            );
       
        if ((strstr($params, $arr[0]) && (strstr($params, $arr[1]))) != false)
        {
            return 'false';
        }
        
        return $params;
    }

    /**
     *    ac签名
     *
     *    @author    Pangxp
     *    @param     array    $temp_arr  
     *    @param     string   $token  
     *    @return    string
     */
    public function make_shopex_ac($temp_arr,$token){
        ksort($temp_arr);
        $str = '';
        foreach($temp_arr as $key=>$value){
            if($key!='certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
    }

}

?>