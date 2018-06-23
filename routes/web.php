<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Swan;

Route::get('/', function () {
    //return 'SWAN - Simple WeChat Alert Notifier';
    return view('swan/subscribe_first', [
        'html_title'           => 'SWAN - Simple WeChat Alert Notifier',
        'page_title'           => '关注接收消息',
        'subscribe_url'        => env('WECHAT_SUBSCRIBE_URL'),
        'subscribe_qrcode_url' => env('WECHAT_SUBSCRIBE_QRCODE_URL'),
    ]);
});

Route::any('/wechat', 'WeChatController@serve');
Route::any(Swan::OAUTH_BASE_CALLBACK_URL, 'WeChatController@swanOauthBaseScopeCallback');
Route::any(Swan::API_SEND_URL, 'WeChatController@send');
Route::any(Swan::API_ASYNC_SEND_URL, 'WeChatController@asyncSend');
Route::any(Swan::MY_KEY_URL, 'WeChatController@myKey');
Route::any(Swan::DETAIL_URL, 'WeChatController@detail');
Route::any('/wechat/swan/logout', 'WeChatController@logout');
Route::any('/wechat/swan/userinfo', 'WeChatController@userinfo');
Route::any('/wechat/swan/wxtauth', 'WeChatController@wxtauth');

