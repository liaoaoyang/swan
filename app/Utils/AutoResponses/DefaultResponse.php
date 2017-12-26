<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/12/26
 * Time: 上午1:13
 */

namespace App\Utils\AutoResponses;

class DefaultResponse
{
    /**
     * @param $weChatApp
     * @param $message
     * @return string
     */
    public function getResponse($weChatApp, $message)
    {
        return '';
    }
}