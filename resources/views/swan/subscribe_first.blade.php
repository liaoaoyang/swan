<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">

    <link href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" rel="stylesheet" />
    @if (isset($html_title))
        <title>{{ $html_title }}</title>
    @else
        <title>请先关注公众号</title>
    @endif
</head>
<body>
<div class="container" id="container">
    <div class="page preview js_show">
        <div class="page__hd" style="padding: 40px;">
            @if (isset($page_title))
                <h1 class="page__title">{{ $page_title }}</h1>
            @else
                <h1 class="page__title">请先关注公众号</h1>
            @endif
            <p class="page__desc">长按识别下方二维码</p>
        </div>
        <div class="page__bd">
            <div class="weui-form-preview__ft">
                <img src="{{ $subscribe_qrcode_url }}" style="width: 200px; height: 200px; margin: 40px auto 40px auto;">
            </div>
        </div>
    </div>
</div>
</body>