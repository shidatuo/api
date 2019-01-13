<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;

class UserController extends Controller{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台用户管理
     */
    public function index(){
        $data = User::withTrashed()->get();
        $assgin = compact("data");
        return view('admin.user.index',$assgin);
    }
}
