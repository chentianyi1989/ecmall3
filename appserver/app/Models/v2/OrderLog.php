<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;

class OrderLog extends BaseModel {
    protected $connection = 'shop';
    protected $table      = 'order_log';
    public    $timestamps = false;
    protected $primaryKey = 'log_id';

    public static function addLog($order_id, $operator, $order_status, $changed_status, $remark)
    {
        $model = new OrderLog();
        $model->order_id = $order_id;
        $model->operator = $operator;
        $model->order_status = $order_status;
        $model->changed_status = $changed_status;
        $model->remark = $remark;
        $model->log_time = time();
        $model->save();
    }

    public static function getLogTime($order_id, $changed_status)
    {
        return OrderLog::where(['order_id' => $order_id, 'changed_status' => $changed_status])->value('log_time');
    }

}
