<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * @param OauthUser $oauthUserModel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台登陆页面
     */
    public function index()
    {
//        return 4444;
        // 获取是否有第三方用户被设置为管理员
//        $count = $oauthUserModel->where('is_admin', 1)->count();
//        // 如果有第三方账号管理员；则通过第三方账号登录
//        if ($count) {
//            die('请通过第三方账号登录');
//        } else {
//            return view('admin.login.index');
//        }
    }


}
