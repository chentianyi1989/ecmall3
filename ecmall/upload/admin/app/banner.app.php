<?php

/**
 *    基本设置控制器
 *
 *    @author   Pangxp
 *    @usage    none
 */
class BannerApp extends BackendApp
{
    function __construct()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
        $this->_banner_mod = &m('banner');
    }

    /**
     *    移动端广告配置
     *
     *    @author    Pangxp
     *    @return    void
     */
    function index()
    {
        $page = $this->_get_page();    //获取分页信息
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
        $banners = $this->_banner_mod->find(array(
            'count' => true,
            'order' => "$sort $order",
            'limit' => $page['limit'],
        ));
        foreach ($banners as $key => $banner)
        {
            $banner['photo'] && $banners[$key]['photo'] = dirname(site_url()) . '/' . $banner['photo'];
        }
        $page['item_count']=$this->_banner_mod->getCount();   //获取统计数据
        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->assign('banners', $banners);
        $this->_format_page($page);
        $this->assign('page_info', $page);   //将分页信息传递给视图，用于形成分页条
        $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
        $this->display('banner.index.html');
    }

    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $banners = array(
                'sort_order' => 255,
                'status' => 1,
                'type' => 'pc',
            );
            // 是否展示
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            // 是否移动端
            // $pc_or_wap = array(
            //     'wap' => Lang::get('yes'),
            //     'pc' => Lang::get('no'),
            // );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('yes_or_no', $yes_or_no);
            // $this->assign('pc_or_wap', $pc_or_wap);
            $this->assign('banners', $banners);
            $this->display('banner.form.html');
        }
        else
        {
            $data = array();
            $data['id']             = $_POST['id'] ? $_POST['id'] : '';
            $data['title']          = $_POST['title'] ? $_POST['title'] : '移动端广告banner';
            // $data['type']           = $_POST['type'] ? $_POST['type'] : 'pc'; // 默认pc端banner
            $data['link']           = $_POST['link'];
            $data['status']         = 1;
            $data['sort_order']     = $_POST['sort_order'] ? $_POST['sort_order'] : '255'; // 排序
            $data['created_at'] = time();
            $data['updated_at'] = time();

            if (!$id = $this->_banner_mod->add($data))  //获取id
            {
                $this->show_warning($this->_banner_mod->get_error());

                return;
            }

            /* 处理上传的图片 */
            $photo = $this->_upload_photo($id);
            if ($photo === false)
            {
                return;
            }
            $photo && $this->_banner_mod->edit($id, array('photo' => $photo)); //将photo地址记下

            $this->show_message('add_banner_successed',
                'back_list',    'index.php?app=banner',
                'continue_add', 'index.php?app=banner&amp;act=add'
            );
        }
    }

    function drop(){
        $id = $_GET['id'] ? trim($_GET['id']) : '';
        if (!$id) {
            $this->$this->show_warning('Hacking Attempt');
            return;
        }

        $banner_ids=explode(',',$id);
        $this->_banner_mod->drop($banner_ids);
        if ($this->_banner_mod->has_error())    //删除
        {
            $this->show_warning($this->_banner_mod->get_error());

            return;
        }

        // $this->_banner_mod->drop($ids);
        // $this->_banner_mod->unlinkImage($id);

        $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
        $this->show_message('drop_ok',
                'back_list', 'index.php?app=banner&page=' . $ret_page);

    }

    /**
     *    编辑banner
     */
    function edit()
    {
        $banner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$banner_id)
        {
            $this->show_warning('no_such_banner');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_banner_mod->find($banner_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_banner');

                return;
            }
            $banners    =   current($find_data);
            if ($banners['photo'])
            {
                $banners['photo']  =   dirname(site_url()) . "/" . $banners['photo'];
            }
            // 是否展示
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('banners', $banners);
            $this->display('banner.form.html');
        }
        else
        {

            $data = array();
            $data['title']          = $_POST['title'] ? $_POST['title'] : '移动端广告banner';
            $data['link']           = $_POST['link'];
            $data['status']         = $_POST['status'] ? $_POST['status'] : 1;;
            $data['sort_order']     = $_POST['sort_order'] ? $_POST['sort_order'] : '255'; // 排序
            $data['updated_at']     = time();

            /* 处理上传的图片 */
            $photo = $this->_upload_photo($banner_id);
            if ($photo === false)
            {
                return;
            }

            $photo && $data['photo'] = $photo;
           
            $rows=$this->_banner_mod->edit($banner_id, $data);
            if ($this->_banner_mod->has_error())
            {
                $this->show_warning($this->_banner_mod->get_error());

                return;
            }

            $this->show_message('edit_ok',
                'back_list',        'index.php?app=banner',
                'edit_again',    'index.php?app=banner&amp;act=edit&amp;id=' . $banner_id);
        }
    }

    function _upload_photo($id)
    {
        $file = $_FILES['photo'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            return '';
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->allowed_size(SIZE_STORE_BANNER); //限制文件大小  banner 1M
        $uploader->addFile($_FILES['photo']);//photo
        if (!$uploader->file_info())
        {
            $this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=banner&amp;act=edit&amp;id=' . $id);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/banner', $id))   //保存到指定目录，并以指定文件名$id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }

}