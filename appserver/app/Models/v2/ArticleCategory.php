<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class ArticleCategory extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'acategory';
    public    $timestamps = false;

    protected $appends = ['id', 'title', 'link', 'created_at', 'updated_at', 'more'];
    protected $visible = ['id', 'link', 'title', 'created_at', 'updated_at', 'more'];

    public static function getNoticeCatId()
    {
        if ($cat = ArticleCategory::where('cate_name', '移动端公告')->value('cate_id'))
        {
            return $cat;
        } else {
            return ArticleCategory::where('cate_name', '商城公告')->value('cate_id');
        }
    }

    public static function getList(array $attributes)
    {
        extract($attributes);
        if(self::where('parent_id', $id)->where('cate_id', '<>', self::getNoticeCatId())->count() > 0){
            $model = self::where('parent_id', $id)->where('cate_id', '<>', self::getNoticeCatId());
        }else{
            $model = Article::where('cate_id', $id)->where('if_show', 1);
        }

        $total = $model->count();
        $data = $model->orderBy('sort_order', 'DESC')
            ->paginate($per_page)
            ->toArray();


        return self::formatBody(['articles' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public function getIdAttribute()
    {
        return $this->attributes['cate_id'];
    }

    public function getTitleAttribute()
    {
        return  $this->attributes['cate_name'];
    }
    public function getLinkAttribute()
    {
        return null;
    }

    public function getCreatedAtAttribute()
    {
        return time();
    }

    public function getUpdatedAtAttribute()
    {
        return time();
    }

    public function getMoreAttribute()
    {
        return true;
    }
}
