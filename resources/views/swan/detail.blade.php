<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">

    <link href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" rel="stylesheet" />
    <title>{{ $text }}</title>
</head>
<body>
<div class="container" id="container">
    <div class="page preview js_show">
        <div class="page__hd" style="padding: 40px;">
            <h1 class="page__title">{{ $text }}</h1>
        </div>
        @if (isset($desp))
        <div class="page__bd">
            <article class="weui-article">
                {!! $desp !!}
            </article>
        </div>
        @endif
    </div>
</div>
</body>