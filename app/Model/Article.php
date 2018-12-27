<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Article extends BaseModel{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @param 渴望加载
     * @param 存在急切加载以缓解N + 1查询问题。例如，考虑Book与之相关的模型Author。这种关系定义如下
     * @link https://laravel.com/docs/4.2/eloquent#eager-loading
     */
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
