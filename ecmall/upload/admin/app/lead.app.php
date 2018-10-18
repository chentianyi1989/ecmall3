<?php

/**
 *    基本设置控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class LeadApp extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
    }

    /**
     *    h5店铺二维码
     *
     *    @author    Pangxp
     *    @return    void
     */
    function index()
    {
        $url_cur = $_SERVER['HTTP_REFERER'];
        $url_arr = explode('/admin',$url_cur);
        $this->assign('url',$url_arr[0].'/h5');
        $this->display('lead.index.html');
    }
}