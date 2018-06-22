<?php

namespace App\Models;

use App\Swan;

class SwanKeyOpenidMapModel implements MultiDrivers
{
    const RESPONSE_GET_KEY_FAILED_TO_SAVE = 'GET_KEY_FAILED_TO_SAVE';
    const RESPONSE_GET_KEY_NO_USER_INFO   = 'GET_KEY_NO_USER_INFO';
    const RESPONSE_GET_KEY_NOT_SUBSCRIBE  = 'GET_KEY_NOT_SUBSCRIBE';

    const STATUS_ENABLED          = 0;
    const STATUS_DISABLED         = 1;
    const STATUS_DISABLED_BY_USER = 2;

    /**
     * @return SwanKeyOpenidMapMongoModel|SwanKeyOpenidMapMySQLModel|null
     */
    public static function createModel()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ('mysql' == $dbConnection) {
            return new \App\Models\SwanKeyOpenidMapMySQLModel();
        } else if ('mongodb' == $dbConnection) {
            return new \App\Models\SwanKeyOpenidMapMongoModel();
        }

        return null;
    }

    /**
     * @param \EasyWeChat\Foundation\Application $weChatApp
     * @param string $openid
     * @return SwanKeyOpenidMapMongoModel|SwanKeyOpenidMapMySQLModel|\Illuminate\Database\Eloquent\Model|null|string|static
     */
    public static function getKey($weChatApp, $openid)
    {
        $swanKeyOpenidMapModel = self::createModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['openid' => $openid])->first();

        if (!$swanKeyOpenidMap) {
            $swanKeyOpenidMap = SwanKeyOpenidMapModel::createModel();
            $swanKeyOpenidMap->key = Swan::generatePushKey();
            $swanKeyOpenidMap->openid = $openid;

            if (!$swanKeyOpenidMap->save()) {
                return self::RESPONSE_GET_KEY_FAILED_TO_SAVE;
            }
        }

        $weChatUserInfo = $weChatApp->user->get($openid);

        if (!$weChatUserInfo) {
            return self::RESPONSE_GET_KEY_NO_USER_INFO;
        }

        if (!$weChatUserInfo->subscribe) {
            return self::RESPONSE_GET_KEY_NOT_SUBSCRIBE;
        }

        return $swanKeyOpenidMap;
    }

    public static function isPushEnabledWithKey($key)
    {
        $swanKeyOpenidMapModel = self::createModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['key' => $key])->first();

        if (!$swanKeyOpenidMap) {
            return false;
        }

        return $swanKeyOpenidMap->status == self::STATUS_ENABLED;
    }

    public static function enablePush($openid)
    {
        $swanKeyOpenidMapModel = self::createModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['openid' => $openid])->first();

        if (!$swanKeyOpenidMap) {
            return false;
        }

        $swanKeyOpenidMap->status = self::STATUS_ENABLED;

        if (!$swanKeyOpenidMap->save()) {
            return false;
        }

        return true;
    }

    public static function disablePush($openid, $byUser = false)
    {
        $swanKeyOpenidMapModel = self::createModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['openid' => $openid])->first();

        if (!$swanKeyOpenidMap) {
            return false;
        }

        $swanKeyOpenidMap->status = $byUser ?
            self::STATUS_DISABLED_BY_USER :
            self::STATUS_DISABLED;

        if (!$swanKeyOpenidMap->save()) {
            return false;
        }

        return true;
    }
}
