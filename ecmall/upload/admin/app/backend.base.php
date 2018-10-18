<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class BackendApp extends ECBaseApp
{
    function __construct()
    {
        $this->BackendApp();
    }
    function BackendApp()
    {
        Lang::load(lang_file('admin/common'));
        Lang::load(lang_file('admin/' . APP));
        parent::__construct();
    }
    function login()
    {
        $yunqioauth_model = &m("yunqioauth");
        /* 下面两个判断是为云起免登加的 _run_action路由时会判断是否已登录 未登录会直接执行login 所以在此加个判断*/
        if ($_REQUEST['type'] == 'yunqi' && $_REQUEST['act'] == 'logout') {
            $this->yunqi_logout();exit();
        }
        if ($_REQUEST['type'] == 'yunqi' && $_REQUEST['acttion'] == 'get_certificate') {
            $yunqioauth_model->get_certificate();
        }
        if ($this->visitor->has_login)
        {
            $this->show_warning('has_login');

            return;
        }
        $yunqi_member = $yunqioauth_model->get_yunqi_member();
        if (!IS_POST && $_REQUEST['type'] != 'yunqi')
        {
            if ($yunqi_member) {
                $this->assign('yunqi_member',$yunqi_member);
            }
            $activate_callback = site_url()."/index.php?acttion=get_certificate&type=yunqi";
            $activate_iframe_url = $yunqioauth_model->get_authorize_url($activate_callback);
            $this->assign('activate_iframe_url',$activate_iframe_url);
            $callback = site_url()."/index.php?type=yunqi";
            $iframe_url = $yunqioauth_model->get_authorize_url($callback);
            $this->assign('iframe_url',$iframe_url);

            $yunqi_bg = $yunqioauth_model->get_yunqi_ad('ecmall_login_bg');
            if( isset($yunqi_bg[0]['picpath']) && !empty($yunqi_bg[0]['picpath']) ){
                $this->assign('yunqi_bg',$yunqi_bg[0]['picpath']);
                $this->assign('yunqi_ad_link',$yunqi_bg[0]['link']);
            }

            if (Conf::get('captcha_status.backend'))
            {
                $this->assign('captcha', 1);
            }
            $this->display('login.html');
        }
        else
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];
            $user_id = '';
            if($_REQUEST['type']=='yunqi') {
               if(isset($_GET['code']) && $_GET['code']){
                    $code = $_GET['code'];
                    $res = $yunqioauth_model->get_token($code);
                    if($res['token'] and $res['params']){
                        if (isset($res['params']['data']) && $res['params']['data']) {
                            foreach ($res['params']['data'] as $d_key => $d_value) {
                                $res['params'][$d_key] = $d_value;
                            }
                            unset($res['params']['data']);
                        }
                        if($yunqi_member['passport_uid'] != $res['params']['passport_uid']){
                            $_SESSION['login_err'] = Lang::get('yunqi_account_inactive'); 
                            $yunqioauth_model->yunqi_logout();exit();
                        }else{
                            $_SESSION['yunqi_login'] = true;
                            $_SESSION['TOKEN'] = $res['token'];
                            $yunqioauth_model->check_certi($res);
                            $user_id = $yunqi_member['user_id'];  // 云起账号免登此处定义user_id 下面不走auth验证
                        }
                    }
                }
            }else{
                if (Conf::get('captcha_status.backend') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha'])){
                    $this->show_warning('captcha_faild');

                    return;
                }
            }

            $ms =& ms();
            !$user_id && $user_id = $ms->user->auth($user_name, $password);
            if (!$user_id)
            {
                /* 未通过验证，提示错误信息 */
                $this->show_warning($ms->user->get_error());

                return;
            }

            /* 通过验证，执行登陆操作 */
            if (!$this->_do_login($user_id))
            {
                return;
            }

            if (isset($_SESSION['yunqi_login'])) {
                $url = site_url()."/index.php";
                echo '<script type="text/javascript">parent.location.href="'.$url.'";</script>';exit;
            }

            $this->show_message('login_successed',
                'go_to_admin', 'index.php');
        }
    }

    function logout()
    {
        if (isset($_SESSION['yunqi_login'])) {
            $yunqioauth_model = &m("yunqioauth");
            $yunqioauth_model->yunqi_logout();exit();
        }
        parent::logout();
        $this->show_message('logout_successed',
            'go_to_admin', 'index.php');
    }

    function yunqi_logout()
    {
        parent::logout();
        if ($_SESSION['yunqi_login'] == true) {
            $this->show_message('logout_successed', 'go_to_admin', 'index.php');
        }else{
            $url = site_url()."/index.php";
            $href = "javascript:window.top.location.replace('".$url."')"; 
            $this->show_message($_SESSION['login_err'], 'go_to_admin', $href);
        }
    }

    /**
     * 执行登陆操作
     *
     * @param int $user_id
     * @return bool
     */
    function _do_login($user_id)
    {
        $mod_user =& m('member');
        $user_info = $mod_user->get(array(
            'conditions' => $user_id,
            'join'       => 'manage_mall',
            'fields'     => 'this.user_id, user_name, reg_time, last_login, last_ip, privs'
        ));

        if (!$user_info['privs'])
        {
            $this->show_warning('not_admin');

            return false;
        }

        /* 分派身份 */
        $this->visitor->assign(array(
            'user_id'       => $user_info['user_id'],
            'user_name'     => $user_info['user_name'],
            'reg_time'      => $user_info['reg_time'],
            'last_login'    => $user_info['last_login'],
            'last_ip'       => $user_info['last_ip'],
        ));

        /* 更新登录信息 */
        $time = gmtime();
        $ip   = real_ip();
        $mod_user->edit($user_id, "last_login = '{$time}', last_ip='{$ip}', logins = logins + 1");

        return true;
    }

    /**
     *    获取JS语言项
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function jslang($lang = '')
    {
        $lang = Lang::fetch(lang_file('admin/jslang'));
        parent::jslang($lang);
    }

    /**
     *    后台的需要权限验证机制
     *
     *    @author    Garbin
     *    @return    void
     */
    function _run_action()
    {
        /* 先判断是否登录 */
        if (!$this->visitor->has_login)
        {
            $this->login();

            return;
        }

        /* 登录后判断是否有权限 */
        if (!$this->visitor->i_can('do_action', $this->visitor->get('privs')))
        {
            $this->show_warning('no_permission');

            return;
        }

        /* 运行 */
        parent::_run_action();
    }

    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = APP_ROOT . '/templates';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/admin';
        $this->_view->res_base      = site_url() . '/templates';
        $this->_view->lib_base      = dirname(site_url()) . '/includes/libraries/javascript';
    }
    
    /**
     *   获取商城当前模板名称
     */
    function _get_template_name()
    {
        $template_name = Conf::get('template_name');
        if (!$template_name)
        {
            $template_name = 'default';
        }

        return $template_name;
    }

    /**
     *    获取商城当前风格名称
     */
    function _get_style_name()
    {
        $style_name = Conf::get('style_name');
        if (!$style_name)
        {
            $style_name = 'default';
        }

        return $style_name;
    }
    
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new AdminVisitor());
    }

    /* 清除缓存 */
    function _clear_cache()
    {
        $cache_server =& cache_server();
        $cache_server->clear();
    }
    
    function display($tpl)
    {
        $this->assign('real_backend_url', site_url());
        parent::display($tpl);
    }
}

/**
 *    后台访问者
 *
 *    @author    Garbin
 *    @usage    none
 */
class AdminVisitor extends BaseVisitor
{
    var $_info_key = 'admin_info';
    /**
     *    获取用户详细信息
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_detail()
    {
        $model_member =& m('member');
        $detail = $model_member->get(array(
            'conditions'    => "member.user_id = '{$this->info['user_id']}'",
            'join'          => 'manage_mall',                 //关联查找看看是否有店铺
        ));
        unset($detail['user_id'], $detail['user_name'], $detail['reg_time'], $detail['last_login'], $detail['last_ip']);

        return $detail;
    }
}

/* 实现消息基础类接口 */
class MessageBase extends BackendApp {};

/* 实现模块基础类接口 */
class BaseModule  extends BackendApp {};

/* 消息处理器 */
require(ROOT_PATH . '/eccore/controller/message.base.php');

?>
