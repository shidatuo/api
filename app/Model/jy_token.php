<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class jy_token extends Model{

    public $table = "jy_token";

    /**
     * @param $params
     * @return string
     * @author shidatuo
     * @description 创建token
     */
    public function createToken($params){
        $str = '';
        if(isset($params['userName']) && NotEstr($params['userName']))
            $str .= $params['userName'];
        if(isset($params['password']) && NotEstr($params['password']))
            $str .= $params['password'];
        $str .= time();
        return bcrypt($str);
    }
}
