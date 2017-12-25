<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:16
 */

namespace App\Utils\AutoResponses;

class KeywordStart extends DefaultResponse
{
    public function getResponse($weChatApp, $message)
    {
        $startKeyUrlKeywords = explode(',', env('SWAN_WECHAT_AUTO_RESPONSE_KEYWORD_SEND_START','start'));

        if (in_array($message->Content, $startKeyUrlKeywords)) {
            return 'START';
        }

        return false;
    }
}