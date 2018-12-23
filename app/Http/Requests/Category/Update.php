<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(){
        return [
            'name'=>'required|max:15|unique:categories,name,'.$this->route()->id,
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
