<?php

return array(
    'code'      => 'yunqi',
    'name'      => Lang::get('yunqi'),
    'desc'      => Lang::get('yunqi_desc'),
    'is_online' => '1',
    'author'    => 'ECMall TEAM',
    'website'   => 'https://charging.teegon.com/',
    'version'   => '1.0',
    'currency'  => Lang::get('yunqi_currency'),
    'config'    => array(
        'shopexid'   => array(        //账号
            'text'  => Lang::get('shopexid'),
            // 'desc'  => Lang::get('shopexid_desc'),
            'type'  => 'text',
        ),
        'yunqi_appkey'       => array(        //密钥
            'text'  => Lang::get('client_id'),
            // 'desc'  => Lang::get('yunqi_appkey_desc'),
            'type'  => 'text',
        ),
        'yunqi_appsecret'   => array(        //合作者身份ID
            'text'  => Lang::get('client_secret'),
            'type'  => 'text',
        ),
        
    ),
);

?>