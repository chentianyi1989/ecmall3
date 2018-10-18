<?php

/**
 *    基本设置控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class AppsettingApp extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
        $this->model_config = &m('config');
    }

    /*app支付配置*/
    function appPayment()
    {
        if (!IS_POST)
        {
            $setting_data = $setting = array(
                'alipay.app' => array('url' => 'http://open.alipay.com'), 
                'wxpay.app' => array('url' => 'http://pay.weixin.qq.com'), 
                'unionpay.app' => array('url' =>'https://open.unionpay.com/ajweb/index')
            );
            $appPayment = $this->model_config->find(array('conditions' => 'code' . db_create_in(array_keys($setting))));//载入设置数据
            if ($appPayment) {
                foreach ($appPayment as $key => $value) {
                    $setting_data[$value['code']] = json_decode($value['config'],1);
                }
                foreach ($setting as $k => $val) {
                    $setting_data[$k]['url'] = $setting[$k]['url'];
                }
            }else{
                $setting_data = $setting;
            }
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('settings', $setting_data);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('appsettingApp.appPayment.html');
        }
        else
        {
            $config = $_POST['config'];
            $code = $_POST['code'];
            $data['status'] = $config['status'];
            if ($code == 'unionpay.app' && $_FILES['cert']) {
                // cer 文件大小限制 400k
                if ($_FILES['cert']['size'] > SIZE_STORE_CERT) {
                    $this->show_warning('file_too_large');
                    return;
                }
                $cert = mysql_real_escape_string(file_get_contents($_FILES['cert']['tmp_name']));
                // $config['cert']['type'] = $_FILES['cert']['type'];
                // $config['cert']['binarydata'] = $cert;
                $data['file'] = $cert;
            }
            $data['config'] = $config;
            $row = $this->model_config->get("code = '{$code}'");
            if (!$row) {
                $code_name = str_replace('.', '_', $code).'_name';
                $code_description = str_replace('.', '_', $code).'_description';
                $data['name']              = Lang::get($code_name);
                $data['type']              = 'payment';
                $data['description']       = Lang::get($code_description);
                $data['code']              = $code;
                $data['created_at']        = local_date('Y-m-d H:i:s');
                $data['updated_at']        = local_date('Y-m-d H:i:s');
                $this->model_config->add($data);
            } else {
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $this->model_config->edit("code='{$code}'", $data);
            }
            $this->show_message('edit_successed');
        }

    }

    /*社交配置*/
    function appSocial()
    {
        if (!IS_POST)
        {
            $setting_data = $setting = array(
                'wechat.app' => array('url' => 'https://open.weixin.qq.com/'), 
                'weibo.app' => array('url' => 'http://open.weibo.com/'), 
                'qq.app' => array('url' =>'http://open.qq.com/')
            );
            $appSocial = $this->model_config->find(array('conditions' => 'code' . db_create_in(array_keys($setting))));//载入设置数据
            if ($appSocial) {
                foreach ($appSocial as $key => $value) {
                    $setting_data[$value['code']] = json_decode($value['config'],1);
                }
                foreach ($setting as $k => $val) {
                    $setting_data[$k]['url'] = $setting[$k]['url'];
                }
            }else{
                $setting_data = $setting;
            }
            $this->assign('settings', $setting_data);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('appsettingApp.appSocial.html');
        }
        else
        {
            $config = $_POST['config'];
            $code = $_POST['code'];
            $data['config'] = $config;
            $data['status'] = $config['status'];
            $row = $this->model_config->get("code = '{$code}'");
            if (!$row) {
                $code_name = str_replace('.', '_', $code).'_name';
                $code_description = str_replace('.', '_', $code).'_description';
                $data['name']              = Lang::get($code_name);
                $data['type']              = 'oauth';
                $data['description']       = Lang::get($code_description);
                $data['code']              = $code;
                $data['created_at']        = local_date('Y-m-d H:i:s');
                $data['updated_at']        = local_date('Y-m-d H:i:s');
                $this->model_config->add($data);
            } else {
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $this->model_config->edit("code='{$code}'", $data);
            }
            $this->show_message('edit_successed');
        }

    }

    /*云推送*/
    function appLeancloud()
    {
        $appLeancloud = $this->model_config->get("code='leancloud'");//载入设置数据
        if (!IS_POST)
        {
            $setting = json_decode($appLeancloud['config'],1);
            $this->assign('setting', $setting);
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->display('appsettingApp.appLeancloud.html');
        }
        else
        {
            $config = $_POST['config'];
            $data['status'] = $config['status'];
            $data['config'] = $config;
            if (!$appLeancloud) {
                $data['name']              = Lang::get('leancloud_name');
                $data['type']              = 'cloud';
                $data['description']       = Lang::get('leancloud_description');
                $data['code']              = 'leancloud';
                $data['created_at']        = local_date('Y-m-d H:i:s');
                $data['updated_at']        = local_date('Y-m-d H:i:s');
                $this->model_config->add($data);
            } else {
                $data['updated_at'] = local_date('Y-m-d H:i:s');
                $this->model_config->edit("code='leancloud'", $data);
            }
            $this->show_message('edit_successed');
        }

    }


}