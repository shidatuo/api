<?php

namespace App\Http\Requests\Tag;

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
            'name'=>'required|unique:tags,name,'. $this->route()->id
        ];
    }

    /**
     * 定义字段名中文
     *
     * @return array
     */
    public function attributes(){
        return [
            'name'=>'标签名称'
        ];
    }

    /**
     * @return array
     * @author shidatuo
     * @description 自定义错误消息
     */
    public function messages(){
        return [
            'name.required' => '请填写标签名称',
        ];
    }
}
