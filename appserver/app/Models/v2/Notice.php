<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;

class Notice extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'article';
    public    $timestamps = false;

    protected $appends = ['id', 'title', 'url', 'created_at', 'updated_at'];
    protected $visible = ['id', 'title', 'url', 'created_at', 'updated_at'];


    public static function getList($attributes)
    {
        extract($attributes);
        $model = Notice::where('cate_id', ArticleCategory::getNoticeCatId())
                ->where('if_show', 1);

        $total = $model->count();

        $data = $model
            ->orderBy('sort_order', 'ASC')
            ->orderBy('add_time', 'DESC')
            ->paginate($per_page)
            ->toArray();

        return self::formatBody(['notices' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getNotice($id)
     {
         if ($model = Notice::where('article_id', $id)->first()) {
             $data['content'] = $model->content;
             return view('notice.mobile', ['notice' => $data]);
         }
     }

     public function getIdAttribute()
     {
         return $this->attributes['article_id'];
     }

     public function getTitleAttribute()
     {
         return $this->attributes['title'];
     }

     public function getUrlAttribute()
     {
         return url('/v2/notice.'.$this->attributes['article_id']);
     }

     public function getCreatedAtAttribute()
     {
         return $this->attributes['add_time'];
     }

     public function getUpdatedAtAttribute()
     {
         return $this->attributes['add_time'];
     }
}
