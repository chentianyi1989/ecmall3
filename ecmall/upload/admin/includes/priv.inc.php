<?php

/**
 * ECMALL: 网站后台管理左侧菜单数据
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: inc.menu.php 16 2007-12-23 15:36:24Z Redstone $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'mall_setting' => array
    (
        'default'     => 'default|all',//后台登录
        'setting'     => 'setting|all',//网站设置
        'region'       => 'region|all',//地区设置
        'payment'    => 'payment|all',//支付方式
        'theme'     => 'theme|all',//主题设置
        'mailtemplate'   => 'mailtemplate|all',//邮件模板
        'template'  => 'template|all',//模板编辑
    ),
    'goods_admin' => array
    (
        'gcategory'    => 'gcategory|all',//分类管理
        'brand' => 'brand|all',//品牌管理
        'goods'    => 'goods|all',//商品管理
        'recommend'    => 'recommend|all',//推荐类型
    ),
    'store_admin' => array
    (
        'sgrade'    => 'sgrade|all',//店铺等级
        'scategory'     => 'scategory|all',//店铺分类
        'store'   => 'store|all',//店铺管理
    ),
    'member' => array
    (
        'user'  => 'user|all',//会员管理
        'admin' => 'admin|all',//管理员管理
        'notice' => 'notice|all',//会员通知
    ),
    'order' => array
    (
        'order'   => 'order|all',//订单管理
        'proceeds'   => 'proceeds|all',//统一收款单管理
    ),
    'website' => array
    (
        'acategory'    => 'acategory|all',//文章分类
        'article'      => array('article' => 'article|all', 'upload' => array('comupload' => 'comupload|all', 'swfupload' => 'swfupload|all')),//文章管理
        'partner'      => 'partner|all',//合作伙伴
        'navigation'   => 'navigation|all',//页面导航
        'db'           => 'db|all',//数据库
        'groupbuy'     => 'groupbuy|all',//团购
        'consulting'   => 'consulting|all',//咨询
        'share_link'   => 'share|all',//分享管理

    ),

    'external' => array
    (
        'plugin' => 'plugin|all',//插件管理
        'module'   => 'module|all',//模块管理
        'widget'   => 'widget|all',//挂件管理
    ),
    // 移动端管理
    'ecmobile' => array
    (
        'banner' => 'banner|all',// 移动端广告配置
        'waprecommend' => 'waprecommend|all',// 移动端精品推荐
        'h5'   => 'h5|all',// h5应用配置
        // 'lead'   => 'lead|all',// h5店铺二维码
        'leancloud'   => 'leancloud|all',// app推送管理
        'appsetting'   => 'appsetting|all',// app应用配置
        // 'wxa'   => 'wxa|all',// 小程序应用配置
    ),
    // 云服务中心
    'yunqiservice' => array
    (
        'yunqilogistic' => 'yunqilogistic|all',// 云起物流
        'smsresource'   => 'smsresource|all',// 短信平台
    ),
    'clear_cache' =>array
    (
        'clear_cache' => 'clear_cache|all',//清空缓存
    )
);
?>