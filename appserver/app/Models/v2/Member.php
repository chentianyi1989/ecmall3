<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;
use App\Helper\Token;
use \DB;
use Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Services\Oauth\Wechat;
class Member extends BaseModel {

    const VENDOR_WEIXIN = 1;
    const VENDOR_WEIBO  = 2;
    const VENDOR_QQ     = 3;
    const VENDOR_TAOBAO = 4;

    const GENDER_SECRET = 0;
    const GENDER_MALE   = 1;
    const GENDER_FEMALE = 2;

    protected $connection = 'shop';
    protected $table      = 'member';
    protected $primaryKey = 'user_id';
    public    $timestamps = false;


    protected $appends = ['id','age','rank','username','nickname','mobile','avatar','mobile_binded','joined_at','is_auth', 'is_completed'];
    protected $visible = ['id','age','rank','gender','username','nickname','email','mobile','avatar','mobile_binded','joined_at','is_auth', 'is_completed'];

    // functions
    
    public static function login(array $attributes)
    {
        extract($attributes);

        //根据后台配置决定是否开启登录
        switch (self::getUsernameType($username)) {
            case 'mobile':
                if($res = Features::check('signin.mobile'))
                {
                    return $res;
                }
                break;
            case 'email':
            case 'username':
                if($res = Features::check('signin.default'))
                {
                    return $res;
                }
                break;

            default:
                break;
        }

        if ($model = self::validatePassword($username, $password)) {
            
            $token = Token::encode(['uid' => $model->user_id]);
            return self::formatBody(['token' => $token, 'user' => $model->toArray()]);
        }

        return self::formatError(self::BAD_REQUEST, trans('message.member.failed'));
    }


    private static function validatePassword($username, $password)
    {
        $model = self::where('user_name', $username)->orWhere('email', $username)->first();

        if ($model && $model->password == md5($password))
        {
            $model->last_login = time();
            $model->logins ++;
            $model->last_ip = Request::getClientIp();
            $model->save();
            return $model;
        }

            return false;
    }

    public static function updatePasswordByMobile(array $attributes)
    {
        extract($attributes);

        if ($model = Member::where('user_name', $mobile)->first()) {

                if (self::verifyCode($mobile, $code)) {
                    // update password
                    Member::where('user_id',$model->id)->where('user_name', $mobile)->update(['password' => md5($password)]);
                    return self::formatBody();

                } else {

                    return self::formatError(self::BAD_REQUEST, trans('message.member.mobile.code.error'));
                }

        } else {

            return self::formatError(self::NOT_FOUND);

        }
    }


    public static function verifyMobile(array $attributes)
    {
        extract($attributes);
        if ($model = Member::where('user_name', $mobile)->orWhere('phone_mob', $mobile)->first())
        {
            return self::formatError(self::BAD_REQUEST, trans('message.member.mobile.exists'));
        }

        return self::formatBody();
    }


    public static function resetPassword(array $attributes)
    {
        extract($attributes);

        if ($model = Member::where('email', $email)->first()){
            //Send mail
            $activation = str_random(40);
            $model->activation = md5($activation);

            if($model->save())
            {
                Mail::send('emails.reset',
                [
                    'username' => $model->user_name,
                    'sitename' => 'ECMall',
                    'link'     => config('app.shop_url').'/index.php?app=find_password&act=set_password&id='.$model->user_id.'&activation='.$activation
                ],
                function($message) use ($model)
                {
                  $message->to($model->email)
                      ->subject(trans('message.email.reset.subject'));
                });
            }

            return self::formatBody();
        }

        return self::formatError(self::NOT_FOUND);
    }


    public static function sendCode(array $attributes)
    {
        extract($attributes);
        $sms_channel = 'App\Services\\' . config('app.sms_channel') . "\Sms";
        $res = $sms_channel::requestSmsCode($mobile);

        if ($res === true) { // !isset($res['error'])
            return self::formatBody();
        }
        
        return self::formatError(self::BAD_REQUEST, trans('message.member.mobile.send.error'));
    }

