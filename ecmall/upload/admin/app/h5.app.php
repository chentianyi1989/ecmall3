<?php

/**
 *    基本设置控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class H5App extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
    }

    /**
     *    H5社交设置
     *
     *    @author    Pangxp
     *    @return    void
     */
    function social()
    {
        $model_config = &m('config');
        $wechat = $model_config->get("code='wechat.web'");//载入设置数据
        $setting = json_decode($wechat['config'],1);

        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('h5.social.html');
        }
        else
        {
            $data['status']            = ($_POST['status'] == '1');
            $config['status']          = ($_POST['status'] == '1');
            $config['app_id']          = $_POST['app_id'];
            $config['app_secret']      = $_POST['app_secret'];
            if (!$wechat) {
                $data['name']              = '微信登录';
                $data['type']              = 'oauth';
                $data['description']       = '微信登录';
                $data['code']              = 'wechat.web';
                $data['config'] = $config;
                $data['created_at'] = local_date('Y-m-d H:i:s');
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->add($data);
            } else {
                $data['config'] = $config;
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->edit("code='wechat.web'", $data);
            }
            $this->show_message('edit_social_successed');
        }
    }

    /**
     *    H5微信支付配置
     *
     *    @author    Pangxp
     *    @return    void
     */
    function wxpay()
    {
        $model_config = &m('config');
        $wxpay = $model_config->get("code='wxpay.web'");//载入设置数据
        $setting = json_decode($wxpay['config'],1);

        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('h5.wxpay.html');
        }
        else
        {
            $wechat = $model_config->get("code='wechat.web'");
            if ($wechat) 
                $wechat_config = json_decode($wechat['config'],1);
            $data['status']            = ($_POST['status'] == '1');
            $config['status']          = ($_POST['status'] == '1');
            $config['mch_id']          = $_POST['mch_id'];
            $config['mch_key']         = $_POST['mch_key'];
            $config['app_id']          = $wechat_config['app_id'];
            $config['app_secret']      = $wechat_config['app_secret'];
            if (!$wxpay) {
                $data['name']              = '微信公众号支付';
                $data['type']              = 'payment';
                $data['description']       = '微信公众号支付';
                $data['code']              = 'wxpay.web';
                $data['config'] = $config;
                $data['created_at'] = local_date('Y-m-d H:i:s');
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->add($data);
            } else {
                $data['config'] = $config;
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->edit("code='wxpay.web'", $data);
            }
            $this->show_message('edit_wxpay_successed');
        }

    }

    /**
     *    H5支付宝支付配置
     *
     *    @author    Pangxp
     *    @return    void
     */
    function alipay()
    {
        $model_config = &m('config');
        $alipay = $model_config->get("code='alipay.wap'");//载入设置数据
        $setting = json_decode($alipay['config'],1);

        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('h5.alipay.html');
        }
        else
        {
            $data['status']           = ($_POST['status'] == '1');
            $config['partner_id']       = $_POST['partner_id'];
            $config['seller_id']        = $_POST['seller_id'];
            $config['private_key']      = $_POST['private_key'];
            $config['public_key']       = $_POST['public_key'];

            if (!$alipay) {
                $data['name']              = '支付宝手机网站支付';
                $data['type']              = 'payment';
                $data['description']       = '支付宝支付';
                $data['code']              = 'alipay.wap';
                $data['config'] = $config;
                $data['created_at'] = local_date('Y-m-d H:i:s');
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->add($data);
            } else {
                $data['config'] = $config;
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $model_config->edit("code='alipay.wap'", $data);
            }
            $this->show_message('edit_alipay_successed');
        }
    }

}