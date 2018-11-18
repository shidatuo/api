<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
    public $table = 'app';
    // 软删除
    use SoftDeletes;
    //Todo :: 白名单与黑名单只能使用其中一个 , 而不是一起使用
    /**
     * 可以被赋值的白名单
     *
     * @var array
     */
//    protected  $fillable = [];
    /**
     * 不可以别赋值的黑名单
     *
     * @var array
     */
//    protected  $guarded = [];
    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