    public static function auth(array $attributes)
    {
        extract($attributes);
        switch ($vendor) {
            case self::VENDOR_WEIXIN:
                if($res = Features::check('signin.weixin'))
                {
                    return $res;
                }
                $userinfo = self::getUserByWeixin($access_token, $open_id);
                break;

            case self::VENDOR_WEIBO:
                if($res = Features::check('signin.weibo'))
                {
                    return $res;
                }

                $userinfo = self::getUserByWeibo($access_token, $open_id);
                break;

            case self::VENDOR_QQ:

                if($res = Features::check('signin.qq'))
                {
                    return $res;
                }

                $userinfo = self::getUserByQQ($access_token, $open_id);
                break;

            case self::VENDOR_TAOBAO:
                return false;
                break;

            default:
                return false;
                break;
        }

        if (!$userinfo) {
            return self::formatError(self::BAD_REQUEST, trans('message.member.auth.error'));
        }

        if (!$user_id = self::checkBind($open_id)) {
            // create user
            $model = self::createAuthUser($vendor, $open_id, $userinfo['nickname'], $userinfo['gender'], $userinfo['prefix'], $userinfo['avatar']);

            if (!$model) {
                return self::formatError(self::BAD_REQUEST, trans('message.member.auth.error'));
            }

            $user_id = $model->user_id;

        }

        Device::toUpdateOrCreate($user_id, $attributes);
        // login
        return self::formatBody(['token' => Token::encode(['uid' => $user_id]), 'user' => Member::where('user_id', $user_id)->first()]);
    }


    private static function getUserByWeixin($access_token, $open_id)
    {
        $api = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}";
        $res = curl_request($api);
        if (isset($res['errcode'])) {
            return false;
        }

