<?php
//
namespace App\Models\v2;
use App\Models\BaseModel;
use App\Helper\Header;

class Version extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'version';
    public  $timestamps   = true;


    protected $appends = ['download_url'];
    protected $visible = ['version', 'download_url', 'content'];

    public static function checkVersion()
    {
        $ver = Header::getVer();
        $platform = Header::getUserAgent('Platform');
        switch ($platform) {
            case 'ios':
                $platform = 1;
                break;
            case 'android':
                $platform = 2;
                break;
            default:
                $platform = 0;
                break;
        }

        if (isset($platform) && !empty($ver)) {
            $model = Version::where('platform',  $platform)->orderBy('version', 'DESC')->first();

            if(isset($model->version) && version_compare($ver, $model->version) < 0){
                return self::formatBody(['version_info' => $model]);
            }
        }

        return self::formatBody(['version_info' => null]);
    }

    public static function getLastestVersion()
    {
        $platform = Header::getUserAgent('Platform');
        switch ($platform) {
            case 'ios':
                $platform = 1;
                break;
            case 'android':
                $platform = 2;
                break;
            default:
                $platform = 0;
                break;
        }

        if (isset($platform)) {
            return  Version::where('platform',  $platform)->orderBy('version', 'DESC')->value('version');
        }

        return '0.0.0';
    }

    public function getDownloadUrlAttribute()
    {
        return $this->attributes['url'];
    }

}
