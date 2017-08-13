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
    return 'SWAN - Simple WeChat Alert Notifier';
});

Route::any('/wechat', 'WeChatController@serve');
Route::any(Swan::OAUTH_BASE_CALLBACK_URL, 'WeChatController@swanOauthBaseScopeCallback');
Route::any(Swan::API_SEND_URL, 'WeChatController@send');
Route::any(Swan::MY_KEY_URL, 'WeChatController@myKey');
Route::any(Swan::DETAIL_URL, 'WeChatController@detail');
Route::any('/wechat/swan/userinfo', 'WeChatController@userinfo');
Route::any('/wechat/swan/logout', 'WeChatController@logout');
