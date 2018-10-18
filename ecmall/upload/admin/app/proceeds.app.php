<?php

/**
 *    合作伙伴控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class ProceedsApp extends BackendApp
{
    /**
     *    管理
     *
     *    @author    Pangxp
     *    @param     none
     *    @return    void
     */
    function index()
    {
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'pay_status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'pay_time',
                'name'  => 'pay_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'pay_time',
                'name'  => 'pay_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'money',
                'name'  => 'money_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'money_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $model_proceeds =& m('proceeds');
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'log_id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'log_id';
            $order = 'desc';
        }
        $proceeds = $model_proceeds->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        $page['item_count'] = $model_proceeds->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('proceeds', $proceeds);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('proceeds.index.html');
    }

    /* 数据导出 */
    function export(){
        $log_id = $_GET['log_id'] ? trim($_GET['log_id']) : '';
        if (!$log_id) {
            $this->$this->show_warning('Hacking Attempt');
            return;
        }
        $sort  = 'log_id';
        $order = 'desc';
        $model_proceeds =& m('proceeds');

        $fields = implode(',', array_keys($model_proceeds->columns));
        $proceeds = $model_proceeds->find(array(
            'conditions'    => 'log_id' . db_create_in($log_id),
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'fields'        => "$fields",
            'count'         => true             //允许统计
        ));

        /* 字段转换 */
        foreach ($proceeds as &$proceed) {
            if (isset($proceed['pay_time'])) {
                $proceed['pay_time'] = local_date("Y-m-d H:i:s", $proceed['pay_time']);
            }
            if (isset($proceed['pay_status'])) {
                $proceed['pay_status'] = proceeds_status($proceed['pay_status']);
            }
            if (isset($proceed['type'])) {
                $proceed['type'] = pay_type($proceed['type']);
            }
        }
        // $this->export_to_csv($proceeds, 'proceeds', 'gbk');
        /* 导出Excel */
        $charset = 'utf8';
        $filename = 'proceeds-'.time();
        import('ExcelWriter.lib');
        $excelWriter = new ExcelWriter($charset, $filename);
        $row = &$excelWriter->add_row();
        foreach ($model_proceeds->columns as $cKey => $cValue) {
            $excelWriter->add_col($row, $cValue);
        }
        $excelWriter->add_array($proceeds);
        $excelWriter->output();
    }

    /* 后续考虑是否需要 */
    function view(){

    }

}
?>
