<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台分类列表
     * @link https://laravelacademy.org/post/138.html 查询软删除的模型数据
     */
    public function index(){
        $data = Category::withTrashed()->orderBy('sort')->get();
        $assign = compact('data');
        return view('admin.category.index', $assign);
    }
}
