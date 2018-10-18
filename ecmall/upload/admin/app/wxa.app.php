<?php

/**
 *    基本设置控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class WxaApp extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
    }

    /**
     *    小程序应用配置
     *
     *    @author    Pangxp
     *    @return    void
     */
    function oauthWxa()
    {
        $model_config = &m('config');
        $wechat = $model_config->get("code='wechat.wxa'");//载入设置数据
        $setting = json_decode($wechat['config'],1);

        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('oauthwxa.wxa.html');
        }
        else
        {
            $data['status']            = ($_POST['status'] == '1');
            $config['status']          = ($_POST['status'] == '1');
            $config['app_id']          = $_POST['app_id'];
            $config['app_secret']      = $_POST['app_secret'];
            if (!$wechat) {
                $data['name']              = Lang::get('oauthwxa_name');
                $data['type']              = 'oauth';
                $data['description']       = Lang::get('oauthwxa_description');
                $data['code']              = 'wechat.wxa';
                $data['config'] = $config;
                $data['created_at'] = local_date('Y-m-d H:i:s');
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->add($data);
            } else {
                $data['config'] = $config;
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->edit("code='wechat.wxa'", $data);
            }
            $this->show_message('edit_wxa_successed');
        }
    }

     function paymentWxa()
    {
        $model_config = &m('config');
        $wxpay = $model_config->get("code='wxpay.wxa'");//载入设置数据
        $setting = json_decode($wxpay['config'],1);

        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('paymentwxa.wxa.html');
        }
        else
        {
            $data['status']            = ($_POST['status'] == '1');
            $config['status']          = ($_POST['status'] == '1');
            $config['mch_id']          = $_POST['mch_id'];
            $config['mch_key']         = $_POST['mch_key'];
            $config['app_id']          = $_POST['app_id'];
            $config['app_secret']      = $_POST['app_secret'];
            if (!$wxpay) {
                $data['name']              = Lang::get('paymentwxa_name');
                $data['type']              = 'payment';
                $data['description']       = Lang::get('paymentwxa_description');
                $data['code']              = 'wxpay.wxa';
                $data['config'] = $config;
                $data['created_at'] = local_date('Y-m-d H:i:s');
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->add($data);
            } else {
                $data['config'] = $config;
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->edit("code='wxpay.wxa'", $data);
            }
            $this->show_message('edit_wxa_successed');
        }
    }

}