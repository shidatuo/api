<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::group(['namespace' => 'Api','prefix' => 'api'], function () {
//    Route::any('wxLogin', 'ApiController@wxLogin');
//});
//> Wechat 模块(小程序api)
//Route::group(['namespace' => 'Api'], function () {
//
//});
  //TokenController::class


//> Wechat 模块(小程序api)
//Route::group(['namespace' => 'Wechat','prefix' => 'wechat'], function () {
//    //> 微信小程序登陆
//    Route::any('wxLogin', 'WechatController@wxLogin');
//    //>保存用户详细信息
//    Route::any('wxUser', 'WechatController@wxUser');
//});



//Route::get('wxLogin', function(){
//    return response()->json(null,404);
//});
