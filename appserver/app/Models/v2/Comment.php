<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;
use DB;

class Comment extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_goods';
    protected $primaryKey = 'rec_id';
    public    $timestamps = false;

    protected $appends = ['id', 'author', 'grade', 'content', 'is_anonymous', 'created_at', 'updated_at'];

    protected $visible = ['id', 'author', 'grade', 'content', 'is_anonymous', 'created_at', 'updated_at'];


    const BAD     = 1;            // 差评
    const MEDIUM  = 2;            // 中评
    const GOOD    = 3;            // 好评

    /**
    * 获取商品评论总数
    *
    * @access public
    * @param integer $goods_id
    * @return integer
    */
    public static function getCommentCountById($goods_id)
    {
         return self::where(['goods_id' => $goods_id, 'is_valid' => 1])->where('evaluation', '>', 0)->count();
    }

 

    public static function getReview(array $attributes)
    {
        extract($attributes);
        $model = self::where(['goods_id' => $product, 'is_valid' => 1])->orderBy('rec_id', 'DESC');;

        if (isset($grade) && is_numeric($grade)) {
            if ($grade == self::BAD) {
                $model->where('evaluation', 1);
            }elseif($grade == self::MEDIUM){
                $model->where('evaluation', 2);
            }elseif($grade == self::GOOD){
                $model->where('evaluation', 3);
            }else{
                $model->where('evaluation', '>', 0);
            }
        }


        $total = $model->count();

        $data = $model->paginate($per_page)->toArray();

        return self::formatBody(['reviews' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getSubtotal(array $attributes)
    {
        extract($attributes);

        $bad    = self::where(['goods_id' => $product, 'is_valid' => 1])->where('evaluation', 1)->count();
        $medium = self::where(['goods_id' => $product, 'is_valid' => 1])->where('evaluation', 2)->count();
        $good   = self::where(['goods_id' => $product, 'is_valid' => 1])->where('evaluation', 3)->count();
        $total  = self::where(['goods_id' => $product, 'is_valid' => 1])->where('evaluation', '>', 0)->count();

        return self::formatBody(['subtotal' => ['total' => $total ,'bad' => $bad, 'medium' => $medium, 'good' => $good]]);

    }


    //getter
    public function getIdAttribute()
    {
        return $this->attributes['rec_id'];
    }    
    public function getAuthorAttribute()
    {
        return Order::getBuyer($this->attributes['order_id']);
    }

    public function getGradeAttribute()
    {
        return $this->attributes['evaluation'];
    }

    public function getContentAttribute()
    {
        if(!empty(trim($this->attributes['comment']))){
            return $this->attributes['comment'];
        }

        return '很懒, 什么都没有评价!';
    }

    public function getIsAnonymousAttribute()
    {
        return Order::where('order_id', $this->attributes['order_id'])->value('anonymous');
    }

    public function getCreatedatAttribute()
    {
        return null;
    }

    public function getUpdatedatAttribute()
    {
        return null;
    }

}
