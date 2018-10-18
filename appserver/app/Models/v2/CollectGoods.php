<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

use App\Helper\Token;

class CollectGoods extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'collect';
    public    $timestamps = false;


    public static function getList(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $model = self::where(['user_id' => $uid, 'type' => 'goods'])->with('goods')->orderBy('add_time', 'DESC');

        //paged
        $total = $model->count();
        $data  = $model->paginate($per_page)
               ->toArray();

        //format
        $goods = [];
        foreach ($data['data'] as $key => $value) {
            $goods[$key] = $data['data'][$key]['goods'];
        }

        return self::formatBody(['products' => $goods, 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getIsLiked($id, $type)
    {
        $uid = Token::authorization();
        if (self::where(['user_id' => $uid, 'item_id' => $id, 'type' => $type])->first())
        {
            return 1;
        }

        return 0;
    }

    public static function getWatchersNum($id, $type)
    {
        return self::where(['item_id' => $id, 'type' => $type])->count();
    }

    public static function setLike(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $num = CollectGoods::where(['user_id' => $uid, 'item_id' => $product, 'type' => 'goods'])->count();

        //因为有网站和手机 所以可能$num大于1
        if($num == 0){
            $model = new CollectGoods;
            $model->user_id             = $uid;
            $model->item_id             = $product;
            $model->type                = 'goods';
            $model->add_time            = time();

            if ($model->save()){
                return self::formatBody(['is_liked' => true ]);
            }else{
                return self::formatError(self::UNKNOWN_ERROR);
            }
        }elseif ($num > 0) {
            return self::formatBody(['is_liked' =>true ]);
        }

    }

    public static function setUnlike(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $model = CollectGoods::where(['user_id' => $uid, 'item_id' => $product, 'type' => 'goods']);
        $num = $model->count();
        $model->delete();
        
        if($model->count() == 0){
            return self::formatBody(['is_liked' =>false ]);
        }
    }

    public function goods()
    {
      return $this->hasOne('App\Models\v2\Goods', 'goods_id', 'item_id');
    }

}
