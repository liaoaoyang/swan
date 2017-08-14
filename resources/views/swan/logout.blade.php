<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">

    <link href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" rel="stylesheet" />
    <title>已清理会话</title>
</head>
<body>
<div class="container" id="container">
    <div class="page preview js_show">
        <div class="page__hd" style="padding: 40px;">
            <h1 class="page__title">已清理会话</h1>
            <p class="page__desc">重新访问会生成新的会话</p>
        </div>
        <div class="page__bd">
            <div class="weui-form-preview">
                <div class="weui-form-preview__ft">
                    <a class="weui-form-preview__btn weui-form-preview__btn_primary" href="{{ \App\Swan::MY_KEY_URL }}">查看我的推送KEY</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>