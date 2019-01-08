<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Product;

class ProductController extends Controller{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 产品列表
     */
    public function index(){
        $data = Product::withTrashed()->orderBy('sort')->get();
        $assign = compact('data');
        return view('admin.product.index',$assign);
    }

    public function create(){

    }

    public function store(){

    }

    public function edit(){

    }

    public function update(){

    }

    public function destroy(){

    }

    public function restore(){

    }

    public function forceDelete(){

    }

}
