<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class Pay extends BaseModel {

    protected $connection = 'shop';

    protected $table      = 'payment';
    
    public    $timestamps = false;

    public static function checkConfig($store_id, $payment_code)
    {
    	if ($payment = self::where('payment_code', $payment_code)->where('enabled', '1')->first()) {
    		return $payment;
    	}
    	return false;
    }
}