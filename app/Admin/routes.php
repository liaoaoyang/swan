<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'SwanMessageController@index');
    $router->get('/system', 'HomeController@index');
    $router->get('/swan/users', 'SwanUserController@index');
    $router->get('/swan/messages', 'SwanMessageController@index');
    $router->post('/api/getWeChatUserInfo', 'SwanUserController@getWeChatUserInfo');

});


