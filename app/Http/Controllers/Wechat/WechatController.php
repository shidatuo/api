<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WechatController extends Controller{

    /**
     * @description 使用登录凭证 code 获取 session_key 和 openid
     * @link https://www.w3cschool.cn/weixinapp/weixinapp-api-login.html
     */
    const API_WX_LOGIN = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 微信小程序code换取session_key
     */
    public function wxLogin(Request $req){
        $d['id'] = decode($req->input("appId",""));
        $d['fields'] = "appid,appSecret";
        $d['single'] = TRUE;
        $rs = get("app",$d);
        if(!$rs){
            jsonReturn(-1,"小程序不存在");
        }
        $request_url = sprintf(self::API_WX_LOGIN, $rs['appid'], $rs['appSecret'] , $req->input("code",""));
        $json = http_request($request_url);
        $result = json_decode($json,true);
        if(!isset($result['openid']))
            jsonReturn(-1,"无效的code");
        if(isset($result['openid']) && NotEstr($result['openid']))
            $m['openid'] = $result['openid'];
        if(isset($result['session_key']) && NotEstr($result['session_key']))
            $m['session_key'] = $result['session_key'];
        if(isset($result['unionid']) && NotEstr($result['unionid']))
            $m['unionid'] = $result['unionid'];
        //>避免用户重复
        $user = get("users","openid={$m['openid']}&single=true&fields=id");
        if(isset($user['id']) && isINT($user['id']))
            $m['id'] = $user['id'];
        if(isset($m) && is_arr($m))
            $s = save("users",$m);
        if(isset($s) && $s)
            jsonReturn(1,"请求成功",$m);
        jsonReturn(-1,"保存失败");
    }
}
