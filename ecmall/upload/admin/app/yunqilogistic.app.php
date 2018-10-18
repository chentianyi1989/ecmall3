<?php

/**
 *    云起物流跟踪
 * 
 *    @author   Pangxp
 *    @usage    none
 */
class YunqilogisticApp extends BackendApp
{


    /**
     *    物流跟踪
     *
     *    @author    Pangxp
     *    @return    void
     */
    function index()
    {
        $this->assign('iframe_url', YUNQI_LOGISTIC_URL . '?ctl=exp&act=index&source='.iframe_source_encode('ecmall'));
        $this->display('yunqi_iframe_url.index.html');
       
    }

}