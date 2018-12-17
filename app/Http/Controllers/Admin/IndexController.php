<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台首页
     */
    public function index(Request $request){
        dump(56765766);
//        Session::flush();
//
//
//        dump(Session::getId());
//// 增加一个会话键值数据
//        Session::put('key', 'value');
//// 将一个值加入到 session 的数组中
//        Session::push('foo.bar','value');
//// 返回 session 的所有条目
//        Session::all();
//// 检查 session 里是否有此条目
//        Session::has('key');
//// 从 session 中移除一个条目
//        Session::forget('key');
//// 从 session 中移除所有条目
//        Session::flush();

//        session_unset()
//        $request->session()->flush();

//        dump(Auth::user());
//        dump(session('user.is_admin'));
//        dd($request->session()->all());

        // 文章总数
        $articleCount = 0;
        // 评论总数
        $commentCount = 0;
        // 随言碎语总数
        $chatCount = 0;
        // 用户总数
        $oauthUserCount = 0;
        // 最新登录小程序的5个用户
        $oauthUserData = [];
//        $oauthUserData = OauthUser::select('name', 'avatar', 'login_times', 'updated_at')
//            ->orderBy('updated_at', 'desc')
//            ->limit(5)
//            ->get();
        // 最新的5条评论
        $commentData = 0;
        $version = [
            "system"    => PHP_OS, //操作系统
            "webServer" => $_SERVER['SERVER_SOFTWARE'], //环境
            "php"       => PHP_VERSION, //获取php版本
            "mysql"     => DB::select('SHOW VARIABLES LIKE "version"')[0]->Value, //获取mysql版本
        ];
        $assign = compact("articleCount","commentCount","chatCount","oauthUserCount","oauthUserData","commentData","version");

        dd($assign);
        return view('admin.index.index',$assign);
    }
}
