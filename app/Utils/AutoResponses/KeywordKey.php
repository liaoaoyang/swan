<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:16
 */

namespace App\Utils\AutoResponses;

use App\Models\SwanKeyOpenidMapModel;

class KeywordKey extends DefaultResponse
{
    public function getResponse($weChatApp, $message)
    {
        $sendKeyUrlKeywords = explode(',', env('SWAN_WECHAT_AUTO_RESPONSE_KEYWORD_SEND_KEY','key'));

        if (in_array($message->Content, $sendKeyUrlKeywords)) {
            $keyObj = SwanKeyOpenidMapModel::getKey($weChatApp, $message->FromUserName);

            if (gettype($keyObj) === 'string') {
                return '';
            }

            return $keyObj->key;
        }

        return false;
    }
}