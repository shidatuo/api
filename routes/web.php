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

Route::any('/apis/{all}', 'ApiController@api');
Route::any('/', function(){

//    $aa = new \App\Model\User();
//    $aa->nickName = 'shidatuo';
//    dd($aa->i());

//    dump(Schema::hasColumn('users', 'email'));
//    dd(DB::table("app")->get());
//
//    Schema::table("users", function (Blueprint $table) {
//
//        dd($table->hasColumn('user_ip'));
//
//        if ($table->hasColumn('user_ip')) {
//            //> 用户表里存在 user_ip
//            $data['user_ip'] = USER_IP;
//        }
//    });
});