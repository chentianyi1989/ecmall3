<?php
//
namespace App\Services\Cloud;

use App\Services\Cloud\Client;

class Sms {

    /**
     * Request SMS codes
     *
     * @param string $phoneNumber
     */
    public static function requestSmsCode($phoneNumber) {
        $res = Client::post("/requestSmsCode",
                        array("mobilePhoneNumber" => $phoneNumber, "ttl" => 30));

        if (!isset($res['error'])) {
            return true;
        }
        return false;

    }

    /**
     * Verify SMS codes
     *
     * @param string $phoneNumber
     * @param string $code
     */
    public static function verifySmsCode($phoneNumber, $code) {
        $res = Client::post("/verifySmsCode/{$code}?mobilePhoneNumber={$phoneNumber}", '');
        if (!isset($res['error'])) {
            return true;
        }
        return false;
    }

}

