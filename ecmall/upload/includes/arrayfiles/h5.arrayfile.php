<?php

/**
 *    基本设置
 *
 *    @author    Hyber
 *    @usage    none
 */
class H5Arrayfile extends BaseArrayfile
{
    var $_filename;
    function __construct()
    {
        $this->_filename = ROOT_PATH . '/data/h5.inc.php';
    }
    /**
     * 获取默认设置
     *
     * @author    Hyber
     * @return    void
     */
    function get_default()
    {
        return array();
    }
}
?>
