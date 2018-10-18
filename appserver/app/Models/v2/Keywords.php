<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use DB;

class Keywords extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'search_history';
    public    $timestamps = false;

    protected $appends = ['type', 'content'];
    protected $visible = ['type', 'content'];


	public static function getHot()
    {
        $data = self::orderBy('count', 'DESC')->limit(10)->get()->toArray();
        return self::formatBody(['keywords' => $data]);
    }

    public static function updateHistory($keyword, $type = 'goods', $store_id = 0)
    {
        if ($type == 'store' && !$store_id) {
            return false;
        }

        $keyword = strip_tags($keyword);
        $keyword = filterSpecialchar($keyword);
        if ($search_history = self::where('keyword', $keyword)->where('type', $type)->first()) {
            $search_history->count += 1;
            $search_history->type = $type;
            $search_history->updated = time();
            $search_history->save();
        } else {
            $search_history = new Keywords;
            $search_history->keyword = $keyword;
            $search_history->count = 1;
            $search_history->type = $type;
            $search_history->updated = time();
            $search_history->store_id = $store_id;  
            $search_history->save();
        }
    }

    public function getTypeAttribute()
    {
        if($this->attributes['type'] == 'goods')
        {
            return 1;
        }else if($this->attributes['type'] == 'store')
        {
            return 2;
        }
    }

    public function getContentAttribute()
    {
        return $this->attributes['keyword'];
    }
}
