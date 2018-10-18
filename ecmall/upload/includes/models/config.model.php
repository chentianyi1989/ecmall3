<?php

/* 移动端配置 */
class ConfigModel extends BaseModel
{
    var $table  = 'config';
    var $prikey = 'id';
    var $_name  = 'config';

    var $_autov     =   array(
        'name'  =>  array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'type'  => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'description'  => array(
            'filter'    => 'trim',
        ),
        'code'  => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'config'        => array(
            'required'  => true,
            'filter'    => 'json_encode',
        ),
        'status'       => array(
            'filter'    => 'intval',
        ),
    );


}

?>