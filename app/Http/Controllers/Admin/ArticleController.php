<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Article;
use App\Model\Category;

class ArticleController extends Controller{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 后台文章首页
     * @link https://laravel.com/docs/4.2/eloquent#eager-loading
     */
    public function index(){
        $article = Article::with('category')
            ->orderBy('created_at', 'desc')
            ->withTrashed()
            ->paginate(15);
        $assign = compact('article');
        return view('admin.article.index', $assign);
    }



    //发布文章
    public function create(){

    }


    //保存文章
    public function store(){

    }

    //修改文章页面
    public function edit(){

    }

    //修改文章
    public function update(){

    }

    //上传图片
    public function uploadImage(){

    }

    //删除文章
    public function destroy(){

    }

    //恢复文章
    public function restore(){

    }

    //彻底删除文章
    public function forceDelete(){

    }
}
