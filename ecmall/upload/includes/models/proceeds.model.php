<?php

/* 平台统一收款单 Proceeds */
class ProceedsModel extends BaseModel
{
    var $table  = 'proceeds_log';
    // var $alias  = 'proceeds_alias';
    var $prikey = 'log_id';
    var $_name  = 'proceeds';

    var $columns = array(
            'log_id'        => '日志id',
            'seller_name'   => '卖家名称',
            'buyer_name'    => '买家名称',
            'payment_name'  => '支付方式',
            'order_sn'      => '订单号',
            'pay_time'      => '支付时间',
            'order_amount'  => '订单总价',
            'goods_amount'  => '商品总价',
            'money'         => '支付金额',
            'out_trade_sn'  => '外部交易号',
            'discount'      => '折扣',
            'pay_status'    => '支付状态',
            'payment_name'  => '支付方式',
            'pay_message'   => '支付留言 ',
            // 'balance'       => '卖家当前余额',
            'type'          => '支付类型',
            'pay_message'   => '支付备注',
        );
    

}

?>
