<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

use App\Helper\Token;
use DB;

class Goods extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'goods';
    public    $timestamps = false;
    protected $primaryKey = 'goods_id';
    

    protected $appends = ['id', 'category', 'brand', 'tags', 'shop', 'sku', 'photos', 'name', 'price', 'current_price', 'promos', 'stock', 'properties', 'sales_count','score','good_stock','comment_count', 'is_liked', 'review_rate', 'intro_url', 'goods_desc', 'share_url', 'created_at', 'updated_at', 'default_photo'];
    protected $visible = ['id', 'category', 'brand', 'tags', 'shop', 'tags', 'photos','sku', 'name', 'price', 'current_price', 'promos', 'stock', 'properties', 'sales_count', 'attachments', 'goods_desc','score','comments', 'good_stock', 'comment_count', 'is_liked', 'review_rate', 'intro_url', 'share_url', 'created_at', 'updated_at', 'default_photo'];

    const NOSORT     = 0;
    const PRICE      = 1;
    const POPULAR    = 2;
    const CREDIT     = 3;
    const SALE       = 4;
    const DATE       = 5;

    public static function findOne($goods_id)
    {
        if ($model = Goods::where(['closed' => 0, 'if_show' => 1, 'goods_id' => $goods_id])->first()) {
            $model = $model->toArray();
            return $model;
        }
        
        return false;
    }

    /**
     * 商品列表
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public static function getList(array $attributes)
    {
        extract($attributes);
        $prefix = DB::connection('shop')->getTablePrefix();

        if (!empty($shop)) {
            //店铺内商品
            $model = Goods::where(['closed' => 0, 'if_show' => 1])
                ->where('store_id', $shop);

        } else {
            //全站商品
            $model = Goods::where(['closed' => 0, 'if_show' => 1]);
        }

        if (!empty($keyword)) {
            $keyword = trim($keyword);
            $keyword = strip_tags($keyword);
            $keyword = filterSpecialchar($keyword);
            $model->where(function ($query) use ($keyword) {
                // keywords  
                $query->where('goods_name', 'like', '%'.$keyword.'%')
                      ->orWhere('cate_name', 'like', '%'.$keyword.'%')
                      ->orWhere('tags', 'like', '%'.$keyword.'%')
                      ->orWhere('goods.goods_id', $keyword);
            });
            // 搜索历史
            Keywords::updateHistory($keyword);
        }

        if (!empty($brand)) {
            $model->where('brand', Brand::getBrandById($brand));
        }

        if (!empty($category)) {
            if(!empty($shop)){
                $model->join('category_goods', 'goods.goods_id', '=', 'category_goods.goods_id');
                $model->where(function ($query) use ($category) {
                    $query->whereIn('goods.cate_id', GoodsCategory::getCategoryIds($category))
                          ->orwhere('category_goods.cate_id', $category);
                });
            }else{
                $model->where(function ($query) use ($category) {
                    $query->whereIn('goods.cate_id', GoodsCategory::getCategoryIds($category))
                          ->orwhere('goods.cate_id_1', $category);
                });
            }
        }

        if (!empty($region_id)) {
            $model->whereIn('store_id', Shop::getShopIdsByRegion($region_id));
        }

        $model->join('goods_statistics', 'goods.goods_id', '=', 'goods_statistics.goods_id');
        $total = $model->count();

        if (isset($sort_key)) {

            switch ($sort_value) {
                case '1':
                    $sort = 'ASC';
                    break;
                
                case '2':
                    $sort = 'DESC';
                    break;

                default:
                    $sort = 'DESC';
                    break;
            }

        

            switch ($sort_key) {

                case self::NOSORT:
                    $model->orderBy('goods.goods_id', $sort);
                    break;

                case self::PRICE:
                    $model->orderBy('price', $sort);
                    break;

                case self::POPULAR:
                    $model->orderBy('goods_statistics.views', $sort);
                    break;

                case self::CREDIT:
                    // 按照好评率
                    $model->select('*', DB::connection('shop')->raw('(select count(evaluation) from '.$prefix.'order_goods a where evaluation = 3 and a.goods_id = '.$prefix.'goods.goods_id) /
                            (select count(evaluation) from '.$prefix.'order_goods b where evaluation > 0 and b.goods_id = '.$prefix.'goods.goods_id) as rate'))
                          ->orderBy('rate', $sort);
                    break;

                case self::SALE:
                    $model->orderBy('goods_statistics.sales', $sort);
                    break;

                case self::DATE:
                    $model->where('add_time', '>', time()-15*86400)->orderBy('add_time', $sort);
                    $total = $model->count();
                    break;

                default:
                    $model->orderBy('goods_statistics.views', 'DESC');
                    break;
            }
        } else {

            $model->orderBy('goods_statistics.views', 'DESC');
        }


        $data = $model->paginate($per_page)->toArray();

        return self::formatBody(['products' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    /**
     * 推荐商品列表
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public static function getRecommendList(array $attributes)
    {
        extract($attributes);

        if (!empty($shop)) {
            //店铺内推荐
            $model = Goods::where('recommended', 1)->where(['closed' => 0, 'if_show' => 1])->where('store_id', $shop);
        } else {
            //全站推荐
            $model = Goods::whereIn('goods.goods_id', RecommendGoods::getRecommendGoods())->where(['closed' => 0, 'if_show' => 1]);
        }

        $model->join('goods_statistics', 'goods.goods_id', '=', 'goods_statistics.goods_id')
              ->orderBy('goods_statistics.sales', 'DESC');

        $total = $model->count();

        $data = $model->paginate($per_page)->toArray();


        return self::formatBody(['products' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    /**
     * 商品配件列表
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public static function getAccessoryList(array $attributes)
    {
        extract($attributes);
        $total = 0;
        return self::formatBody(['products' => [],'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getInfo(array $attributes)
    {
        extract($attributes);
        if ($data = self::findOne($product)) {
            return self::formatBody(['product' => $data]);
        }
        return self::formatError(self::NOT_FOUND);
    }


    public static function getIntro($id)
    {
        
        if ($model = self::where('goods_id', $id)->first()) {
            $model->goods_desc = $model->description;
            return view('goods.intro', ['goods' => $model->toArray()]);
        }

        return self::formatError(self::NOT_FOUND);
    }

    public static function getShare($id)
    {
        //TODO
    }

    public static function purchase(array $attributes)
    {
        return Cart::_checkout($attributes, 'fastbuy');
    }

    public static function countShopProducts($id)
    {
        return Goods::where('store_id', $id)->where(['closed' => 0, 'if_show' => 1])->count();
    }

    public static function countShopNewProducts($id)
    {
        return Goods::where('store_id', $id)->where(['closed' => 0, 'if_show' => 1])->where('add_time', '>', time()-15*86400)->count();
    }

    public static function countShopHotCount($id)
    {
        // 热销商品按排序展示，所以应计算所有商品的数量
        return Goods::where('store_id', $id)->where(['closed' => 0, 'if_show' => 1])->count();
    }

    public static function checkStatus($id)
    {
        if ($model = Goods::where('goods_id', $id)->first()) {
            if ($model->closed == 0 && $model->if_show == 1) {
                return true;
            }
        }

        return false;
    }


    //with
    public function photos()
    {
        return $this->hasMany('App\Models\v2\GoodsGallery', 'goods_id', 'goods_id');
    }    

    public function stock()
    {
         return $this->hasMany('App\Models\v2\GoodsStock', 'goods_id', 'goods_id');
    }

    //getter
    public function getIdAttribute()
    {
        return $this->attributes['goods_id'];
    }

    public function getCategoryAttribute()
    {
        return $this->attributes['cate_id'];
    }

    public function getScoreAttribute()
    {
        return 0;
    }

    public function getBrandAttribute()
    {
        return Brand::getBrandByName($this->attributes['brand']);
    }

    public function getShopAttribute()
    {
        return $this->attributes['store_id'];
    }

    public function getSkuAttribute()
    {
        return null;
    }

    public function getNameAttribute()
    {
        return $this->attributes['goods_name'];
    }

    public function getGoodstockAttribute()
    {
        return GoodsProperty::where('goods_id', $this->attributes['goods_id'])->sum('stock');
    }

    public function getTagsAttribute()
    {
        $tags =[];
        $arr = explode(',', trim($this->attributes['tags'], ','));
        if(!empty($arr))
        {
            foreach ($arr as $key => $tag) {
                $tags[$key]['id'] = $key; 
                $tags[$key]['name'] = $key; 
                $tags[$key]['created_at'] = time(); 
                $tags[$key]['updated_at'] = time(); 
            }
        }
        return $tags;
    }

    public function getPriceAttribute()
    {
        return $this->attributes['price'];
    }
    public function getCurrentpriceAttribute()
    {
        return $this->attributes['price'];
    }

    public function getIslikedAttribute()
    {
        return CollectGoods::getIsLiked($this->goods_id, 'goods');
    }

    public function getPropertiesAttribute()
    {
        $properties = [];

        if(!empty($this->attributes['spec_name_1']) && $this->attributes['spec_qty'] > 0)
        {
            $properties [0]['id'] = 1;
            $properties [0]['is_multiselect'] = false;
            $properties [0]['name'] = $this->attributes['spec_name_1'];
            $properties [0]['attrs'] = GoodsProperty::getPropertiesOfSpec(1, $this->attributes['goods_id']);
        }        
        if(!empty($this->attributes['spec_name_2']) && $this->attributes['spec_qty'] == 2)
        {
            $properties [1]['id'] = 2;
            $properties [1]['is_multiselect'] = false;
            $properties [1]['name'] = $this->attributes['spec_name_2'];
            $properties [1]['attrs'] = GoodsProperty::getPropertiesOfSpec(2, $this->attributes['goods_id']);
        }

        return $properties;
    }

    public function getPromosAttribute()
    {
        return [];
    }

    public function getStockAttribute()
    {
        if(empty($this->attributes['spec_name_1']) && empty($this->attributes['spec_name_2'])){
             return null;
        }
        return GoodsStock::where('goods_id', $this->attributes['goods_id'])->get();
    }

    public function getSalescountAttribute()
    {
        return GoodsStatistics::getSales($this->attributes['goods_id']);
    }

    public function getCommentcountAttribute()
    {
        return Comment::getCommentCountById($this->attributes['goods_id']);
    }

    public function getPhotosAttribute()
    {
        return GoodsGallery::getPhotosById($this->attributes['goods_id']);
    }

    public function getGoodsDescAttribute()
    {
        return $this->attributes['description'];
    }

    public function getReviewrateAttribute()
    {
        return OrderGoods::getCommentRateById($this->attributes['goods_id']);
    }

    public function getIntrourlAttribute()
    {
        return url('/v2/product.intro.'.$this->attributes['goods_id']);
    }

    public function getShareUrlAttribute()
    {
        //  http://ecmall.geek-zoo.net/index.php?app=goods&id=35
        //  http://10.0.0.15:8000/src/#/product/?product=19
        return config('app.h5_url').'/#/product/?product='.$this->attributes['goods_id'];
    }

    public function getCreatedatAttribute()
    {
        return $this->attributes['add_time'];
    }
    public function getUpdatedatAttribute()
    {
        return $this->attributes['last_update'];
    }

    public function getDefaultPhotoAttribute()
    {
        return formatPhoto($this->default_image);
    }
}
