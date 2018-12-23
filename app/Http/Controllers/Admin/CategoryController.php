<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Http\Requests\Category\Store;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台分类列表
     * @link https://laravelacademy.org/post/138.html 查询软删除的模型数据
     */
    public function index(){
        //>调用 withTrashed 方法需要开启软删除
        $data = Category::withTrashed()->orderBy('sort')->get();
        $assign = compact('data');
        return view('admin.category.index',$assign);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 创建分类页面
     */
    public function create(){
        return view('admin.category.create');
    }

    /**
     * @param Store $req
     * @param Category $categoryModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 保存分类
     */
    public function store(Store $req,Category $categoryModel){
        //>过滤 _token 字段
        $data = $req->except('_token');
        $rs = $categoryModel->storeData($data);
        if($rs){
            //>删除缓存
            Cache::forget('common:category');
        }
        return redirect("admin/category/index");
    }


}
