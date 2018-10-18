<?php
//
namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;

class UserAddress extends BaseModel
{
    protected $connection = 'shop';
    protected $table      = 'address';
    protected $primaryKey = 'addr_id';
    public    $timestamps = false;

    protected $appends = ['id', 'name', 'mobile', 'tel', 'zip_code', 'regions', 'is_default'];
    protected $visible = ['id', 'name', 'mobile', 'tel', 'zip_code', 'regions', 'address', 'is_default'];

    public static function getList()
    {
        $uid = Token::authorization();
        $data = UserAddress::where('user_id', $uid)->get()->toArray();
        return self::formatBody(['consignees' => $data]);
    }

    public static function get_consignee($consignee)
    {
        $uid = Token::authorization();
        $arr = array();
        if ($consignee) {
            return self::where('addr_id',$consignee)->first();
        }
        if ($uid > 0)
        {
            $arr = self::join('users','user_address.address_id', '=', 'users.address_id')
                    ->where('users.user_id',$uid)
                    ->first()->toArray();
        }

        return $arr;
    }

    public static function remove(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();
        UserAddress::where('addr_id', $consignee)->where('user_id', $uid)->delete();
        return self::formatBody();
    }

    public static function add(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();
        $region_name = Region::getRegionName($region);
        if($region_name === false){
            return self::formatError(self::BAD_REQUEST,trans('message.consignee.region'));
        }

        $model = new UserAddress;
        $model->user_id = $uid;
        $model->consignee = $name;
        $model->phone_mob = isset($mobile) ? $mobile : '';
        $model->phone_tel = isset($tel) ? $tel : '';
        $model->zipcode = isset($zip_code) ? $zip_code : '';
        $model->region_id = $region;
        $model->region_name = $region_name;
        $model->address = $address;

        if ($model->save()){
            return self::formatBody(['consignee' => $model->toArray()]);
        }

        return self::formatError(self::UNKNOWN_ERROR);

    }

    public static function modify(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        if ($model = UserAddress::where('addr_id', $consignee)->where('user_id', $uid)->first()) {

            $region_name = Region::getRegionName($region);
            if($region_name === false){
                return self::formatError(self::BAD_REQUEST,trans('message.consignee.region'));
            }

            $model->user_id = $uid;
            $model->consignee = $name;
            $model->phone_mob = isset($mobile) ? $mobile : '';
            $model->phone_tel = isset($tel) ? $tel : '';
            $model->zipcode = isset($zip_code) ? $zip_code : '';
            $model->region_id = $region;
            $model->region_name = $region_name;
            $model->address = $address;

            if ($model->save()){
                return self::formatBody(['consignee' => $model->toArray()]);
            }
        }

        return self::formatError(self::NOT_FOUND);

    }

    public static function setDefault(array $attributes)
    {
        return self::formatBody();
    }

    public function getIdAttribute()
    {
        return $this->attributes['addr_id'];
    }

    public function getNameAttribute()
    {
        return $this->attributes['consignee'];
    }    

    public function getMobileAttribute()
    {
        return $this->attributes['phone_mob'];
    }

    public function getTelAttribute()
    {
        return $this->attributes['phone_tel'];
    }


    public function getRegionsAttribute()
    {
        return Region::getRegionGroup($this->attributes['region_id']);
    }

    public function getZipCodeAttribute()
    {
        return $this->attributes['zipcode'];
    }

    public function getIsDefaultAttribute()
    {
        $uid = Token::authorization();
        $lastest_addr = self::where('user_id', $uid)->orderBy('addr_id', 'DESC')->value('addr_id');
        $flag = $this->attributes['addr_id'] == $lastest_addr ? true : false;
        return  $flag;
    }
}