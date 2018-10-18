<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

use App\Helper\Token;

class CollectShop extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'collect';
    public    $timestamps = false;


    public static function getList(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $model = self::where(['user_id' => $uid, 'type' => 'store'])->with('shop')->orderBy('add_time', 'DESC');

        //paged
        $total = $model->count();
        $data  = $model->paginate($per_page)
               ->toArray();

        //format
        $shop = [];
        foreach ($data['data'] as $key => $value) {
            $shop[$key] = $data['data'][$key]['shop'];
        }

        return self::formatBody(['shops' => $shop, 'paged' => self::formatPaged($page, $per_page, $total)]);
    }


    public static function setWatching(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $num = CollectGoods::where(['user_id' => $uid, 'item_id' => $shop, 'type' => 'store'])->count();

        //因为有网站和手机 所以可能$num大于1
        if($num == 0){
            $model = new CollectShop;
            $model->user_id             = $uid;
            $model->item_id             = $shop;
            $model->type                = 'store';
            $model->add_time            = time();

            if ($model->save()){
                return self::formatBody(['is_watching' => true ]);
            }else{
                return self::formatError(self::UNKNOWN_ERROR);
            }
        }elseif ($num > 0) {
            return self::formatBody(['is_watching' => true ]);
        }

    }

    public static function setUnwatching(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $model = CollectGoods::where(['user_id' => $uid, 'item_id' => $shop, 'type' => 'store']);
        $num = $model->count();
        $model->delete();
        
        if($model->count() == 0){
            return self::formatBody(['is_watching' =>false ]);
        }
    }

    public function shop()
    {
      return $this->hasOne('App\Models\v2\Shop', 'store_id', 'item_id');
    }

}
