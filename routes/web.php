<?php
//use Tymon\JWTAuth\Contracts\Providers\JWT;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\Registrar as RouteContract;

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

//> Wechat 模块(小程序api)
Route::group(['namespace' => 'Wechat','prefix' => 'wechat'], function () {
    //> 微信小程序登陆
    Route::any('wxLogin', 'WechatController@wxLogin');
    //>保存用户详细信息
    Route::any('wxUser', 'WechatController@wxUser');
});

// auth
Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function () {
    // 第三方登录
    Route::group(['prefix' => 'oauth'], function () {
        // 重定向
        Route::get('redirectToProvider/{service}', 'OAuthController@redirectToProvider');
        // 获取用户资料并登录
        Route::get('handleProviderCallback/{service}', 'OAuthController@handleProviderCallback');
        // 退出登录
        Route::get('logout', 'OAuthController@logout');
    });
    // 后台登录 laravel 自带的登陆系统
    Route::group(['prefix' => 'admin'], function () {
        Route::post('login', 'LoginController@login');
    });
});

// 后台登录页面
Route::group(['namespace' => 'Admin','prefix' => 'admin'], function () {
    Route::group(['prefix' => 'login'], function () {
        // 登录页面
        Route::get('index', 'LoginController@index')->middleware('admin.login');
        // 退出
        Route::get('logout', 'LoginController@logout');
    });
});



