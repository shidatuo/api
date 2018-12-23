<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel{


    public function removeData($map){
        // 先获取分类id
        $categoryIdArray = $this
            ->whereMap($map)
            ->pluck('id')
            ->toArray();
        // 获取分类下的文章数
        $articleCount = Article::whereIn('category_id',$categoryIdArray)->count();
        // 如果分类下存在文章；则需要下删除文章
        if ($articleCount > 0) {
            flash_error('请先删除此分类下的文章', false);
            return false;
        }
        // 删除分类
        return parent::destroyData($map);
    }
}
