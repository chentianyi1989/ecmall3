<?php

/**
 *    短信平台
 * 
 *    @author   Pangxp
 *    @usage    none
 */
class SmsresourceApp extends BackendApp
{


    /**
     *    短信平台
     *
     *    @author    Pangxp
     *    @return    void
     */
    function index()
    {
        $data = array();
        $data[] = base64_encode(SOURCE_ID);
        $mem_mod = &m("member");
        $yunqi_member = $mem_mod->get("passport_type = 'yunqi'");
        $data[] = $yunqi_member['passport_uid'];
        $data[] = $yunqi_member['passport_yunqi_code'];
        $data[] = time();
        $data[] = $this->getRandChar(6);
        $data[] = $this->getRandChar(6);
        $source_str = implode('|', $data);
        $this->assign('resource_url', SMS_RESOURCE_URL . '/index.php?source='.base64_encode($source_str));
        $this->display('smsresource.index.html');
       
    }

    /**
     *    获取随机字符串
     *    @param    int      $length
     *    @return   string   $str
     *
     */
    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }
        return $str;
    }

}