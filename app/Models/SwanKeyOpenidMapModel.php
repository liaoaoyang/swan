<?php

namespace App\Models;

use App\Swan;

class SwanKeyOpenidMapModel implements MultiDrivers
{
    const RESPONSE_GET_KEY_FAILED_TO_SAVE = 'GET_KEY_FAILED_TO_SAVE';
    const RESPONSE_GET_KEY_NO_USER_INFO   = 'GET_KEY_NO_USER_INFO';
    const RESPONSE_GET_KEY_NOT_SUBSCRIBE  = 'GET_KEY_NOT_SUBSCRIBE';

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
}