// Admin 模块(后台页面路由)
Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => 'admin.auth'], function () {
    // 首页控制器
    Route::group(['prefix' => 'index'], function () {
        // 后台首页
        Route::get('index', 'IndexController@index');
        // 更新系统
        Route::get('upgrade', 'IndexController@upgrade');
    });

    // 文章管理
    Route::group(['prefix' => 'article'], function () {
        // 文章列表
        Route::get('index', 'ArticleController@index');
        // 发布文章
        Route::get('create', 'ArticleController@create');
        Route::post('store', 'ArticleController@store');
        // 编辑文章
        Route::get('edit/{id}', 'ArticleController@edit');
        Route::post('update/{id}', 'ArticleController@update');
        // 上传图片
        Route::post('uploadImage', 'ArticleController@uploadImage');
        // 删除文章
        Route::get('destroy/{id}', 'ArticleController@destroy');
        // 恢复删除的文章
        Route::get('restore/{id}', 'ArticleController@restore');
        // 彻底删除文章
        Route::get('forceDelete/{id}', 'ArticleController@forceDelete');
    });

    // 产品管理
    Route::group(['prefix' => 'product'], function () {
        // 产品列表
        Route::get('index', 'ProductController@index');
        // 发布文章
        Route::get('create', 'ProductController@create');
        Route::post('store', 'ProductController@store');
        // 编辑文章
        Route::get('edit/{id}', 'ProductController@edit');
        Route::post('update/{id}', 'ProductController@update');
        // 上传图片
//        Route::post('uploadImage', 'ProductController@uploadImage');
        // 删除文章
        Route::get('destroy/{id}', 'ProductController@destroy');
        // 恢复删除的文章
        Route::get('restore/{id}', 'ProductController@restore');
        // 彻底删除文章
        Route::get('forceDelete/{id}', 'ProductController@forceDelete');
    });

    // 分类管理
    Route::group(['prefix' => 'category'], function () {
        // 分类列表
        Route::get('index/{data_type}', 'CategoryController@index');
        // 添加分类
        Route::get('create/{data_type}', 'CategoryController@create');
        Route::post('store/{data_type}', 'CategoryController@store');
        // 编辑分类
        Route::get('edit/{id}', 'CategoryController@edit');
        Route::post('update/{id}', 'CategoryController@update');
        // 排序
        Route::post('sort', 'CategoryController@sort');
        // 删除分类
        Route::get('destroy/{id}', 'CategoryController@destroy');
        // 恢复删除的分类
        Route::get('restore/{id}', 'CategoryController@restore');
        // 彻底删除分类
        Route::get('forceDelete/{id}', 'CategoryController@forceDelete');
    });

    // 标签管理
    Route::group(['prefix' => 'tag'], function () {
        // 标签列表
        Route::get('index', 'TagController@index');
        // 添加标签
        Route::get('create', 'TagController@create');
        Route::post('store', 'TagController@store');
        // 编辑标签
        Route::get('edit/{id}', 'TagController@edit');
        Route::post('update/{id}', 'TagController@update');
        // 删除标签
        Route::get('destroy/{id}', 'TagController@destroy');
        // 恢复删除的标签
        Route::get('restore/{id}', 'TagController@restore');
        // 彻底删除标签
        Route::get('forceDelete/{id}', 'TagController@forceDelete');
    });

    // 评论管理
    Route::group(['prefix' => 'comment'], function () {
        // 评论列表
        Route::get('index', 'CommentController@index');
        // 删除评论
        Route::get('destroy/{id}', 'CommentController@destroy');
        // 恢复删除的评论
        Route::get('restore/{id}', 'CommentController@restore');
        // 彻底删除评论
        Route::get('forceDelete/{id}', 'CommentController@forceDelete');
    });

    // 管理员
    Route::group(['prefix' => 'user'], function () {
        // 管理员列表
        Route::get('index', 'UserController@index');
        // 编辑管理员
        Route::get('edit/{id}', 'UserController@edit');
        Route::post('update/{id}', 'UserController@update');
        // 删除管理员
        Route::get('destroy/{id}', 'UserController@destroy');
        // 恢复删除的管理员
        Route::get('restore/{id}', 'UserController@restore');
        // 彻底删除管理员
        Route::get('forceDelete/{id}', 'UserController@forceDelete');
    });

    // 第三方用户管理
    Route::group(['prefix' => 'oauthUser'], function () {
        // 用户列表
        Route::get('index', 'OauthUserController@index');
        // 编辑管理员
        Route::get('edit/{id}', 'OauthUserController@edit');
        Route::post('update/{id}', 'OauthUserController@update');
    });

    // 友情链接管理
    Route::group(['prefix' => 'friendshipLink'], function () {
        // 友情链接列表
        Route::get('index', 'FriendshipLinkController@index');
        // 添加友情链接
        Route::get('create', 'FriendshipLinkController@create');
        Route::post('store', 'FriendshipLinkController@store');
        // 编辑友情链接
        Route::get('edit/{id}', 'FriendshipLinkController@edit');
        Route::post('update/{id}', 'FriendshipLinkController@update');
        // 排序
        Route::post('sort', 'FriendshipLinkController@sort');
        // 删除友情链接
        Route::get('destroy/{id}', 'FriendshipLinkController@destroy');
        // 恢复删除的友情链接
        Route::get('restore/{id}', 'FriendshipLinkController@restore');
        // 彻底删除友情链接
        Route::get('forceDelete/{id}', 'FriendshipLinkController@forceDelete');
    });

    // 随言碎语管理
    Route::group(['prefix' => 'chat'], function () {
        // 随言碎语列表
        Route::get('index', 'ChatController@index');
        // 添加随言碎语
        Route::get('create', 'ChatController@create');
        Route::post('store', 'ChatController@store');
        // 编辑随言碎语
        Route::get('edit/{id}', 'ChatController@edit');
        Route::post('update/{id}', 'ChatController@update');
        // 删除随言碎语
        Route::get('destroy/{id}', 'ChatController@destroy');
        // 恢复删除的随言碎语
        Route::get('restore/{id}', 'ChatController@restore');
        // 彻底删除随言碎语
        Route::get('forceDelete/{id}', 'ChatController@forceDelete');
    });

    // 系统设置
    Route::group(['prefix' => 'config'], function () {
        // 编辑配置项
        Route::get('edit', 'ConfigController@edit');
        Route::post('update', 'ConfigController@update');
        // 清空各种缓存
        Route::get('clear', 'ConfigController@clear');
    });

    // 开源项目管理
    Route::group(['prefix' => 'gitProject'], function () {
        // 开源项目列表
        Route::get('index', 'GitProjectController@index');
        // 添加开源项目
        Route::get('create', 'GitProjectController@create');
        Route::post('store', 'GitProjectController@store');
        // 编辑开源项目
        Route::get('edit/{id}', 'GitProjectController@edit');
        Route::post('update/{id}', 'GitProjectController@update');
        // 排序
        Route::post('sort', 'GitProjectController@sort');
        // 删除开源项目
        Route::get('destroy/{id}', 'GitProjectController@destroy');
        // 恢复删除的开源项目
        Route::get('restore/{id}', 'GitProjectController@restore');
        // 彻底删除开源项目
        Route::get('forceDelete/{id}', 'GitProjectController@forceDelete');
    });
});



