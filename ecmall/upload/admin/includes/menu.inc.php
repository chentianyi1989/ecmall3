<?php

return array(
    'dashboard' => array(
        'text'      => Lang::get('dashboard'),
        'subtext'   => Lang::get('offen_used'),
        'default'   => 'welcome',
        'children'  => array(
            'welcome'   => array(
                'text'  => Lang::get('welcome_page'),
                'url'   => 'index.php?act=welcome',
            ),
            'aboutus'   => array(
                'text'  => Lang::get('aboutus_page'),
                'url'   => 'index.php?act=aboutus',
            ),
            'base_setting'  => array(
                'parent'=> 'setting',
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'parent'=> 'user',
                'url'   => 'index.php?app=user',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'parent'=> 'store',
                'url'   => 'index.php?app=store',
            ),
            'goods_manage'  => array(
                'text'  => Lang::get('goods_manage'),
                'parent'=> 'goods',
                'url'   => 'index.php?app=goods',
            ),
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'parent'=> 'trade',
                'url'   => 'index.php?app=order'
            ),
             'license' => array(
                'text'  => Lang::get('license'),
                'url'   => 'index.php?app=license&act=l_list'
            ),  
    
        ),
    ),
    // 设置
    'setting'   => array(
        'text'      => Lang::get('setting'),
        'default'   => 'base_setting',
        'children'  => array(
            'base_setting'  => array(
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'region' => array(
                'text'  => Lang::get('region'),
                'url'   => 'index.php?app=region',
            ),
            'payment' => array(
                'text'  => Lang::get('payment'),
                'url'   => 'index.php?app=payment',
            ),
            'theme' => array(
                'text'  => Lang::get('theme'),
                'url'   => 'index.php?app=theme',
            ),
            'template' => array(
                'text'  => Lang::get('template'),
                'url'   => 'index.php?app=template',
            ),
            'mailtemplate' => array(
                'text'  => Lang::get('noticetemplate'),
                'url'   => 'index.php?app=mailtemplate',
            ),
        ),
    ),
    // 商品
    'goods' => array(
        'text'      => Lang::get('goods'),
        'default'   => 'goods_manage',
        'children'  => array(
            'gcategory' => array(
                'text'  => Lang::get('gcategory'),
                'url'   => 'index.php?app=gcategory',
            ),
            'brand' => array(
                'text'  => Lang::get('brand'),
                'url'   => 'index.php?app=brand',
            ),
            'goods_manage' => array(
                'text'  => Lang::get('goods_manage'),
                'url'   => 'index.php?app=goods',
            ),
            'recommend_type' => array(
                'text'  => LANG::get('recommend_type'),
                'url'   => 'index.php?app=recommend'
            ),

        ),
    ),
    // 店铺
    'store'     => array(
        'text'      => Lang::get('store'),
        'default'   => 'store_manage',
        'children'  => array(
            'sgrade' => array(
                'text'  => Lang::get('sgrade'),
                'url'   => 'index.php?app=sgrade',
            ),
            'scategory' => array(
                'text'  => Lang::get('scategory'),
                'url'   => 'index.php?app=scategory',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'url'   => 'index.php?app=store',
            ),
        ),
    ),
    // 会员
    'user' => array(
        'text'      => Lang::get('user'),
        'default'   => 'user_manage',
        'children'  => array(
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'url'   => 'index.php?app=user',
            ),
            'admin_manage' => array(
                'text' => Lang::get('admin_manage'),
                 'url'   => 'index.php?app=admin',
             ),
             'user_notice' => array(
                'text' => Lang::get('user_notice'),
                'url'  => 'index.php?app=notice',
             ),
        ),
    ),
    // 交易
    'trade' => array(
        'text'      => Lang::get('trade'),
        'default'   => 'order_manage',
        'children'  => array(
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'url'   => 'index.php?app=order'
            ),
            'proceeds_manage' => array(
                'text'  => Lang::get('proceeds_manage'),
                'url'   => 'index.php?app=proceeds'
            ),
        ),
    ),
    // 网站
    'website' => array(
        'text'      => Lang::get('website'),
        'default'   => 'acategory',
        'children'  => array(
            'acategory' => array(
                'text'  => Lang::get('acategory'),
                'url'   => 'index.php?app=acategory',
            ),
            'article' => array(
                'text'  => Lang::get('article'),
                'url'   => 'index.php?app=article',
            ),
            'partner' => array(
                'text'  => Lang::get('partner'),
                'url'   => 'index.php?app=partner',
            ),
            'navigation' => array(
                'text'  => Lang::get('navigation'),
                'url'   => 'index.php?app=navigation',
            ),
            'db' => array(
                'text'  => Lang::get('db'),
                'url'   => 'index.php?app=db&amp;act=backup',
            ),
            'groupbuy' => array(
                'text' => Lang::get('groupbuy'),
                'url'  => 'index.php?app=groupbuy',
            ),
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
            ),
            'share_link' => array(
                'text'  =>  LANG::get('share_link'),
                'url'   => 'index.php?app=share',
            ),
        ),
    ),
    // 扩展
    'extend' => array(
        'text'      => Lang::get('extend'),
        'default'   => 'plugin',
        'children'  => array(
            'plugin' => array(
                'text'  => Lang::get('plugin'),
                'url'   => 'index.php?app=plugin',
            ),
            'module' => array(
                'text'  => Lang::get('module'),
                'url'   => 'index.php?app=module&act=manage',
            ),
            'widget' => array(
                'text'  => Lang::get('widget'),
                'url'   => 'index.php?app=widget',
            ),
        ),
    ),
    // 移动端管理
    'ecmobile' => array(
        'text'      => Lang::get('ecmobile'),
        'default'   => 'h5',
        'children'  => array(
            // 移动端广告配置
            'banner' => array(
                'text'  => Lang::get('banner'),
                'url'   => 'index.php?app=banner',
            ),
            // 移动端精品推荐
            'waprecommend' => array(
                'text'  => Lang::get('waprecommend'),
                'url'   => 'index.php?app=waprecommend',
            ),
            // h5应用配置
            'h5' => array(
                'text'  => Lang::get('h5'),
                'url'   => 'index.php?app=h5&act=social',
            ),
            // h5店铺二维码
            // 'lead' => array(
            //     'text'  => Lang::get('lead'),
            //     'url'   => 'index.php?app=lead',
            // ),
            // app推送管理
            'leancloud' => array(
                'text'  => Lang::get('leancloud'),
                'url'   => 'index.php?app=leancloud',
            ),
            // app应用配置
            'appsetting' => array(
                'text'  => Lang::get('appsetting'),
                'url'   => 'index.php?app=appsetting&act=appPayment',
            ),
            // 小程序应用配置
            // 'wxa' => array(
            //     'text'  => Lang::get('wxa'),
            //     'url'   => 'index.php?app=wxa&act=oauthWxa',
            // ),
        ),
    ),
    // 云服务中心
    'yunqiservice' => array(
        'text'      => Lang::get('yunqiservice'),
        'default'   => 'servicemarket',
        'children'  => array(
            // 云起物流
            'yunqilogistic' => array(
                'text'  => Lang::get('yunqi_logistic'),
                'url'   => 'index.php?app=yunqilogistic',
            ),
            // 短信平台
            'smsresource' => array(
                'text'  => Lang::get('sms_resource'),
                'url'   => 'index.php?app=smsresource',
            ),
            'servicemarket' => array(
                'text'  => Lang::get('service_market'),
                'url'   => 'index.php?app=servicemarket',
            ),
        ),
    ),
);

?>
