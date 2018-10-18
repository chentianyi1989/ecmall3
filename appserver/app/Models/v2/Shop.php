<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;

class Shop extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'store';
    public    $timestamps = false;
    protected $primaryKey = 'store_id';

    protected $appends = ['id', 'category', 'name', 'logo', 'banner', 'credit', 'keeper', 'address', 'desc', 'is_watching', 'watcher_count', 
                          'product_num', 'latest_product_num', 'best_sellers_num', 'created_at', 'updated_at'];
    protected $visible = ['id', 'category', 'name', 'logo', 'banner', 'credit', 'keeper', 'tel', 'address', 'desc', 'is_watching', 'watcher_count', 
                          'product_num', 'latest_product_num', 'best_sellers_num', 'created_at', 'updated_at'];

    const POPULAR = 2; // 人气
    const CREDIT  = 3; // 信用

    public static function getList(array $attributes)
    {
        extract($attributes);
        $model = Shop::where('state', 1);

        if (!empty($keyword)) {
            $keyword = trim($keyword);
            $keyword = strip_tags($keyword);
            $keyword = filterSpecialchar($keyword);
            $model->where(function ($query) use ($keyword) {
                // keywords  
                $query->where('store_name', 'like', '%'.$keyword.'%')
                      ->orWhere('store_id', $keyword);
            });
            
        }

        if (!empty($brand)) {
            $model->where('store_name', 'like', '%'.Brand::getBrandById($brand).'%');
        }

        if (!empty($category)) {
            $model->whereIn('store_id', ShopCategory::getShopByCatId($category));
        }

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

                case self::POPULAR:
                    $model->orderBy('praise_rate', $sort);
                    break;

                case self::CREDIT:
                    $model->orderBy('credit_value', $sort);
                    break;

                default:
                    $model->orderBy('praise_rate', 'DESC');
                    break;
            }
        } else {
            $model->orderBy('praise_rate', 'DESC');
        }

        $total = $model->count();
        $data = $model->paginate($per_page)->toArray();

        // 搜索历史
        if(!empty($data['data'][0]['id']) && !empty($keyword))
        {
            Keywords::updateHistory($keyword, 'store', $data['data'][0]['id']);
        }
        
        return self::formatBody(['shops' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getRecommand(array $attributes)
    {
        extract($attributes);
        $model = Shop::where('recommended', 1)->where('state', 1);

        $total = $model->count();

        $data = $model->orderBy('add_time', 'DESC')
            ->paginate($per_page)
            ->toArray();

         return self::formatBody(['shops' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }


    public static function getInfo(array $attributes)
    {
        extract($attributes);
        if ($data = self::where('store_id', $shop)->first()) {
            return self::formatBody(['shop' => $data->toArray()]);
        }
        return self::formatError(self::NOT_FOUND);
    }


    //getter
    public function getIdAttribute()
    {
        return $this->attributes['store_id'];
    }

    public function getCategoryAttribute()
    {
        return ShopCategory::getShopCatId($this->attributes['store_id']);
    }

    public function getNameAttribute()
    {
        return $this->attributes['store_name'];
    }

    public function getLogoAttribute()
    {
        return formatPhoto($this->attributes['store_logo'], null);
    }

    public function getBannerAttribute()
    {
        return formatPhoto($this->attributes['store_banner'], null);
    }

    public function getCreditAttribute()
    { 
        return ceil($this->attributes['praise_rate'] / 20);
    }

    public function getKeeperAttribute()
    {
        if($model = Member::where('user_id', $this->attributes['store_id'])->first()){
            return $model->toArray();
        }
        return null;
    }

    public function getAddressAttribute()
    {
        $str = preg_replace('/\t/', ' ', $this->attributes['region_name'].$this->attributes['address']);
        return $str ;
    }

    public function getDescAttribute()
    {
        return html_entity_decode(strip_tags($this->attributes['description']));
    }

    public function getIsWatchingAttribute()
    {
        return CollectGoods::getIsLiked($this->attributes['store_id'], 'store');
    }    

    public function getWatcherCountAttribute()
    {
        return CollectGoods::getWatchersNum($this->attributes['store_id'], 'store');
    }
    
    public function getProductNumAttribute()
    {
        return Goods::countShopProducts($this->attributes['store_id'], 'store');
    } 

    public function getLatestProductNumAttribute()
    {
        return Goods::countShopNewProducts($this->attributes['store_id'], 'store');
    }

    public function getBestSellersNumAttribute()
    {
        return Goods::countShopHotCount($this->attributes['store_id'], 'store');
    }    
       
    public function getCreatedAtAttribute()
    {
        return $this->attributes['add_time'];
    }    

    public function getUpdatedAtAttribute()
    {
        return $this->attributes['add_time'];
    }

    public static function getShopIdsByRegion($id)
    {
        $region_ids = Region::getRegionIds($id);

        return self::whereIn('region_id', $region_ids)->lists('store_id');
    }
}
