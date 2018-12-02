<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Model\User;
use Illuminate\Support\Facades\URL;

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
        log_ex("wxLogin",PHP_EOL . "============================== 微信小程序code换取session_key START =============================" . PHP_EOL);
        log_ex("wxLogin",PHP_EOL . "获取请求的url : " . URL::current() . PHP_EOL);
        $d['id'] = decode($req->input("appId",""));
        $d['fields'] = "appid,appSecret";
        $d['single'] = TRUE;
        log_ex("wxLogin",PHP_EOL . "查询小程序参数的信息 : " . json_encode($d) . PHP_EOL);
        $rs = get("app",$d);
        log_ex("wxLogin",PHP_EOL . "查询小程序信息返回结果 : " . json_encode($rs) . PHP_EOL);
        if(!$rs){
            log_ex("wxLogin",PHP_EOL ."返回 -1 [小程序不存在]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
            jsonReturn(-1,"小程序不存在");
        }
        $request_url = sprintf(self::API_WX_LOGIN,$rs['appid'],$rs['appSecret'],$req->input("code",""));
        log_ex("wxLogin",PHP_EOL . "请求微信服务器url : " . $request_url . PHP_EOL);
        $json = http_request($request_url);
        log_ex("wxLogin",PHP_EOL . "微信服务器返回值 : " . $json . PHP_EOL);
        $result = json_decode($json,true);
        if(!isset($result['openid'])){
            log_ex("wxLogin",PHP_EOL ."返回 -1 [无效的code]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
            jsonReturn(-1,"无效的code");
        }
        $m['appId'] = $req->input("appId","");
        if(isset($result['openid']) && NotEstr($result['openid']))
            $m['openid'] = $result['openid'];
        if(isset($result['session_key']) && NotEstr($result['session_key']))
            $m['session_key'] = $result['session_key'];
        if(isset($result['unionid']) && NotEstr($result['unionid']))
            $m['unionid'] = $result['unionid'];
        //>避免用户重复
        log_ex("wxLogin",PHP_EOL . "获取到用户的openid : {$m['openid']}" . PHP_EOL);
        $user = get("users","openid={$m['openid']}&single=true&fields=id");
        log_ex("wxLogin",PHP_EOL . "获取到用户信息 : " . json_encode($user) . PHP_EOL);
        if(isset($user['id']) && isINT($user['id']))
            $m['id'] = $user['id'];
        if(isset($m) && is_arr($m))
            $s = save("users",$m);
        log_ex("wxLogin",PHP_EOL . "保存用户信息 : " . json_encode($m) . PHP_EOL);
        if(isset($s) && $s){
            log_ex("wxLogin",PHP_EOL ."返回 1 [请求成功]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
            jsonReturn(1,"请求成功",$m);
        }
        log_ex("wxLogin",PHP_EOL ."返回 -1 [保存失败]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
        jsonReturn(-1,"保存失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 保存微信小程序获取的用户信息
     */
    public function wxUser(Request $req,User $user){
        log_ex("wxUser",PHP_EOL . "============================== 保存微信小程序获取的用户信息 START =============================" . PHP_EOL);
        log_ex("wxUser",PHP_EOL . "获取请求的url : " . URL::current() . PHP_EOL);
        $data = array();
        $params = $req->all();
        log_ex("wxUser",PHP_EOL . "打印出请求的参数 : " . json_encode($params) . PHP_EOL);
        $uid = $user::getuid($params['openid']);
        log_ex("wxUser",PHP_EOL . "获取到的用户id为 : " . $uid . PHP_EOL);
        $data['id'] = $uid;
        if(!$uid)
            jsonReturn(-1,"该用户不存在");
        if(isset($params['nickName']) && NotEstr($params['nickName']))
            $data['nickName'] = $params['nickName'];
        if(isset($params['avatarUrl']) && NotEstr($params['avatarUrl']))
            $data['avatarUrl'] = $params['avatarUrl'];
        if(isset($params['gender']) && is_numeric($params['gender']))
            $data['gender'] = $params['gender'];
        if(isset($params['country']) && NotEstr($params['country']))
            $data['country'] = $params['country'];
        if(isset($params['province']) && NotEstr($params['province']))
            $data['province'] = $params['province'];
        if(isset($params['city']) && NotEstr($params['city']))
            $data['city'] = $params['city'];
        log_ex("wxUser",PHP_EOL . "保存的用户信息为 : " . json_encode($data) . PHP_EOL);
        $rs = save("users",$data);
        if($rs){
            log_ex("wxUser",PHP_EOL ."返回 1 [保存成功]" .PHP_EOL."============================== 保存微信小程序获取的用户信息 END =============================" . PHP_EOL);
            jsonReturn(1,"保存成功");
        }
        log_ex("wxUser",PHP_EOL ."返回 -1 [保存失败]" .PHP_EOL."============================== 保存微信小程序获取的用户信息 END =============================" . PHP_EOL);
        jsonReturn(-1,"保存失败");
    }

}
