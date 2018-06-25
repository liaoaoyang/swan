<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2018/6/24
 * Time: 上午1:09
 */

namespace App\Admin\Controllers;

use Carbon\Carbon;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;
use Cache;

trait SwanColumns
{
    protected function addColumnWeChatNickname(&$grid,
                                               $weChatApp = null,
                                               $keepSecondsWhenNotExist = 86400,
                                               $keepSecondsWhenUnknown = 3600,
                                               $keepSeconds = 86400)
    {
        $context = $this;
        $weChatApp = $weChatApp ? $weChatApp : new WeChatApplication(Swan::loadEasyWeChatConfig());

        $grid->column('wechat_nickanme', '用户昵称')->display(function () use ($context, $weChatApp, $keepSecondsWhenNotExist, $keepSecondsWhenUnknown, $keepSeconds) {
            $openid = $this->openid;
            $userNotExistText = '用户不存在';
            $userUnknownText = '未知用户';

            $info = Cache::store('redis')->tags([Swan::CACHE_TAG_WECHAT_USERINFO])->get($openid, false);

            if ($info !== false) {
                $info = json_decode($info, true);
                return $info['nickname'];
            }

            try {
                $user = $weChatApp->user->get($openid);

                if (!$user || !isset($user->nickname)) {
                    $context->saveUserInfoInCache($openid, ['nickname' => $userNotExistText,], $keepSecondsWhenNotExist);
                    return $userNotExistText;
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 40003) {
                    $context->saveUserInfoInCache($openid, ['nickname' => $userNotExistText,], $keepSecondsWhenNotExist);
                    return $userNotExistText;
                }

                $context->saveUserInfoInCache($openid, ['nickname' => $userUnknownText,], $keepSecondsWhenUnknown);
                return $userUnknownText;
            }

            $context->saveUserInfoInCache($openid, $user->toJson(), $keepSeconds, false);

            return $user->nickname;
        });
    }

    protected function saveUserInfoInCache($openid, $data, $timeout, $encodeData = true)
    {
        $data = $encodeData && is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        Cache::store('redis')
             ->tags([Swan::CACHE_TAG_WECHAT_USERINFO])
             ->put($openid, $data, Carbon::now()->addSecond($timeout));
    }
}