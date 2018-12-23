<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @description 授权表单请求
     */
    public function authorize(){
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @link https://laravelacademy.org/post/7978.html
     */
    public function rules(){
        /* 注：实际执行代码之前，需要在数据库中创建 posts 数据表，因为这里用到了 unique:categories 这个验证规则，该规则会去数据库中查询传入标题是否已存在以保证唯一性。*/
        return [
            'name'=>'required|unique:categories|max:15',
            'keywords'=>'required|max:255',
            'description'=>'required|max:255',
            'sort'=>'numeric'
        ];
    }

    /**
     * 定义字段名中文
     *
     * @return array
     */
    public function attributes(){
        return [
            'name'=>'分类名',
            'keywords'=>'关键字',
            'description'=>'描述',
        ];
    }

    /**
     * @return array
     * @author shidatuo
     * @description 自定义错误消息
     */
    public function messages(){
        return [
            'name.required' => '请填写分类名',
            'keywords.required' => '请填写分类关键字',
            'description.required' => '请填写分类描述',
            'sort.numeric' => '排序字段必须是数字'
        ];
    }
}
