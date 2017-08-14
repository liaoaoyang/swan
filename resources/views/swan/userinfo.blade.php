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
            <h1 class="page__title">我的用户信息</h1>
            <p class="page__desc">用于开发者调试，生产环境不可用</p>
        </div>
        <div class="page__bd">
            <div class="weui-form-preview">
                @foreach ($infos as $k => $v)
                <div class="weui-form-preview__bd">
                    <div class="weui-form-preview__item">
                        <label class="weui-form-preview__label">{{ $k }}</label>
                        <span class="weui-form-preview__value">{{ is_array($v) ? json_encode($v, JSON_PRETTY_PRINT) : $v }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
</body>