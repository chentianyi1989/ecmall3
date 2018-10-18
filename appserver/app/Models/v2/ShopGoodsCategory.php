<?php
//
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helper\Helper;

class ShopGoodsCategory extends Model {

    protected $connection = 'shop';
    protected $table      = 'category_goods';
    public    $timestamps = false;
    
}
