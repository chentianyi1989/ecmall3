<?php

/**
 *    移动端精品推荐
 *
 *    @author   Pangxp
 *    @usage    none
 */
class WaprecommendApp extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
    }

    function index()
    {
        $recom_mod =& bm('recommend', array('_store_id' => 0));
        $recommends = $recom_mod->get_options();
        $config_mod = &m('config');
        $recommend = $config_mod->get("code = 'wap.recommend'");
        if ($recommend) {
            $recommend && $recommend_config = json_decode($recommend['config'],1);
            $recommends['recom_id'] = $recommend_config['recom_id'];
        }
        if (!IS_POST)
        {
            $this->assign('recommends', $recommends);
            $this->display('waprecommend.index.html');
        }
        else
        {
            $config['recom_id']   = $_POST['recom_id'];
            $data['status'] = $_POST['recom_id'] ? 1 : 0;
            $now = local_date('Y-m-d H:i:s');
            if (!$recommend) {
                $data['name']              = '移动端精品推荐';
                $data['type']              = 'recommend';
                $data['description']       = '移动端精品推荐';
                $data['code']              = 'wap.recommend';
                $data['config'] = $config;
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $config_mod->add($data);
            }else{
                $data['config'] = $config;
                $data['updated_at'] = $now;
                $config_mod->edit("code='wap.recommend'", $data);
            }

            $this->show_message('edit_wap_recommend_setting_successed');
        }
    }
}