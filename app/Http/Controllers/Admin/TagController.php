<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\Store;
use App\Http\Requests\Tag\Update;
use App\Model\Tag;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller{

    /**
     * @param Tag $tagModel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 展示标签列表
     */
    public function index(Tag $tagModel){
        $data = $tagModel::withTrashed()->get();
        $assign = compact("data");
        return view('admin.tag.index',$assign);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 创建标签页面
     */
    public function create(){
        return view('admin.tag.create');
    }

    /**
     * @param Store $req
     * @param Tag $tagModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 添加标签
     */
    public function store(Store $req , Tag $tagModel){
        $data = $req->except('_token');
        $rs = $tagModel->storeData($data);
        if($rs){
            //>删除缓存
            Cache::forget('common:tag');
        }
        return redirect('admin/tag/index');
    }

    /**
     * @param $id
     * @param Tag $tagModel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 修改标签页面
     */
    public function edit($id , Tag $tagModel){
        $data = $tagModel::find($id);
        $assign = compact("data");
        return view('admin.tag.edit',$assign);
    }

    /**
     * @param $id
     * @param Update $req
     * @param Tag $tagModel
     * @return \Illuminate\Http\RedirectResponse
     * @author shidatuo
     * @description 修改标签
     */
    public function update($id , Update $req , Tag $tagModel){
        $data = $req->except('_token');
        $map = compact("id");
        $rs = $tagModel->updateData($map,$data);
        if($rs){
            //>删除缓存
            Cache::forget('common:tag');
        }
        return redirect('admin/tag/index');
    }

    /**
     * @param $id
     * @param Tag $tagModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 软删除标签
     */
    public function destroy($id , Tag $tagModel){
        $map = compact("id");
        $rs = $tagModel->destroyData($map);
        if($rs){
            //>删除缓存
            Cache::forget('common:tag');
        }
        return redirect()->back();
    }

    /**
     * @param $id
     * @param Tag $tagModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 恢复软删除标签
     */
    public function restore($id , Tag $tagModel){
        $map = compact("id");
        $rs = $tagModel->restoreData($map);
        if($rs){
            //>删除缓存
            Cache::forget('common:tag');
        }
        return redirect()->back();
    }

    /**
     * @param $id
     * @param Tag $tagModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 彻底删除
     */
    public function forceDelete($id , Tag $tagModel){
        $map = compact("id");
        $tagModel->forceDeleteData($map);
        return redirect()->back();
    }
}
