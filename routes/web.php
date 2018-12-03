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

//> Wechat 模块
Route::group(['namespace' => 'Wechat','prefix' => 'wechat'], function () {
    //> 微信小程序登陆
    Route::any('wxLogin', 'WechatController@wxLogin');
    //>保存用户详细信息
    Route::any('wxUser', 'WechatController@wxUser');
});

// 后台登录页面
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::group(['prefix' => 'login'], function () {
        // 登录页面
        Route::get('index', 'LoginController@index')->middleware('admin.login');
        // 退出
        Route::get('logout', 'LoginController@logout');
    });
});


Route::any('/apis/{all}', 'ApiController@api');
Route::any('/', function(){
     echo htmlspecialchars_decode('<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>史大坨<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>');
});