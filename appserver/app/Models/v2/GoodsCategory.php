<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class GoodsCategory extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'gcategory';
    public    $timestamps = false;
    protected $with = ['categories'];
    

    protected $visible = ['id','name','desc','photo','more','categories'];
    protected $appends = ['id','name','desc','photo','more','categories'];

    public static function getList(array $attributes)
    {
        extract($attributes);
        
          if (isset($shop) && $shop) {
            //店铺内分类
            $model = GoodsCategory::where('store_id', $shop)->where('if_show', 1);
        } else {
            //全站分类
            $model = GoodsCategory::where('store_id', 0)->where('if_show', 1);
        }

        if (isset($category) && $category) {
            //指定分类
            $model->where(function($query) use ($category){
                $query->where('cate_id', $category)->orWhere('parent_id', $category);
            });

        } else {
            $model->where('parent_id', 0);
        }

        if (isset($keyword) && $keyword) {
            $model->where(function ($query) use ($keyword) {
                 $query->where('cate_name', 'like', '%'.strip_tags($keyword).'%')->orWhere('cate_id', strip_tags($keyword));
            });
        }

        $total = $model->count();
        $data = $model
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->paginate($per_page)->toArray();

        return self::formatBody(['categories' => $data['data'],'paged' => self::formatPaged($page, $per_page, $total)]);

    }


    public static function getCategoryIds($id)
    {
        if($model = GoodsCategory::where('cate_id', $id)->where('if_show', 1)->orderBy('cate_id', 'ASC')->first())
        {
            $ids = GoodsCategory::where('parent_id', $id)->where('if_show', 1)->orderBy('cate_id', 'ASC')->lists('cate_id')->toArray();
            @array_push($ids, $model->cate_id);
            return $ids;
        }
        return [0];
       
    }

    private static function getParentCategories($parent_id)
    {
        $model = self::where('parent_id', $parent_id)->where('if_show', 1)->orderBy('cate_id', 'ASC')->get();
        if (!$model->isEmpty()) {
            return $model->toArray();
        }
    }


    public function getIdAttribute()
    {
        return $this->attributes['cate_id'];
    }
    public function getNameAttribute()
    {
        return $this->attributes['cate_name'];
    }
    public function getDescAttribute()
    {
        return null;
    }
    public function getPhotoAttribute()
    {
        
        $goods_images = Goods::where(['closed' => 0, 'if_show' => 1])->join('goods_statistics', 'goods.goods_id', '=', 'goods_statistics.goods_id')
                        ->whereIn('goods.cate_id', GoodsCategory::getCategoryIds($this->attributes['cate_id']))
                        ->orwhere('goods.cate_id_1', $this->attributes['cate_id'])->orderBy('goods_statistics.sales', 'DESC')->value('default_image');
        if(!empty($goods_images))
        {
            $photo = $goods_images;
        }else{
            $photo = url('/default/commodity.png');
        }

        return formatphoto($photo);
    }

    public function getCategoriesAttribute()
    {
        return self::where('parent_id', $this->cate_id)->where('if_show', 1)->orderBy('cate_id', 'ASC')->get();
    }

    public function getMoreAttribute()
    {
        return ($this->parent_id === 0) ? 1 : 0;
    }

    public function parentCategory()
    {
        return $this->belongsTo('App\Models\v2\GoodsCategory', 'parent_id', 'cate_id');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\v2\GoodsCategory', 'parent_id', 'cate_id');
    }

}