        return [
            'nickname' => $res['nickname'],
            'gender' => $res['sex'],
            'prefix' => 'wx',
            'avatar' => $res['headimgurl']
        ];
    }

    private static function getUserByWeibo($access_token, $open_id)
    {
        $api = "https://api.weibo.com/2/users/show.json?access_token={$access_token}&uid={$open_id}";
        $res = curl_request($api);
        if (isset($res['error_code'])) {
            return false;
        }

        return [
            'nickname' => $res['screen_name'],
            'gender' => ($res['gender'] == 'm') ? self::GENDER_MALE : (($res['gender'] == 'f') ? self::GENDER_MALE : self::GENDER_SECRET),
            'prefix' => 'wb',
            'avatar' => $res['avatar_large']
        ];
    }

    private static function getUserByQQ($access_token, $open_id)
    {
        if ($qq = Configs::where(['type' => 'oauth', 'code' => 'qq.app'])->first()) {
            $config = json_decode($qq->config, true);
            if (isset($config['app_id'])) {
                $api = "https://graph.qq.com/user/get_user_info?oauth_consumer_key={$config['app_id']}&access_token={$access_token}&openid={$open_id}&format=json";
                $res = curl_request($api);
                if (isset($res['ret']) && $res['ret'] != 0) {
                    return false;
                }

                return [
                    'nickname' => $res['nickname'],
                    'gender' => ($res['gender'] == '男' ? 1 : ($res['gender'] == '女' ? 2 : 0)),
                    'prefix' => 'qq',
                    'avatar' => $res['figureurl_qq_2']
                ];
            }
        }

        return false;

    }

    private static function createAuthUser($vendor, $open_id, $nickname, $gender, $prefix = 'ec', $avatar = '')
    {
        $username = self::genUsername($prefix);
 
        if (!Member::where('user_name', $username)->first())
        {
            $data = new Member;
            $data->user_name = $username;
            $data->real_name = $nickname;
            $data->email = "{$username}@sns.user";
            $data->password = md5(uniqid());
            $data->reg_time = time();
            $data->portrait = $avatar;
            $data->feed_config = '';
            
            if ($data->save())
            {
                $sns = new Sns;
                $sns->user_id = $data->user_id;
                $sns->open_id = $open_id;
                $sns->vendor  = $vendor;
                $sns->save();

                return $data;
            }

            return false;

        }
    }

    private static function genUsername($type)
    {
        return $type.'_'.time().rand(1000,9999);
    }

    public static function getUsernameType($username)
    {
        if (preg_match("/^\d{8,11}$/",$username)) {

            return 'mobile';

        } elseif (preg_match("/^\w+@\w+\.\w+$/",$username)) {

            return 'email';

        } else {

            return 'username';
        }
    }

    public static function createMemberByMobile(array $attributes)
    {
        //mobile
        extract($attributes);

        if (!Member::where('user_name', $mobile)->orWhere('phone_mob', $mobile)->first())
        {
            if (!self::verifyCode($mobile, $code)) {
                return self::formatError(self::BAD_REQUEST, trans('message.member.mobile.code.error'));
            }

            $data = new Member;
            $data->user_name = $mobile;
            $data->phone_mob = $mobile;
            $data->email = "{$mobile}@mobile.user";
            $data->password = md5($password);
            $data->reg_time = time();


            if ($data->save())
            {
                $model = self::where('user_name', $mobile)->where('phone_mob', $mobile)->first();
                
                Device::toUpdateOrCreate($model->id, $attributes);
                $token = Token::encode(['uid' => $model->id]);
                return self::formatBody(['token' => $token, 'user' => $model->toArray()]);

            }   else {

                return self::formatError(self::UNKNOWN_ERROR);

            }
        } else {

            return self::formatError(self::BAD_REQUEST, trans('message.member.exists'));

        }
    }

    public static function createMember(array $attributes)
    {
        //email
        extract($attributes);
        if (!Member::where('user_name', $username)->orWhere('email', $email)->first())
        {
            $data = new Member;
            $data->user_name = $username;
            $data->email = $email;
            $data->password = md5($password);
            $data->reg_time = time();
              
            if ($data->save())
            {   
                $model = self::where('user_name', $username)->where('email', $email)->first();

                Device::toUpdateOrCreate($model->id, $attributes);
                $token = Token::encode(['uid' => $model->id]);
                return self::formatBody(['token' => $token, 'user' => $model->toArray()]);

            }   else {

                return self::formatError(self::UNKNOWN_ERROR);

            }

        } else {

            return self::formatError(self::BAD_REQUEST, trans('message.member.exists'));

        }
    }


    public static function getMemberByToken()
    {
        $uid = Token::authorization();
        if ($model = self::where('user_id', $uid)->first())
        {
            return self::formatBody(['user' => $model->toArray()]);

        } else {

            return self::formatError(self::NOT_FOUND);

        }

    }


    public static function updateMember(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        if ($model = self::where('user_id', $uid)->first())
        {
            if (isset($gender)) {
                $model->gender = $gender;
            }

            if (isset($nickname)) {
                $model->real_name = strip_tags($nickname);
            }

            if(isset($avatar_url)){
                $model->portrait = $avatar_url;
            }
            
            if ($model->save())
            {
                return self::formatBody(['user' => $model->toArray()]);

            }   else {

                return self::formatError(self::UNKNOWN_ERROR);
            }

        } else {

            return self::formatError(self::NOT_FOUND);

        }
    }

    public static function updateAvatar(array $attributes)
    {
        extract($attributes);
        $uid = Token::authorization();

        if ($model = self::where('user_id', $uid)->first())
        {
            $path = base_path().'/public/avatar';
            $name = $uid.'.'.$avatar->getClientOriginalExtension();
            $avatar->move($path, $name);
            
            $model->portrait = url('/avatar/'.$name);
            
            if ($model->save())
            {
                return self::formatBody(['user' => $model->toArray()]);

            }   else {

                return self::formatError(self::UNKNOWN_ERROR);
            }

        } else {

            return self::formatError(self::NOT_FOUND);

        }
    }

    public static function updatePassword(array $attributes)
    {
        extract($attributes);

        $uid = Token::authorization();

        if ($model = Member::where('user_id', $uid)->first()) {
                if ($model->password == md5($old_password)) {
                    // update password
                    Member::where('user_id',$uid)->update(['password' => md5($password)]);
                    return self::formatBody();
                } else {
                    //old password error
                    return self::formatError(self::BAD_REQUEST, trans('message.member.password.old_password'));;
                }

        } else {

            return self::formatError(self::NOT_FOUND);

        }
    }

    public static function webOauth(array $attributes)
    {
        extract($attributes);

        switch ($vendor) {
            case self::VENDOR_WEIXIN:

                $oauth = Configs::where(['type' => 'oauth', 'status' => 1, 'code' => 'wechat.web'])->first();
                $config = Configs::verifyConfig(['app_id', 'app_secret'], $oauth);

                if (!$oauth || !$config) {
                    return self::formatError(self::BAD_REQUEST, trans('message.config.oauth.wechat'));
                }

                $wechat = new Wechat($config['app_id'], $config['app_secret']);
                return $wechat->getWeChatAuthorizeURL(url('/v2/ecapi.auth.web.callback/'.self::VENDOR_WEIXIN.'/?referer='.$referer.'&scope='.$scope),$scope);
                break;

            case self::VENDOR_WEIBO:
                return false;
                break;

            case self::VENDOR_QQ:
                return fasle;
                break;

            case self::VENDOR_TAOBAO:
                return false;
                break;

            default:
                return false;
                break;
        }
    }

    public static function webOauthCallback($vendor)
    {
        switch ($vendor) {
            case self::VENDOR_WEIXIN:

                $oauth = Configs::where(['type' => 'oauth', 'status' => 1, 'code' => 'wechat.web'])->first();

                $config = Configs::verifyConfig(['app_id', 'app_secret'], $oauth);                                                                

                if (!$oauth || !$config) {
                    return self::formatError(self::BAD_REQUEST, trans('message.config.oauth.wechat'));
                }


                $scope = isset($_GET['scope'])?$_GET['scope']:"";

                $wechat = new Wechat($config['app_id'], $config['app_secret']);

                if (!$access_token = $wechat->getAccessToken('code', isset($_GET['code']) ? $_GET['code'] : '')) {
                    Log::error('access_token: '.$wechat->error());
                    return self::formatError(self::BAD_REQUEST, trans('message.member.auth.error'));
                }
                $open_id = $wechat->getOpenid();
                if($scope == "snsapi_userinfo"){
                    $oauth_id = $wechat->getUnionid() ?: $open_id;
                    $userinfo = self::getUserByWeixin($access_token, $oauth_id);
                }                

                $platform = 'wechat';

                if($scope == "snsapi_userinfo"){
                    if (!$userinfo) {
                        return self::formatError(self::BAD_REQUEST, trans('message.member.auth.error'));
                    }

                    if (!$user_id = self::checkBind($oauth_id)) {
                        // create user
                        $model = self::createAuthUser($vendor, $oauth_id, $userinfo['nickname'], $userinfo['gender'], $userinfo['prefix'], $userinfo['avatar']);

                        if (!$model) {
                            return self::formatError(self::BAD_REQUEST, trans('message.member.auth.error'));
                        }

                        $user_id = $model->user_id;
                    }

                    $token = Token::encode(['uid' => $user_id]);

                    $key = "platform:{$user_id}";
                    Cache::put($key, $platform, 0);

                    return ['token' => $token, 'openid' => $open_id];
                }
                else{
                    return ['token' => "", 'openid' => $open_id];        
                }

                break;

            case self::VENDOR_WEIBO:
                return false;
                break;

            case self::VENDOR_QQ:
                return false;
                break;

            case self::VENDOR_TAOBAO:
                return false;
                break;

            default:
                return false;
                break;
        }
    }

    public static function getUserPayPoints()
    {
        return 0;
    }

    private static function verifyCode($mobile, $code)
    {
        $sms_channel = 'App\Services\\' . config('app.sms_channel') . "\Sms";
        $res = $sms_channel::verifySmsCode($mobile, $code);
        if ($res === true) { // !isset($res['error']
            return true;
        }
       return false;
    }

    private static function checkBind($open_id)
    {
        return Sns::where('open_id', $open_id)->pluck('user_id')->first();
    }

    // attributes
 
    public function getIdAttribute()
    {
        return $this->attributes['user_id'];
    }

    public function getAgeAttribute()
    {
        return null;
    }

    public function getRankAttribute()
    {
        return [
            'name' => '普通会员',
            'desc' => '',
            'score_min' => 0,
            'score_max' => 0
        ];
    }

    public function getUsernameAttribute()
    {
        return $this->attributes['user_name'];
    }

    public function getNicknameAttribute()
    {
        return $this->attributes['real_name'];
    }

    public function getMobileAttribute()
    {
        return $this->attributes['phone_mob'];
    }

    public function getAvatarAttribute()
    {
        if($avatar = $this->attributes['portrait'])
        {
            return formatPhoto($avatar, null, config('app.shop_url'));
        }

        return null;
    }

    public function getMobileBindedAttribute()
    {
        return false;
    }

    public function getJoinedAtAttribute()
    {
        return $this->attributes['reg_time'];
    }

    public function getIsAuthAttribute()
    {
        return (bool)strpos($this->attributes['email'], 'sns.user');
    }

    public function getIsCompletedAttribute()
    {
        return true;
    }

}
