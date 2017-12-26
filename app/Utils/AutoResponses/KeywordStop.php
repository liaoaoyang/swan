<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:16
 */

namespace App\Utils\AutoResponses;

use App\Models\SwanKeyOpenidMapModel;

class KeywordStop extends DefaultResponse
{
    public function getResponse($weChatApp, $message)
    {
        $stopKeyUrlKeywords = explode(',', env('SWAN_WECHAT_AUTO_RESPONSE_KEYWORD_SEND_STOP', 'stop'));
        $stopKeyUrlKeywordsDecoded = [];

        foreach ($stopKeyUrlKeywords as $stopKeyUrlKeyword) {
            $stopKeyUrlKeywordsDecoded[] = urldecode($stopKeyUrlKeyword);
        }

        if (in_array($message->Content, $stopKeyUrlKeywordsDecoded)) {
            if (SwanKeyOpenidMapModel::disablePush($message->FromUserName, true)) {
                return trans('swan.auto_response.disable_success');
            } else {
                return trans('swan.auto_response.disable_failed');
            }
        }

        return false;
    }
}