Route::any('/apis/{all}', 'ApiController@api');

Route::get('/',Api\ApiController::class."@index");

//Route::any('/', function(){
//    JWTAuth::setToken('foo.bar.baz');
//      dd(JWTAuth::getToken());
//    $user = User::first();
//    dd($user);
//    $token = JWTAuth::fromUser($user);
//    dd(JWTAuth::getToken());
    //qrcode("周盛吃" . htmlspecialchars_decode('<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>史大坨<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>'));
//    qrcode("http://www.shidatuos.cn/");
//     exit;

     //echo htmlspecialchars_decode('<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>史大坨<img class="emojione" alt="&#x1f4a9;" title=":poop:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f4a9.png"/>');
//});



/**
 * 简约生活小程序  +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

Route::group(['namespace' => 'Api','prefix' => 'api'], function () {
    Route::any('wxLogin', 'ApiController@wxLogin');
    Route::any('wxgetUser', 'ApiController@wxgetUser');
    Route::any('wxcreateUser', 'ApiController@wxcreateUser');
    Route::any('wxupImg', 'ApiController@wxupImg');
    Route::any('wxupSaleInfo', 'ApiController@wxupSaleInfo');
    Route::any('wxupSaleGoods', 'ApiController@wxupSaleGoods');
    Route::any('wxgetActive', 'ApiController@wxgetActive');
    Route::any('wxgetGoods', 'ApiController@wxgetGoods');
    Route::any('wxgetSaleInfo', 'ApiController@wxgetSaleInfo');
    Route::any('wxInsertBill', 'ApiController@wxInsertBill');
    Route::any('wxgetOrderList', 'ApiController@wxgetOrderList');
    Route::any('wxPurchaser', 'ApiController@wxPurchaser');

    Route::any('wxgetOrderDetails', 'ApiController@wxgetOrderDetails');
    Route::any('wxtakeOver', 'ApiController@wxtakeOver');
    Route::any('wxgetOActive', 'ApiController@wxgetOActive');

    Route::any('wxgetuserReceiv', 'ApiController@wxgetuserReceiv');
    Route::any('wxDeliver', 'ApiController@wxDeliver');
    Route::any('wxWallet', 'ApiController@wxWallet');
    Route::any('wxpayment', 'ApiController@wxpayment');
    Route::any('wxnotifyurl', 'ApiController@wxnotifyurl');
    Route::any('wxseeExpress', 'ApiController@wxseeExpress');
    Route::any('wxComplaint', 'ApiController@wxComplaint');
    Route::any('wxExpressList', 'ApiController@wxExpressList');
    Route::any('wxOrderDetaile', 'ApiController@wxOrderDetaile');
    Route::any('wxOnekeyfh', 'ApiController@wxOnekeyfh');
    Route::any('wxpaymentCallBack', 'ApiController@wxpaymentCallBack');
    Route::any('wxgetPhonenumber', 'ApiController@wxgetPhonenumber');

    Route::any('wxgetLaunchwithdraw', 'ApiController@wxgetLaunchwithdraw');
    Route::any('wxgetWithdrawList', 'ApiController@wxgetWithdrawList');

    Route::any('wxgetConfig', 'ApiController@wxgetConfig');
    Route::any('wxgetCarousel', 'ApiController@wxgetCarousel');

    Route::any('backLogin', 'ApiController@backLogin');
    Route::any('backSignOut', 'ApiController@backSignOut');

    Route::any('backgetSale', 'ApiController@backgetSale')->middleware('VerifyToken');
    Route::any('backgetSaleInfo', 'ApiController@backgetSaleInfo')->middleware('VerifyToken');
    Route::any('backupdataSale', 'ApiController@backupdataSale')->middleware('VerifyToken');

    Route::any('backadduser', 'ApiController@backadduser')->middleware('VerifyToken');
    Route::any('backaddjuese', 'ApiController@backaddjuese')->middleware('VerifyToken');
    Route::any('backupjuese', 'ApiController@backupjuese')->middleware('VerifyToken');
    Route::any('backCloseuser', 'ApiController@backCloseuser')->middleware('VerifyToken');

    Route::any('backgetuser', 'ApiController@backgetuser')->middleware('VerifyToken');
    Route::any('backgetjuese', 'ApiController@backgetjuese')->middleware('VerifyToken');
    Route::any('backgetUserlist', 'ApiController@backgetUserlist')->middleware('VerifyToken');
    Route::any('backgetUserinfo', 'ApiController@backgetUserinfo')->middleware('VerifyToken');
    Route::any('backgetUserOrderList', 'ApiController@backgetUserOrderList')->middleware('VerifyToken');
    Route::any('backgetOrderList', 'ApiController@backgetOrderList')->middleware('VerifyToken');
    Route::any('backgetOrderinfo', 'ApiController@backgetOrderinfo')->middleware('VerifyToken');
    Route::any('backgetcomplaintList', 'ApiController@backgetcomplaintList')->middleware('VerifyToken');
    Route::any('backgetcomplaintinfo', 'ApiController@backgetcomplaintinfo')->middleware('VerifyToken');
    Route::any('backresetPassword', 'ApiController@backresetPassword')->middleware('VerifyToken');
    Route::any('backgetIndexData', 'ApiController@backgetIndexData')->middleware('VerifyToken');
    Route::any('backgetuploadImg', 'ApiController@backgetuploadImg');
    //->middleware('VerifyToken')

    Route::any('backgetFit', 'ApiController@backgetFit')->middleware('VerifyToken');
    Route::any('backsetFit', 'ApiController@backsetFit')->middleware('VerifyToken');
    Route::any('backwithdrawList', 'ApiController@backwithdrawList')->middleware('VerifyToken');
    Route::any('backhandlewithdraw', 'ApiController@backhandlewithdraw')->middleware('VerifyToken');

    Route::any('backaddcarousel', 'ApiController@backaddcarousel')->middleware('VerifyToken');
    Route::any('backcarouselList', 'ApiController@backcarouselList')->middleware('VerifyToken');
    Route::any('backdeletecarousel', 'ApiController@backdeletecarousel')->middleware('VerifyToken');

    Route::any('backIncomedetails', 'ApiController@backIncomedetails')->middleware('VerifyToken');
    Route::any('backmodify', 'ApiController@backmodify')->middleware('VerifyToken');
    Route::any('closeuser', 'ApiController@closeuser')->middleware('VerifyToken');
    Route::any('getAccessToken', 'ApiController@getAccessToken');
    Route::any('wxgetQcodeGoodsInfo', 'ApiController@wxgetQcodeGoodsInfo');
});


Route::group(['prefix' => 'v2'], function (RouteContract $api) {


    $api->group(['middleware' => 'jwt.auth', 'providers' => 'jwt'], function (RouteContract $api) {
        // Refresh token
        $api->post('/tokens/{token}', TokenController::class.'@refresh');

        $api->get('/check_token', TokenController::class.'@check_token');
        $api->any('/{all}', array('as' => 'mp', 'uses' => 'ApiMpController@api'))->where('all', '.*');
//        $api->group(['middleware' => 'sees', 'providers' => 'jwt'], function (RouteContract $api) {
//            $api->any('/{all}', array('as' => 'mp', 'uses' => 'ApiMpController@api'))->where('all', '.*');
//        });


    });

});