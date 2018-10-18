<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class Banner extends BaseModel {
    protected $connection = 'shop';
    protected $table      = 'banner';
    public    $timestamps = false;

   
    public static function getList()
    {
        $data = self::where('status', 1)->get()->toArray();
        return self::formatBody(['banners' => $data]);
    }

    public function getPhotoAttribute()
    {
        return formatPhoto($this->attributes['photo'], null);
    }

}
