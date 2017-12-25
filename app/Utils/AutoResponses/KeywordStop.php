<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:16
 */

namespace App\Utils\AutoResponses;

class KeywordStop extends DefaultResponse
{
    public function getResponse($weChatApp, $message)
    {
        $stopKeyUrlKeywords = explode(',', env('SWAN_WECHAT_AUTO_RESPONSE_KEYWORD_SEND_STOP','stop'));

        if (in_array($message->Content, $stopKeyUrlKeywords)) {
            return 'STOP';
        }

        return false;
    }
}