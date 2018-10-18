<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class Invoice extends BaseModel
{


    public static function getTypeList()
    {
        // 暂时取的伪数据
        $data = [
            ['id'=>1, 'name' => '个人发票', 'tax' => '5％'],
            ['id'=>2, 'name' => '公司发票', 'tax' => '5％'],
            ['id'=>3, 'name' => '其它', 'tax' => '５％']
        ];
        return self::formatBody(['types' => $data]);
    }


    public static function getContentList()
    {
        $model = ['商品明细','办公用品','其它'];
        for($i = 0; $i < count($model); $i++){
            $data[$i]['id'] = $i + 1;
            $data[$i]['name'] = $model[$i];
        }
        return self::formatBody(['contents' => $data]);

    }

    public static function getStatus()
    {
        return self::formatBody(['is_provided' => 1]);
    }

    //with
    public function getIdAttribute()
    {
        return $this->attributes['invoice_no'];
    }

    public function getTaxAttribute()
    {
        return 100 * $this->attributes['tax'] . '%';
    }
}