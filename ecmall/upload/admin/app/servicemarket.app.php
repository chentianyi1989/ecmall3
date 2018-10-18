<?php

/**
 *    服务市场
 */
class ServicemarketApp extends BackendApp
{


    /**
     *    服务市场
     *
     *    @author    Pangxp
     *    @return    void
     */
    function index()
    {
        $this->assign('iframe_url', YUNQI_SERVICE_URL . 'cid=96&source='.iframe_source_encode('ecmall'));
        $this->display('yunqi_iframe_url.index.html');
       
    }

}