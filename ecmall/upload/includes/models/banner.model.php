<?php

/* 移动端广告配置 banner */
class BannerModel extends BaseModel
{
    var $table  = 'banner';
    var $prikey = 'id';
    var $_name  = 'banner';

    /**
     * 删除移动端广告配置相关数据
     *
     * @param   string  $ids  移动端广告配置id号，用逗号隔开
     */
    function unlinkImage($ids)
    {
        if (empty($ids)) 
            return;
        $images = parent::find(array(
            'conditions' => 'id' . db_create_in($ids),
            'fields' => 'id, photo',
        ));

        foreach ($images as $image) {
            if (!empty($image['photo']) && trim($image['photo']) && substr($image['photo'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['photo']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['photo']);
            }
        }
    }

    /**
     *    删除banner图片
     */
    function drop($conditions, $fields = 'photo')
    {
        $droped_rows = parent::drop($conditions, $fields);
        if ($droped_rows)
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $key => $value)
            {
                if ($value['photo'])
                {
                    @unlink(ROOT_PATH . '/' . $value['photo']);  //删除photo文件
                }
            }
            reset_error_handler();
        }

        return $droped_rows;
    }

}

?>
