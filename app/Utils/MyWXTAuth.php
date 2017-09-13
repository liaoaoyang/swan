<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/9/8
 * Time: 上午2:26
 */

namespace App\Utils;


use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class MyWXTAuth
{
    const CIPHER_MAX_KEY_LENGTH      = 16;
    const FLASH_WX_TAUTH_ONCE_SECRET = 'wx_tauth_once_time_secret';
    const FLASH_WX_TAUTH_BACK_URL    = 'wx_tauth_back_url';
    const RESPONSE_FLAG              = 'wx_tauth_response';
    const RESPONSE_DATA              = 'data';
    const SESSION_WX_USER_INFO       = 'wx_user_info';

    const RSA_DATA_SEPARATOR = '_';
    const RSA_1024           = 1024;
    const RSA_2048           = 2048;

    public static function getWXTAuthServerPublicKey()
    {
        $publicKey = env('WX_TAUTH_SERVER_PUBLIC_KEY', '');

        if (!$publicKey) {
            return false;
        }

        $publicKey = base64_decode($publicKey);

        if (!$publicKey) {
            return false;
        }

        return openssl_get_publickey($publicKey);
    }

    public static function getWXTAuthClientPrivateKeyByBid($bid)
    {
        $bid = strtoupper($bid);
        $privateKey = env("WX_TAUTH_CLIENT_{$bid}_PRIVATE_KEY", '');

        if (!$privateKey) {
            return false;
        }

        $privateKey = base64_decode($privateKey);

        if (!$privateKey) {
            return false;
        }

        return openssl_get_privatekey($privateKey);
    }

    public static function getAuthenticDomainByBid($bid)
    {
        $bid = strtoupper($bid);
        $authenticDomains = explode(',', env("WX_TAUTH_CLIENT_{$bid}_AUTHENTIC_DOMAINS", ''));

        return $authenticDomains;
    }

    public static function getWXTAuthClientPrivateKey()
    {
        $privateKey = env('WX_TAUTH_PRIVATE_KEY', '');

        if (!$privateKey) {
            return false;
        }

        $privateKey = base64_decode($privateKey);

        if (!$privateKey) {
            return false;
        }

        return openssl_get_privatekey($privateKey);
    }

    public static function RSAPartLength($length)
    {
        return intval($length / 8 - 11);
    }

    public static function RSAEncrypt($data, $keyLength = self::RSA_1024, $separator = self::RSA_DATA_SEPARATOR)
    {
        $publicKeyRes = self::getWXTAuthServerPublicKey();

        if (!$publicKeyRes) {
            return false;
        }

        $cryptData = [];
        $data = str_split($data, self::RSAPartLength($keyLength));

        foreach ($data as $d) {
            openssl_public_encrypt($d, $cd, $publicKeyRes);
            $cryptData[] = base64_encode($cd);
        }

        openssl_free_key($publicKeyRes);
        return join($separator, $cryptData);
    }

    public static function RSADecrypt($data, $separator = self::RSA_DATA_SEPARATOR)
    {
        $privateKeyRes = self::getWXTAuthClientPrivateKey();

        if (!$privateKeyRes) {
            return false;
        }

        $data = explode($separator, $data);
        $decryptData = [];

        foreach ($data as $d) {
            $d = base64_decode($d);
            openssl_private_decrypt($d, $dd, $privateKeyRes);
            $decryptData[] = $dd;
        }

        openssl_free_key($privateKeyRes);
        return join('', $decryptData);
    }

    public static function RSAAuthServerDecrypt($bid, $data, $base64 = true)
    {
        $privateKeyRes = self::getWXTAuthClientPrivateKeyByBid($bid);

        if ($base64) {
            $data = base64_decode($data);
        }

        if (!$privateKeyRes || !openssl_private_decrypt($data, $decryptData, $privateKeyRes)) {
            if ($privateKeyRes) {
                openssl_free_key($privateKeyRes);
            }
            return false;
        }

        openssl_free_key($privateKeyRes);
        return $decryptData;
    }

    public static function getOnceEncryptSecret()
    {
        return Session::get(self::FLASH_WX_TAUTH_ONCE_SECRET, false);
    }

    public static function getBackUrl()
    {
        return Session::get(self::FLASH_WX_TAUTH_BACK_URL, false);
    }

    public static function generateOnceEncryptSecret()
    {
        $oneTimeSecret = substr(base64_encode(openssl_random_pseudo_bytes(self::CIPHER_MAX_KEY_LENGTH)), 0, self::CIPHER_MAX_KEY_LENGTH);
        Session::flash(self::FLASH_WX_TAUTH_ONCE_SECRET, $oneTimeSecret);
        return $oneTimeSecret;
    }

    public static function encrypt($oneTimeSecret, $data)
    {
        $encoder = new \Illuminate\Encryption\Encrypter($oneTimeSecret);
        return $encoder->encrypt($data);
    }

    public static function decrypt($data)
    {
        $oneTimeSecret = Session::get(self::FLASH_WX_TAUTH_ONCE_SECRET);

        if (!$oneTimeSecret) {
            return false;
        }

        $encoder = new \Illuminate\Encryption\Encrypter($oneTimeSecret);
        return $encoder->decrypt($data);
    }

    public static function clearWXTAuthInfo()
    {
        Session::forget(self::FLASH_WX_TAUTH_ONCE_SECRET);
        Session::forget(self::FLASH_WX_TAUTH_BACK_URL);
    }

    public static function generateAuthUrl()
    {
        $tauthUrl = env('WX_TAUTH_URL', '');
        $bid = env('WX_TAUTH_BID', '');

        if (!$tauthUrl || !$bid) {
            return false;
        }

        $oneTimeSecret = self::generateOnceEncryptSecret();

        if (!$oneTimeSecret) {
            return false;
        }

        $oneTimeSecretAfterRSA = self::RSAEncrypt($oneTimeSecret);

        if (!$oneTimeSecretAfterRSA) {
            return false;
        }

        $tauthUrl = $tauthUrl . '?' . http_build_query([
                'url' => URL::current(),
                'bid' => $bid,
                'key' => $oneTimeSecretAfterRSA,
            ]);

        return $tauthUrl;
    }

    public static function handleAuthRequest()
    {
        $url = request('url', '');
        $bid = request('bid', '');
        $key = request('key', '');

        if (!$url || !$bid || !$key) {
            return false;
        }

        $urlParsed = parse_url($url);
        $urlDomains = self::getAuthenticDomainByBid($bid);

        if (!in_array($urlParsed['host'], $urlDomains)) {
            return false;
        }

        $oneTimeSecret = self::RSAAuthServerDecrypt($bid, $key);

        if (!$oneTimeSecret) {
            return false;
        }

        Session::flash(self::FLASH_WX_TAUTH_ONCE_SECRET, $oneTimeSecret);
        Session::flash(self::FLASH_WX_TAUTH_BACK_URL, $url);

        return true;
    }

    public static function generateResponseUrl($data)
    {
        $backUrl = self::getBackUrl();
        $oneTimeSecret = self::getOnceEncryptSecret();

        if (!$oneTimeSecret || !$backUrl) {
            return false;
        }

        $responseData = self::encrypt($oneTimeSecret, json_encode($data));

        if (!$responseData) {
            return false;
        }

        $backUrl = $backUrl . '?' . http_build_query([
                self::RESPONSE_FLAG => 1,
                self::RESPONSE_DATA => $responseData,
            ]);

        return $backUrl;
    }
}