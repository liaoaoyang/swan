<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:16
 */

namespace App\Utils\AutoResponses;

use App\Models\SwanKeyOpenidMapModel;

class KeywordStart extends DefaultResponse
{
    public function getResponse($weChatApp, $message)
    {
        $startKeyUrlKeywords = explode(',', env('SWAN_WECHAT_AUTO_RESPONSE_KEYWORD_SEND_START','start'));
        $startKeyUrlKeywordsDecoded = [];

        foreach ($startKeyUrlKeywords as $startKeyUrlKeyword) {
            $startKeyUrlKeywordsDecoded[] = urldecode($startKeyUrlKeyword);
        }

        if (in_array($message->Content, $startKeyUrlKeywordsDecoded)) {
            if (SwanKeyOpenidMapModel::enablePush($message->FromUserName)) {
                return trans('swan.auto_response.enable_success');
            } else {
                return trans('swan.auto_response.enable_failed');
            }
        }

        return false;
    }
}