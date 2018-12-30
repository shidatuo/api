<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ArticleTag extends BaseModel{

    /**
     * @param $article_id
     * @param $tag_ids
     * @author shidatuo
     * @description 为文章批量插入标签
     */
    public function addTagIds($article_id,$tag_ids){
        //>先删除此文章下的所有标签
        $map = compact("article_id");
        $this->whereMap($map)->delete();
        //>循环插入
        foreach ($tag_ids as $tag_id){
            $data = compact("article_id","tag_id");
            $this->storeData($data);
        }
    }

    /**
     * @param $ids
     * @return array
     * @author shidatuo
     * @description 传递一个文章id数组;获取标签名
     */
    public function getTagNameByArticleIds($ids){
        //>获取标签数据
        $tag = $this
            ->select('article_tags.article_id as id', 't.id as tag_id', 't.name')
            ->join('tags as t', 'article_tags.tag_id', 't.id')
            ->whereIn('article_tags.article_id', $ids)
            ->get();
        $data = [];
        // 组合成键名是文章id 键值是 标签数组
        foreach ($tag as $k => $v) {
            $data[$v->id][] = $v;
        }
        return $data;
    }
}
