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
                                               $keepSecondsWhenNotExist = 3600,
                                               $keepSecondsWhenUnknown = 3600,
                                               $keepSeconds = 86400)
    {
        $weChatApp = $weChatApp ? $weChatApp : new WeChatApplication(Swan::loadEasyWeChatConfig());

        $grid->column('wechat_nickanme', '用户昵称')->display(function () use ($weChatApp, $keepSecondsWhenNotExist, $keepSecondsWhenUnknown, $keepSeconds) {
            $openid = $this->openid;

            $info = Cache::store('redis')->tags([Swan::CACHE_TAG_WECHAT_USERINFO])->get($openid, false);

            if ($info !== false) {
                $info = json_decode($info, true);
                return $info['nickname'];
            }

            try {
                $user = $weChatApp->user->get($openid);

                if (!$user || !isset($user->nickname)) {
                    Cache::store('redis')->tags([Swan::CACHE_TAG_WECHAT_USERINFO])->put($openid, json_encode([
                        'nickname' => '用户不存在',
                    ], JSON_UNESCAPED_UNICODE), Carbon::now()->addSecond($keepSecondsWhenNotExist));
                    return '用户不存在';
                }
            } catch (\Exception $e) {
                Cache::store('redis')->tags([Swan::CACHE_TAG_WECHAT_USERINFO])->put($openid, json_encode([
                    'nickname' => '未知用户',
                ], JSON_UNESCAPED_UNICODE), Carbon::now()->addSecond($keepSecondsWhenUnknown));
                return '未知用户';
            }

            Cache::store('redis')->tags([Swan::CACHE_TAG_WECHAT_USERINFO])->put($openid, $user->toJson(), Carbon::now()->addSecond($keepSeconds));

            return $user->nickname;
        });
    }
}