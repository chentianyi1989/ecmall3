<?php

/**
 *  移动端对接api
 *
 *  @author Pangxp
 *
 */

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');
define('API_RETURN_TYPE', empty($_POST['return_data']) ? 2 : ($_POST['return_data'] == 'json' ? 1 : 2));
define('API_AC_TOKEN', '123f4dc3ffdd769dd95d2ed1a3290911'); // ac验签token

class EcmobileApp extends ApiApp
{
    var $sms_mod;

    function __construct()
    {
        parent::__construct();
        $this->appdir   = ROOT_PATH . '/';
        $this->sms_mod  = &m('sms');
        $this->yunqioauth_mod = &m('yunqioauth');
    }

    function index()
    {
        if ( @constant( "DEBUG_API" ) ) 
            $this->debug_log();
        $_DCACHE = $get = $post = array();
        $get = $_GET;
        $post = $_POST;
        // 验授权证书 无授权证书不能使用api
        $open_api = $this->use_api();
        if (!$open_api) {
            $this->api_response('fail', 'not allow use api!');
        }
        // 验ac签名
        $certi_ac = $this->make_shopex_ac($_POST, API_AC_TOKEN);
        if ($certi_ac != $post['certi_ac']) {
            $this->api_response('fail', 'veriy fail', '', API_RETURN_TYPE);
        }
        $get = _stripslashes($get);

        $timestamp = time();
      
        if($timestamp - $get['time'] > 3600) {
            exit('Authracation has expiried');
        }
        if(empty($get)) {
            exit('Invalid Request');
        }
        $action = $get['action'];

        if(in_array($get['action'], array('test', 'send_sms', 'logistics_trace'))) {
            exit($this->$get['action']($get, $post));
        } else {
            exit(API_RETURN_FAILED);
        }
    }

    /* 测试能否连接 */
    function test($get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    function send_sms($get, $post)
    {
        $phone = $post['phone'];
        $message = $post['message'];
        $sendType = $post['sendType'] ? $post['sendType'] : 'notice';
        $status = $this->sms_mod->send($phone, $message, $sendType, $msg);
        $status = ( $status == true ) ? 'true' : 'fail'; 
        $this->api_response($status, $msg, '', API_RETURN_TYPE);
    }

    function logistics_trace($get, $post)
    {
        $order_id = $post['order_id'];
        if (!$order_id) {
            $this->api_response('fail', '', 'Lack of necessary parameters！');
        }
        $method = $post['method'] ? $post['method'] : '';
        $res = $this->yunqioauth_mod->get_logistics_trace($order_id, $method);
        if ($res['status'] == 'succ') {
            $this->api_response('true', '', $res['data']);
        } else {
            $this->api_response('fail', '');
        }
    }

    function debug_log()
    {
        foreach ($_POST as $key=>$val) {
            $array_debug_info[] = $key."=".stripslashes($val);
        }
        $str_debug_info = implode("&", $array_debug_info);
        $filename = ROOT_PATH."/temp/logs/ecmobile_debug_".date("Y-m-d").".log";
        if (!is_dir('temp/logs'))
        {
            ecm_mkdir(ROOT_PATH . '/' . 'temp/logs');
        }
        error_log(date("c")."\t".rawurldecode($str_debug_info)."\n".stripslashes(var_export($_POST,true))."\n\n", 3, $filename);
        unset($str_debug_info,$array_debug_info);
    }

    function make_shopex_ac($temp_arr,$token)
    {
        ksort($temp_arr);
        $str = '';
        foreach($temp_arr as $key=>$value){
            if($key!='certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
    }

    /**
     *   判断是否可以使用api
     * 
     *   @return boolean
     */
    function use_api()
    {
        $license_model = &ls("license");
        $license = $license_model->license_check();
        if(isset($license['request']) && $license['request']['res']=='succ' && !empty($license['request']['info']['certificate_id']) && $license['request']['info']['service']['ecmall']['cert_auth']['auth_type']=='U') {
            return true;
        }else{
            return false;
        }
    }

    /**
     *   api 返回数据
     *
     *   @param     string    $resCode
     *   @param     mixed     $errorCode
     *   @param     mixed     $data
     *   @param     int       $type   返回数据类型  1 xml   2|other json
     *   @return    xml | json     default json 
     * 
     */
    function api_response($resCode, $errorCode = false, $data = null, $type = 2)
    {
        $resposilbe = array(
            'true' => 'success',
            'fail' => 'fail',
            'wait' => 'wait'
        );

        $result['result'] = $resposilbe[$resCode];
        $result['msg'] = $errorCode ? $errorCode : '';
        $result['shopex_time'] = time();
        $result['info'] = $data;

        if ($type == 1) {
            //XML
            $this->_header('text/xml');
            $result = $this->array2xml($result, 'shopex');
        } else {
            //JSON
            $this->_header('text/html');
            $result = json_encode($result);
        }
        echo $result;
        exit;
    }

    /**
     * 头文件
     */
    function _header($content = 'text/html', $charset = 'utf-8')
    {
        header('Content-type: ' . $content . ';charset=' . $charset);
        header("Cache-Control: no-cache,no-store , must-revalidate");
        $expires = gmdate("D, d M Y H:i:s", time() + 20);
        header("Expires: " . $expires . " GMT");
    }

    function array2xml($data,$root='shopex'){
        $xml='<'.$root.'>';
        $this->_array2xml($data,$xml);
        $xml.='</'.$root.'>';
        return $xml;
    }

    function _array2xml(&$data,&$xml){
        if(is_array($data)){
            foreach($data as $k=>$v){
                if(is_numeric($k)){
                    $xml.='<item key="' . $k . '">';
                    $xml.=$this->_array2xml($v,$xml);
                    $xml.='</item>';
                }else{
                    $xml.='<'.$k.'>';
                    $xml.=$this->_array2xml($v,$xml);
                    $xml.='</'.$k.'>';
                }
            }
        }elseif(is_numeric($data)){
            $xml.=$data;
        }elseif(is_string($data)){
            $xml.='<![CDATA['.$data.']]>';
        }
    }
    
}


function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;

    $key = md5($key ? $key : UC_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
                return '';
            }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }

}

function _stripslashes($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = _stripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

?>
