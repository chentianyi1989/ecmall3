<?php

class YunqioauthModel extends BaseModel
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
        $this->oauth = new oauth2($openapi_key);
        $this->model = &m("member");
    }

    function oauth_set_callback($code,&$res)
    {
        $res = $this->get_token($code);
        if($res['token'] and $res['params']){
            if (isset($res['params']['data']) && $res['params']['data']) {
                foreach ($res['params']['data'] as $d_key => $d_value) {
                    $res['params'][$d_key] = $d_value;
                }
                unset($res['params']['data']);
            }
            $result = $this->set_yunqi_passport($res['params']['passport_uid']);
            $this->check_certi($res);
            return true;
        }
    }

    /**
     * 云起认证后绑定超管账号
     *
     * @access  public
     * @param   string      $passport_uid passport_uid账号
     * @return  boolean     成功返回true，失败返回false
     */
    function set_yunqi_passport($passport_uid)
    {
        $yunqi_member = $this->get_yunqi_member();
        if($yunqi_member) return;
        $data = array(
            'user_name' => $passport_uid, 
            'password' => 'shopex_ecmall', 
            'reg_time' => gmtime(),
            'passport_type' => 'yunqi',
            'passport_uid' => $passport_uid,
        );
        if ($id = $this->model->add($data)) {
            $user_priv_model = &m('userpriv');
            $privData = array(
                'user_id' => $id,
                'store_id' => 0,
                'privs' => 'all'
            );
            return $user_priv_model->add($privData);
        }
    }

    /**
    * 功能：oauth获取token
    *
    * @param   string     $code
    * @return  array
    */
    function get_token($code)
    {
        return $this->oauth->get_token($code);
    }

    /**
     *  检测passport_uid和短信token是否存在 
     *  
     *  @param  array  $res 
     *  @return  boolean 
     */
    function check_certi($res)
    {
        if (!$res) 
            return false;
        $yunqi_member = $this->model->get("passport_type = 'yunqi'");
        if(!$yunqi_member['passport_uid']){
            $data['passport_uid'] = $res['params']['passport_uid'];
            $this->model->edit($yunqi_member['user_id'], array('passport_uid' => $data['passport_uid']));
        }
        if(!$yunqi_member['passport_yunqi_code']){
            $code_result = $this->get_yunqi_code($res['token']);
            $code_result['status']=='success' and $this->model->edit($yunqi_member['user_id'], array('passport_yunqi_code' => $code_result['data']['token']));
        }
        //激活云起物流
        if($yunqi_member['yunqiexp_active'] != 1){
            $yunqiexp_result = $this->yqexp_exp_active();
            $yunqiexp_result['status']=='success' and $this->model->edit($yunqi_member['user_id'], array('yunqiexp_active' => 1));
        }
        return true;

    }

    /**
     *    获取云起激活的member数据
     *
     *    @return  array
     *
     */
    function get_yunqi_member()
    {
        static $gym = null;
        if ($gym === null) {
            $gym = $this->model->get("passport_type = 'yunqi'");
        }
        return $gym;
    }

    /**
     * 功能：oauth的登录地址
     *
     * @param   string     $callback
     * @return  string
     */
     function get_authorize_url($callback){
        return $this->oauth->authorize_url($callback)."&view=auth_ecshop";
     }

     function yunqi_logout(){
        unset($_SESSION['yunqi_login']);
        $url = $this->logout_url();
        header("location: $url");
     }

     function logout_url($callback = ''){
        !$callback and $callback = site_url()."/index.php?act=logout&type=yunqi";
        return $this->oauth->logout_url($callback);
    }

    function get_certificate()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        if(isset($_GET['code']) && $_GET['code']){
            $code = $_GET['code'];
            $rs = $this->oauth_set_callback($code, $res);
            $_SESSION['TOKEN'] = $res['token'];
            if($_GET['type']=='yunqi'){
                $url = site_url()."/index.php";
                echo '<script type="text/javascript">parent.location.href="'.$url.'";</script>';exit;
            }
        }
    }

    /**
    * 功能：oauth根据token获取物流和短信的永久token
    *
    * @param   string     $token
    * @return  array
    */
    function get_yunqi_code($token){
        $r = $this->oauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $type = OAUTH_API_PATH.'/auth/auth.gettoken';
        $params['product_code'] = PRODUCT_CODE;
        $rall = $this->oauth->request($token)->post($type,$params,$time);
        $response = $rall->parsed();
        return $response;
    }

    /**
    *   设置短信签名
    *
    *   @param   string     $new_content
    *   @param   mixed      $msg
    *   @return  boolean
    */
    function smsContentSave($new_content, &$msg){
        $error_code = array(
            2002 => Lang::get('sms_sign_error02'),
            2009 => Lang::get('sms_sign_error09'),
            2010 => Lang::get('sms_sign_error10'),
            2011 => Lang::get('sms_sign_error11'),
        );
        $new_content = str_replace(array('【','】'), '', $new_content);
        $content_length = mb_strlen($new_content,'utf-8');
        if($content_length > 8 or $content_length < 2){
            $msg = Lang::get('sms_sign_length_error');
            return false;
        }
        $model_setting = &af('settings');
        $setting = $model_setting->getAll(); //载入系统设置数据
        $old_content = $setting['sms_sign'];
        $new_content = '【'.$new_content.'】';
        $old_content and $old_content = '【'.$old_content.'】';
        $yunqi_member = $this->get_yunqi_member();
        $params['token'] = $yunqi_member['passport_yunqi_code'];
        $params['shopexid'] = $yunqi_member['passport_uid'];

        if($old_content){
            $type = 'api/addcontent/updatebytoken';
            $params['old_content'] = $old_content;
            $params['new_content'] = $new_content;
        }else{
            $type = 'api/addcontent/newbytoken';
            $params['content'] = $new_content;
        }

        $r = $this->oauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $this->oauth->request()->timeout = 1;
        error_log(date("c")."\t".print_r($params,1)."\t\n",3,ROOT_PATH."/temp/logs/smsContentSave_".date("Y-m-d").".log");
        $rall = $this->oauth->request()->post($type, $params,$time);
        $results = $rall->parsed();
        error_log(date("c")."\t".'smsContentSave : '.json_encode($results).' type :'.$type."\t\n",3,ROOT_PATH."/temp/logs/smsContentSave_".date("Y-m-d").".log");
        if($results['res']=='succ'){
            return true;
        }else{
            $msg = $error_code[$results['code']] ? $error_code[$results['code']] : Lang::get('unknown_error');
            return false;
        }
    }

    /**
    *   云起物流 激活
    *   @return array
    */
    function yqexp_exp_active(){
        $yunqi_member = $this->get_yunqi_member();
        if (!$yunqi_member) return;
        $r = $this->oauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $type = OAUTH_API_PATH.'/yqexp/exp/active';
        $params['shopexid'] = $yunqi_member['passport_uid'];
        $params['token'] = $yunqi_member['passport_yunqi_code'];
        $params['product_code'] = PRODUCT_CODE;
        $params['appid'] = 'kdniao';
        $params['method'] = 'express.expactive';
        $params['siteurl'] = dirname(site_url());
        $rall = $this->oauth->request()->post($type, $params, $time);
        $response = $rall->parsed();
        return $response;
    }

    /**
    *    云起获取物流信息
    *    @param    array    $data
    *    @param    int      $limit
    *    @return   array
    */
    function logistics_trace_detail_get($data, $limit = 0){
        $yunqi_member = $this->get_yunqi_member();
        if (!$yunqi_member) return array();
        $r = $this->oauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $type = OAUTH_API_PATH.'/yqexp/exp/get';
        $params['shopexid'] = $yunqi_member['passport_uid'];
        $params['token'] = $yunqi_member['passport_yunqi_code'];
        $params['product_code'] = PRODUCT_CODE;
        $params['appid'] = 'kdniao';
        $params['method'] = 'express.explogistics';
        $params['expno'] = $data['logistic_code'];
        $params['expcode'] = $data['company_code'];
        $rall = $this->oauth->request()->post($type, $params, $time);
        $response = $rall->parsed();
        if($response['status'] == 'success' and $response['data']['Traces']){
            $tData = $response['data']['Traces'];
            $cur_day = '';
            $cweekday = array(
                Lang::get('day0'),
                Lang::get('day1'),
                Lang::get('day2'),
                Lang::get('day3'),
                Lang::get('day4'),
                Lang::get('day5'),
                Lang::get('day6'),
            );
            foreach ($tData as &$v) {
                $time = explode(' ', $v['AcceptTime']);
                $v['day'] = $time[0];
                $v['time'] = $time[1];
                $v['weekday'] = $cweekday[date("w",strtotime($v['AcceptTime']))];
                $v['display'] = 'hidden';
                if( $cur_day != $v['day'] ){
                    $v['display'] = '';
                    $cur_day = $v['day'];
                }
            }
            return $limit ? array_slice($tData,count($tData)-3, count($tData)) : $tData;
        }
        return array();
    }

    /**
    *    获取物流信息
    *    @param    int       $order_id
    *    @param    string    $method    默认 detail  完整明细    brief 最近三条
    *    @return   array
    */
    function get_logistics_trace($order_id, $method = 'detail')
    {
        $yunqi_member = $this->get_yunqi_member();
        if (!$yunqi_member || ($yunqi_member['yunqiexp_active'] != 1)) return $message['status'] = 'fail';
        $message = array();
        $limit = $method == 'brief' ? 2 : 0;
        if (!$order_id) {
            $message['status'] = 'fail';
            $message['data'] = Lang::get('query_fail');
        } else {
            $params['order_id'] = $order_id;
            $sql = "SELECT o.order_id, o.invoice_no, oe.shipping_id, s.corp_id, s.shipping_name, dc.type, dc.name
FROM  `".DB_PREFIX."order` o
LEFT JOIN  `".DB_PREFIX."order_extm` oe ON o.order_id = oe.order_id
LEFT JOIN  `".DB_PREFIX."shipping` s ON oe.shipping_id = s.shipping_id
LEFT JOIN  `".DB_PREFIX."dly_corp` dc ON s.corp_id = dc.corp_id
WHERE o.order_id ='{$order_id}'";
            $data = $this->model->getRow($sql);
            if (!$data) {
                $message['status'] = 'fail';
                $message['data'] = Lang::get('query_fail');
            }
            $params['company_code'] = $data['type'];
            $params['company_name'] = $data['name'];
            $params['logistic_code'] = $data['invoice_no'];
            $res = $this->logistics_trace_detail_get($params, $limit);
            if($res){
                $message['status'] = 'succ';
                $message['data'] = $res;
            }else{
                $message['status'] = 'fail';
            }
        }
        return $message;
    }

    /**
    *    获取后台云起背景图 link跳转链接
    *    @param    string       $ident
    *    @return   array
    */
    function get_yunqi_ad($ident){
        if( !$ident ) return false;
        $iopenapi_key = array(
            'key'=>OPENAPI_IKEY,
            'secret'=>OPENAPI_ISECRET,
            'site'=>OPENAPI_ISITE,
            'oauth'=>OPENAPI_OAUTH
        );
        $ioauth = new oauth2($iopenapi_key);
        $params['ad_ident'] = $ident;
        $type = 'api/yunqiaccount/csm/adgetapi';
        $r = $ioauth->request()->get('api/platform/timestamp');
        $time = $r->parsed();
        $ioauth->request()->timeout = 2;
        $rall = $ioauth->request()->post($type, $params, $time);
        $results = $rall->parsed();
        if($results['status'] != 'succ'){
            return false;
        }
        return $results['data'];
    }

}

