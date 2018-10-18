<?php

return array(
    'code'      => 'alipaywap',
    'name'      => Lang::get('alipaywap'),
    'desc'      => Lang::get('alipaywap_desc'),
    'is_online' => '1',
    'author'    => 'ECMall TEAM',
    'website'   => 'http://yunqi.shopex.cn/products/ecmall',
    'version'   => '1.0',
    'currency'  => Lang::get('alipaywap_currency'),
    'config'    => array(
        'alipaywap_partner_id'   => array(
            'text'  => Lang::get('alipaywap_partner_id'),
            // 'desc'  => Lang::get('alipaywap_account_desc'),
            'type'  => 'text',
        ),
        'alipaywap_seller_id'       => array(
            'text'  => Lang::get('alipaywap_seller_id'),
            'type'  => 'text',
        ),
        'alipaywap_private_key'   => array(
            'text'  => Lang::get('alipaywap_private_key'),
            'type'  => 'text',
        ),
        'alipaywap_public_key'  => array(
            'text'      => Lang::get('alipaywap_public_key'),
            'type'  => 'text',
        ),
    ),
);

?>