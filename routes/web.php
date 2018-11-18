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
});

Route::any('/apis/{all}', 'ApiController@api');