<?php

namespace App\Http\Controllers\Admin;

use App\Model\Config;
use App\Http\Controllers\Controller;
use App\Model\Article;
use App\Model\Category;
use App\Model\Tag;
use App\Model\ArticleTag;
use App\Http\Requests\Article\Store;
use Illuminate\Support\Facades\Cache;

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
            ->paginate(10);
        $assign = compact('article');
        return view('admin.article.index',$assign);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 发布文章
     */
    public function create(){
        $category = Category::all();
        $tag = Tag::all();
        $author = Config::where('name','AUTHOR')->value('value');
        $assign = compact("category","tag","author");
        return view('admin.article.create',$assign);
    }

    /**
     * @param Store $req
     * @param Article $articleModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 保存文章接口
     */
    public function store(Store $req , Article $articleModel){
        $data = $req->except('_token');
        if($req->hasFile('cover')){
            $result = upload('cover','uploads/article');
            if($result['status_code'] == 200){
                $data['cover'] = $result['data']['path'].$result['data']['new_name'];
            }
        }
        $result = $articleModel->storeData($data);
        if($result){
            // 更新热门推荐文章缓存  移除缓存
            Cache::forget('common:topArticle');
            // 更新标签统计缓存  移除缓存
            Cache::forget('common:tag');
        }
        return redirect('admin/article/index');
    }

    /**
     * @param $id
     * @param Article $articleModel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author shidatuo
     * @description 修改文章页面
     */
    public function edit($id,Article $articleModel){
        //>查询文章
        $article = $articleModel::withTrashed()->find($id);
        //>查询标签组成数组
        $article->tag_ids = ArticleTag::where('article_id', $id)->pluck('tag_id')->toArray();
        //>获取分类
        $category = Category::all();
        //>获取标签
        $tag = Tag::all();
        $assign = compact("category","tag","article");
        return view('admin.article.edit',$assign);
    }

    /**
     * @param Store $req
     * @param Article $articleModel
     * @param ArticleTag $articleTagModel
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @author shidatuo
     * @description 修改文章
     */
    public function update(Store $req ,Article $articleModel ,ArticleTag $articleTagModel ,$id){
        $data = $req->except('_token');
        $data['is_top'] = isset($data['is_top']) ? $data['is_top'] : 0;
        //>markdown 字段
        $markdown = $articleModel->where('id',$id)->value('markdown');
        //>取出封面图 字段
        $cover_value = $articleModel->where('id', $id)->value('cover');
        preg_match_all('/!\[.*\]\((.*.[jpg|jpeg|png|gif]).*\)/i', $markdown, $images);
        //>添加水印 并获取第一张图
        $cover = $articleModel->getCover($data['markdown'], $images[1]);
        //>是否上传了封面图
        if($req->hasFile('cover')){
            $result = upload('cover','uploads/article');
            if($result['status_code'] == 200){
                $data['cover'] = $result['data']['path'].$result['data']['new_name'];
            }
        }else{
            if(checkEmpty($cover_value))
                $data['cover'] = $cover;
        }
        //>为文章批量添加标签
        $tag_ids = isset($data['tag_ids']) ? $data['tag_ids'] : [];
        if(isset($data['tag_ids']))
            unset($data['tag_ids']);
        $articleTagModel->addTagIds($id,$tag_ids);
        //>把markdown 转化成 html
        $data['html'] = markdown_to_html($data['markdown']);
        $map = compact("id");
        $result = $articleModel->updateData($map,$data);
        if($result){
            //>更新热门推荐文章缓存
            Cache::forget('common:topArticle');
            //>更新标签统计缓存
            Cache::forget('common:tag');
        }
        return redirect('admin/article/index');
    }

    /**
     * @param $id
     * @param Article $articleModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 删除文章
     */
    public function destroy($id , Article $articleModel){
        $map = compact("id");
        $result = $articleModel->destroyData($map);
        if($result){
            //>更新缓存
            Cache::forget('common:topArticle');
        }
        return redirect('admin/article/index');
    }

    /**
     * @param $id
     * @param Article $articleModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 恢复文章
     */
    public function restore($id , Article $articleModel){
        $map = compact("id");
        $result = $articleModel->restoreData($map);
        if($result){
            //>更新缓存
            Cache::forget('common:topArticle');
        }
        return redirect('admin/article/index');
    }

    /**
     * @param $id
     * @param Article $articleModel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @author shidatuo
     * @description 彻底删除文章
     */
    public function forceDelete($id , Article $articleModel){
        $map = compact("id");
        $result = $articleModel->restoreData($map);
        if($result){
            //>更新缓存
            Cache::forget('common:topArticle');
        }
        return redirect('admin/article/index');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author shidatuo
     * @description markdown 编辑器上传图片
     */
    public function uploadImage(){
        $result = upload('editormd-image-file', 'uploads/article');
        if ($result['status_code'] === 200) {
            $data = [
                'success' => 1,
                'message' => $result['message'],
                'url' => $result['data']['path'].$result['data']['new_name']
            ];
        } else {
            $data = [
                'success' => 0,
                'message' => $result['message'],
                'url' => ''
            ];
        }
        return response()->json($data);
    }
    

}
