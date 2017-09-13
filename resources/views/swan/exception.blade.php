<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">

    <link href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" rel="stylesheet" />
    <title>{{ isset($title) ? $title : '异常问题' }}</title>
</head>
<body>
<div class="container" id="container">
    <div class="page preview js_show">
        <div class="page__hd" style="padding: 40px;">
            <div class="weui-msg">
                <div class="weui-msg__icon-area"><i class="weui-icon-warn weui-icon_msg"></i></div>
                <div class="weui-msg__text-area">
                    <h2 class="weui-msg__title">{{ isset($exception['title']) ? $exception['title'] : '异常问题' }}</h2>
                    <p class="weui-msg__desc">{{ isset($exception['desc']) ? $exception['desc'] : '请重试当前操作' }}</p>
                </div>
                <div class="weui-msg__opr-area">
                    <p class="weui-btn-area">
                        {{--<a href="javascript:window.close();" class="weui-btn weui-btn_primary">推荐操作</a>--}}
                    </p>
                </div>
                <div class="weui-msg__extra-area">
                    <div class="weui-footer">
                        <p class="weui-footer__links">
                            @if (config('app.url', ''))
                            <a href="{{ config('app.url', '') }}" class="weui-footer__link">{{ config('app.name', 'SWAN') }}</a>
                            @endif
                        </p>
                        <p class="weui-footer__text">Copyright © {{ date('Y') }} {{ config('app.url', '') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>