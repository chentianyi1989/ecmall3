<?php
//

namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;
class Coupon extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'coupon';
    public    $timestamps = false;

    protected $appends = ['id', 'name', 'infos', 'start_at', 'end_at', 'condition', 'status'];
    protected $visible = ['id', 'name', 'infos', 'start_at', 'end_at', 'condition', 'status'];

    const AVAILABLE   = 0;        // 未过期
    const EXPIRED     = 1;        // 过期
    const USED        = 2;        // 已使用

    public static function checkCoupon($store_id, $user_id, $coupon, $amount)
    {
        $model = self::leftJoin('coupon_sn', 'coupon.coupon_id', '=', 'coupon_sn.coupon_id')
            ->leftJoin('user_coupon', 'coupon_sn.coupon_sn', '=', 'user_coupon.coupon_sn')
            ->where(['user_coupon.user_id' => $user_id, 'user_coupon.coupon_sn' => $coupon, 'coupon.store_id' => $store_id, 'if_issue' => 1])
            ->where('coupon.min_amount', '<=', $amount)
            ->where('coupon.start_time', '<', time())
            ->where('coupon.end_time', '>', time())
            ->where('coupon_sn.remain_times', '>', 0)
            ->first();

        if ($model) {
            return $model;
        }

        return false;
    }

    public static function getList(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        $model = self::leftJoin('coupon_sn', 'coupon.coupon_id', '=', 'coupon_sn.coupon_id')
                ->leftJoin('user_coupon', 'coupon_sn.coupon_sn', '=', 'user_coupon.coupon_sn')
                ->where(['user_coupon.user_id' => $uid, 'if_issue' => 1]);

        switch ($status) {
            case self::AVAILABLE:
                $model->where('coupon_sn.remain_times', '>', 0)
                      ->where('coupon.start_time', '<', time())
                      ->where('coupon.end_time', '>', time());
                break;
            case self::EXPIRED:
                $model->where('coupon_sn.remain_times', '>', 0)
                      ->where('coupon.end_time', '<', time());
                break;  
            case self::USED:
                $model->where('coupon_sn.remain_times', 0);
                break;
            default:
                break;
        }
        $total = $model->count();
        $data = $model->paginate($per_page)
              ->toArray();

        return self::formatBody(['coupons' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    public static function getAvailable(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        $model = self::leftJoin('coupon_sn', 'coupon.coupon_id', '=', 'coupon_sn.coupon_id')
                ->leftJoin('user_coupon', 'coupon_sn.coupon_sn', '=', 'user_coupon.coupon_sn')
                ->where(['user_coupon.user_id' => $uid, 'if_issue' => 1])
                ->where('coupon_sn.remain_times', '>', 0)
                ->where('coupon.start_time', '<', time())
                ->where('coupon.end_time', '>', time())
                ->where('coupon.min_amount', '<', $total_price)
                ->where('coupon.store_id', $shop);
        $total = $model->count();
        $data = $model->paginate($per_page)
              ->toArray();

        return self::formatBody(['coupons' => $data['data'], 'paged' => self::formatPaged($page, $per_page, $total)]);
    }


    //getter
    public function getIdAttribute()
    {
        return $this->attributes['coupon_sn'];
    }

    public function getNameAttribute()
    {
        return $this->attributes['coupon_name'];
    }

    public function getInfosAttribute()
    {
        return [];
    }

    public function getStartAtAttribute()
    {
        return $this->attributes['start_time'];
    }

    public function getEndAtAttribute()
    {
        return $this->attributes['end_time'];
    }

    public function getConditionAttribute()
    {
        return $this->attributes['min_amount'];
    }

    public function getStatusAttribute()
    {
        if($this->attributes['remain_times'] == 0){
            return self::USED;
        }

        if(time() > $this->attributes['end_time'])
        {
            return self::EXPIRED;
        }else{
            return self::AVAILABLE;
        }
    }


}
