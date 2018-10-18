<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;

class OrderExtm extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_extm';
    public    $timestamps = false;

}
