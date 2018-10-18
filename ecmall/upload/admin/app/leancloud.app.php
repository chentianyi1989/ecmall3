<?php

/**
 *    合作伙伴控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class LeancloudApp extends BackendApp
{

    function __construct()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
        $this->model = &m('leancloud');
    }

    /**
     *    管理
     */
    function index()
    {
        $status = array(
            0 => Lang::get('waiting'),
            1 => Lang::get('sent')
        );//数据库0：等待中，1：已发送
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'title',       
                'equal' => 'LIKE',
                'name'  => 'title',
            ),array(
                'field' => 'platform',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'created_at',
                'name'  => 'created_at_from',
                'equal' => '>=',
                // 'handler'=> 'gmstr2time',
            ),array(
                'field' => 'created_at',
                'name'  => 'created_at_to',
                'equal' => '<=',
                // 'handler'   => 'gmstr2time_end',
            ),
            array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            )
        ));
        $page   =   $this->_get_page();    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'id';
            $order = 'desc';
        }
        $leanclouds = $this->model->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); 
        $page['item_count'] = $this->model->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('platform', $this->model->getPlatform());
        $this->assign('status', $status);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('leanclouds', $leanclouds);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('leancloud.index.html');
    }

    function add()
    {
        $leancloud_config = $this->model->getAppLeancloud();  // 是否开启云推送
        if (!$leancloud_config) {
            $this->show_message('leancloud_push_off',
                'go_to_appLeancloud',    'index.php?app=appsetting&act=appLeancloud',
                'back_list',    'index.php?app=leancloud'
            );
            return;
        }
        if (!IS_POST)
        {
            // 推送类型
            $push_type = array(
                1 => Lang::get('instant'),  // 立即发送
                2 => Lang::get('timing'),  // 定时发送
            );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('platform', $this->model->getPlatform());
            $this->assign('link', $this->model->getLinks());
            $this->assign('push_type', $push_type);
            $this->assign('leancloud', $leancloud);
            $this->display('leancloud.form.html');
        }
        else
        {
            $data = array();
            $link = $_POST['link'];
            if ($_POST['link_type'] && $_POST['link_arg']) {
                $link_arg = $_POST['link_arg'];
                $link_type = $_POST['link_type'];
                if ($_POST['link_type'] == 'user_defined') {
                    $link = $link_arg;
                }
                if ($_POST['link_type'] == 'id') {
                    $link = str_replace(':id', $link_arg, $link);
                }
                if ($_POST['link_type'] == 'keywords') {
                    $link = str_replace('?k=关键字', '?k='.$link_arg, $link);
                }
            }
            $data['title']          = $_POST['title'] ? $_POST['title'] : 'APP推送';
            $data['content']        = $_POST['content']; 
            $data['platform']       = $_POST['platform']; 
            $data['push_type']      = $_POST['push_type']; 
            $data['push_at']        = $_POST['push_at'] ? $_POST['push_at'] : local_date('Y-m-d H:i:s'); 
            $data['link']           = $link;
            $data['created_at'] = local_date('Y-m-d H:i:s');
            $data['updated_at'] = local_date('Y-m-d H:i:s');

            
            if (!$id = $this->model->add($data)) {
                $this->show_warning($this->model->get_error());
                return;
            }
            
            $this->model->push($id); //推送消息

            $this->show_message('add_leancloud_successed',
                'back_list',    'index.php?app=leancloud',
                'continue_add', 'index.php?app=leancloud&amp;act=add'
            );
        }

    }

    function drop()
    {
        $id = $_GET['id'] ? trim($_GET['id']) : '';
        if (!$id) {
            $this->$this->show_warning('Hacking Attempt');
            return;
        }

        $ids=explode(',',$id);
        $this->model->drop($ids);
        if ($this->model->has_error())    //删除
        {
            $this->show_warning($this->model->get_error());

            return;
        }

        $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
        $this->show_message('drop_ok',
                'back_list', 'index.php?app=leancloud&page=' . $ret_page);

    }

    function edit()
    {
        $leancloud_id = isset($_GET['id']) ? intval($_GET['id']) : '';
        if (!$leancloud_id) {
            $this->show_warning('no_such_leancloud');
            return;
        }
        if (!IS_POST)
        {
            $find_data = $this->model->find($leancloud_id);
            if (empty($find_data)) {
                $this->show_warning('no_such_leancloud');
                return;
            }
            $leancloud = current($find_data);
            // 推送类型
            $push_type = array(
                1 => Lang::get('instant'),  // 立即发送
                2 => Lang::get('timing'),  // 定时发送
            );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('platform', $this->model->getPlatform());
            $this->assign('link', $this->model->getLinks());
            $this->assign('push_type', $push_type);
            $this->assign('leancloud', $leancloud);
            $this->display('leancloud.form.html');
        }
        else
        {
            $data = array();
            $link = $_POST['link'];
            if ($_POST['link_type'] && $_POST['link_arg']) {
                $link_arg = $_POST['link_arg'];
                $link_type = $_POST['link_type'];
                if ($_POST['link_type'] == 'user_defined') {
                    $link = $link_arg;
                }
                if ($_POST['link_type'] == 'id') {
                    $link = str_replace(':id', $link_arg, $link);
                }
                if ($_POST['link_type'] == 'keywords') {
                    $link = str_replace('?k=关键字', '?k='.$link_arg, $link);
                }
            }
            $data['title']          = $_POST['title'] ? $_POST['title'] : 'APP推送';
            $data['content']        = $_POST['content']; 
            $data['platform']       = $_POST['platform']; 
            $data['push_type']      = $_POST['push_type']; 
            $data['push_at']        = $_POST['push_at'] ? $_POST['push_at'] : local_date('Y-m-d H:i:s'); 
            $data['link']           = $link;
            $data['updated_at']     = local_date('Y-m-d H:i:s');

            $this->model->edit($leancloud_id, $data);
            if ($this->model->has_error())
            {
                $this->show_warning($this->model->get_error());
                return;
            }

            $this->model->push($leancloud_id); //推送消息

            $this->show_message('edit_leancloud_successed',
                'back_list',    'index.php?app=leancloud',
                'continue_add', 'index.php?app=leancloud&amp;act=add'
            );
        }
    }



    function rePush()
    {
        $id = $_GET['id'] ? trim($_GET['id']) : '';
        if (!$id) {
            $this->$this->show_warning('Hacking Attempt');
            return;
        }
        $status = $this->model->push($id);
        $time = local_date('Y-m-d H:i:s');
        $data = array(
            'push_at'     => $time,
            'updated_at'  => $time,
            'status'      => $status ? 1 : 0,
        ); 
        $this->model->edit($id, $data);
        $this->show_message('has_pushed',
            'back_list',    'index.php?app=leancloud'
        );

    }

}
?>
