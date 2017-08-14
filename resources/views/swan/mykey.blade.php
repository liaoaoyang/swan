<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">

    <link href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" rel="stylesheet" />
    <title>我的推送Key</title>
</head>
<body>
<div class="container" id="container">
    <div class="page preview js_show">
        <div class="page__hd" style="padding: 40px;">
            <h1 class="page__title">我的推送Key</h1>
            <p class="page__desc">用于send API</p>
        </div>
        <div class="page__bd">
            <div class="weui-form-preview">
                <div class="weui-form-preview__hd">
                    <div class="weui-form-preview__item">
                        <label class="weui-form-preview__label">KEY</label>
                        <em class="weui-form-preview__value" style="font-size: 1.2em;">{{ $key }}</em>
                    </div>
                </div>
                <div class="weui-form-preview__bd">
                    <div class="weui-form-preview__item">
                        <label class="weui-form-preview__label">更新时间</label>
                        <span class="weui-form-preview__value">{{ $updated_at }}</span>
                    </div>
                </div>
                @if (isset($retry_url))
                <div class="weui-form-preview__ft">
                    <a class="weui-form-preview__btn weui-form-preview__btn_primary" href="{{ $retry_url }}">重试</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</body>