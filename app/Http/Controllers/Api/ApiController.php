<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use \App\Model\User;
use \App\Model\jy_express;
use \App\Model\jy_token;
use \App\Model\jy_back_user;
use \App\Model\jy_user;
use WxPayConf;
use WxQrcodePay;
//use WxPay;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use \App\Libraries\Functions\WxPay\WxPay as WxPay;

class ApiController extends Controller{

    public $app;

    /**
     * @description 使用登录凭证 code 获取 session_key 和 openid
     * @link https://www.w3cschool.cn/weixinapp/weixinapp-api-login.html
     */
    const API_WX_LOGIN = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

    public function __construct($app = null){
//        if (!is_object($this->app)) {
//            if (is_object($app)) {
//                $this->app = $app;
//            } else {
//                $this->app = xn();
//            }
//        }
    }

    public function api(){
        $params = false;
        $callback = isset($_GET['callback']) ? trim($_GET['callback']) : ''; //jsonp回调参数，必需
        if (!defined('API_CALL')) {
            define('API_CALL', true);
        }
        $mod_class_api = false;
        $mod_class_api_called = false;

        $api_function_full = new UrlManager;

        $api_function_full = $api_function_full->string();
        $api_function_full = replace_once('v2', 'api', $api_function_full);


        $api_function_full = replace_once('apis', '', $api_function_full);
        $api_function_full = trim($api_function_full, '/');

        $api_function_full = str_replace('..', '', $api_function_full);
        $api_function_full = str_replace('\\', '/', $api_function_full);
        $api_function_full = str_replace('//', '/', $api_function_full);

        //过滤请求url，转成数组
        $api_function_full = str_replace('..', '', $api_function_full);
        $api_function_full = str_replace('\\', '/', $api_function_full);
        $api_function_full = str_replace('//', '/', $api_function_full);


        $search = array('\\', "\x00", "\n", "\r", "'", '"', "\x1a");
        $replace = array('\\\\', '\\0', '\\n', '\\r', "\'", '\"', '\\Z');
        $api_function_full = str_replace($search, $replace, $api_function_full);

//        $api_function_full = $this->app->database_manager->escape_string($api_function_full);
        if (is_string($api_function_full)) {
            $mod_api_class = explode('/', $api_function_full);
        } else {
            $mod_api_class = $api_function_full;
        }


        $try_class_func = array_pop($mod_api_class);
        // $try_class_func2 = array_pop($mod_api_class);
        $mod_api_class_copy = $mod_api_class;
        $try_class_func2 = array_pop($mod_api_class_copy);
        $mod_api_class2 = implode(DS, $mod_api_class_copy);

        $mod_api_class = implode(DS, $mod_api_class);
        $mod_api_class_clean = ltrim($mod_api_class, '/');
        $mod_api_class_clean = ltrim($mod_api_class_clean, '\\');
        $mod_api_class_clean_uc1 = ucfirst($mod_api_class_clean);

        //得到相关方法过滤
        $api_exposed = '';
        // user functions
        $api_exposed .= 'user_login user_logout social_login_process set_language';
        // content functions
        $api_exposed .= (api_expose(true));
        $api_exposed .= (api_expose_user(true));
        $api_exposed .= (api_expose_admin(true));
        $api_exposed = explode(' ', $api_exposed);
        $api_exposed = array_unique($api_exposed);
        $api_exposed = array_trim($api_exposed);
        //定义钩子
        $hooks = api_bind(true);
        $hooks_admin = api_bind_user(true);
        if (is_array($hooks_admin)) {
            $hooks = array_merge($hooks, $hooks_admin);
        }
        $hooks_admin = api_bind_admin(true);
        if (is_array($hooks_admin)) {
            $hooks = array_merge($hooks, $hooks_admin);
        }
        //请求方法
//        $api_function = $this->app->url_manager->segment(1);
//        if (!defined('XN_API_RAW')) {
//            if ($mod_class_api != false) {
//                $url_segs = $this->app->url_manager->segment(-1);
//            }
//        } else {
//            if (is_array($api_function)) {
//                $url_segs = $api_function;
//            } else {
//                $url_segs = explode('/', $api_function);
//            }
//        }

//        if (!defined('XN_API_FUNCTION_CALL')) {
//            define('XN_API_FUNCTION_CALL', $api_function);
//        }
        $res = false;
        if (isset($hooks[$api_function_full])) {
            $data = array_merge($_GET, $_POST);
            $call = $hooks[$api_function_full];
            if (!empty($call)) {
                foreach ($call as $call_item) {
                    $res = call_user_func($call_item, $data);
                }
            }
            if ($res != false) {
                return $this->_api_responce($res);
            }
        }
        if ($mod_class_api == true and $mod_api_class != false) {
            $mod_api_class = str_replace('..', '', $mod_api_class);
            $try_class = str_replace('/', '\\', $mod_api_class);
            $try_class_full = str_replace('/', '\\', $api_function_full);
            $try_class_full2 = str_replace('\\', '/', $api_function_full);
            $mod_api_class_test = explode('/', $try_class_full2);
            $try_class_func_test = array_pop($mod_api_class_test);
            $mod_api_class_test_full = implode('/', $mod_api_class_test);
            $mod_api_err = false;
            if (!defined('XN_API_RAW')) {
                if (!in_array($try_class_full, $api_exposed, true) and !in_array($try_class_full2, $api_exposed, true) and !in_array($mod_api_class_test_full, $api_exposed, true)) {
                    $mod_api_err = true;
                    foreach ($api_exposed as $api_exposed_value) {
                        if ($mod_api_err == true) {
                            if ($api_exposed_value == $try_class_full) {
                                $mod_api_err = false;
                            } elseif (strtolower('\\' . $api_exposed_value) == strtolower($try_class_full)) {
                                $mod_api_err = false;
                            } elseif ($api_exposed_value == $try_class_full2) {
                                $mod_api_err = false;
                            } else {
                                $convert_slashes = str_replace('\\', '/', $try_class_full);

                                if ($convert_slashes == $api_exposed_value) {
                                    $mod_api_err = false;
                                }
                            }
                        }
                    }
                } else {
                    $mod_api_err = false;
                }
            }

            if ($mod_class_api and $mod_api_err == false) {
                if (!class_exists($try_class, false)) {
                    $remove = $url_segs;
                    $last_seg = array_pop($remove);
                    $last_prev_seg = array_pop($remove);
                    $last_prev_seg2 = array_pop($remove);

                    if (class_exists($last_prev_seg, false)) {
                        $try_class = $last_prev_seg;
                    } elseif (class_exists($last_prev_seg2, false)) {
                        $try_class = $last_prev_seg2;
                    }
                }

                if (!class_exists($try_class, false)) {
                    $try_class_xn = ltrim($try_class, '/');
                    $try_class_xn = ltrim($try_class_xn, '\\');
                    $try_class = $try_class_xn;
                }

                if (class_exists($try_class, false)) {
                    if ($params != false) {
                        $data = $params;
                    } elseif (!$_POST and !$_REQUEST) {
                        $data = $this->app->url_manager->params(true);
                        if (empty($data)) {
                            $data = $this->app->url_manager->segment(2);
                        }
                    } else {
                        $data = array_merge($_GET, $_POST);
                    }
                    $res = new $try_class($data);

                    if (method_exists($res, $try_class_func) or method_exists($res, $try_class_func2)) {
                        if (method_exists($res, $try_class_func2)) {
                            $try_class_func = $try_class_func2;
                        }
                        $res = $res->$try_class_func($data);
                        $mod_class_api_called = true;
                        return $this->_api_responce($res);
                    }
                } else {
//                    xn_error('The api class ' . $try_class . '  does not exist');
                }
            }
        }
//        dd($mod_class_api_called);
        if ($mod_class_api_called == false) {
            if (!$_POST and !$_REQUEST) {
                //  $data = $this->app->url_manager->segment(2);
                $data = $_REQUEST;
                if (empty($data)) {
//                    $data = $this->app->url_manager->segment(2);
                }
            } else {
                //$data = $_REQUEST;
                $data = array_merge($_GET, $_POST);
            }
            $api_function_full_2 = explode('/', $api_function_full);
            $api_function_full_2 = implode('/', $api_function_full_2);
            if (function_exists($api_function_full)) {
                $res = $api_function_full($data);
            }
            else {
                $api_function_full_2 = str_replace(array('..', '/'), array('', '\\'), $api_function_full_2);
                $api_function_full_2 = __NAMESPACE__ . '\\' . $api_function_full_2;
                if (class_exists($api_function_full_2, false)) {
                    $segs = $this->app->url_manager->segment();
                    $mmethod = array_pop($segs);

                    $class = new $api_function_full_2($this->app);

                    if (method_exists($class, $mmethod)) {
                        $res = $class->$mmethod($data);
                    }
                } elseif (isset($api_function_full)) {
                    $api_function_full = str_replace('\\', '/', $api_function_full);
                    $api_function_full1 = explode('/', $api_function_full);
                    $mmethod = array_pop($api_function_full1);
                    $mclass = array_pop($api_function_full1);

                    if (class_exists($mclass, false)) {
                        $class = new $mclass($this->app);

                        if (method_exists($class, $mmethod)) {
                            $res = $class->$mmethod($data);
                        }
                    }
                }
            }
        }
//        if (isset($res)  and !empty($hooks[$api_function])) {
//            foreach ($hooks[$api_function] as $hook_key => $hook_value) {
//                if ($hook_value != false and $hook_value != null) {
//                    $hook_value($res);
//                }
//            }
//        }
        $u_data['code']=(int)$mod_class_api_called;
        $u_data['data']=$res;
        $u_data['ver']='1.0';

        if (isset($res)) {
            if (!empty($callback)) {
                return $this->_api_responce($u_data, true, $callback);
            } else {
                return $this->_api_responce($u_data);
            }
        }
        return;
    }

    /**
     * @param $res
     * @param bool $jsonp
     * @param string $callback
     * @return string
     * @author shidatuo
     * @description 返回数据转化成json
     */
    private function _api_responce($res,$jsonp=false,$callback=''){
        if($jsonp){
            $tmp= json_encode($res); //json 数据
            echo $callback . '(' . $tmp .')';  //返回格式，必需
            exit;
        }
        $response=json_encode($res);
        return $response;
    }



    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 微信小程序code换取session_key
     */
    public function wxLogin(Request $req){
        log_ex("wxLogin",PHP_EOL . "============================== 微信小程序code换取session_key START =============================" . PHP_EOL);
        log_ex("wxLogin",PHP_EOL . "获取请求的url : " . URL::current() . PHP_EOL);
//        $request_url = sprintf(self::API_WX_LOGIN,'wx6e75e53e4a50bf41','c716d92c8e4f2df7f54a73c563e24b57',$req->input("code",""));
        $request_url = sprintf(self::API_WX_LOGIN,'wx47102dcd005677d3','a3edbe21439b90a1ef0f6132a784ae2a',$req->input("code",""));
        log_ex("wxLogin",PHP_EOL . "请求微信服务器url : " . $request_url . PHP_EOL);
        $json = http_request($request_url);
        log_ex("wxLogin",PHP_EOL . "微信服务器返回值 : " . $json . PHP_EOL);
        $result = json_decode($json,true);
        if(!isset($result['openid'])){
            log_ex("wxLogin",PHP_EOL ."返回 -1 [无效的code]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
            jsonReturn(201,"无效的code");
        }
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取用户信息
     */
    public function wxgetUser(Request $req){
        log_ex("wxLogin",PHP_EOL . "============================== 获取用户信息 START =============================" . PHP_EOL);
        log_ex("wxLogin",PHP_EOL . "获取请求的url : " . URL::current() . PHP_EOL);
        $open_id = $req->input("openid","");
        if(!NotEstr($open_id))
            jsonReturn(201,"无效的openid");
        log_ex("wxLogin",PHP_EOL . "获取open_id : " . $open_id . PHP_EOL);
        $user_info = get("jy_user","openid={$open_id}&single=true");
        log_ex("wxLogin",PHP_EOL . "根据open_id 获取到用户信息" . json_encode($user_info) . PHP_EOL);
        jsonReturn(200,"请求成功",$user_info ? $user_info : []);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 创建用户信息
     */
    public function wxcreateUser(Request $req,User $user){
        log_ex("wxcreateUser",PHP_EOL . "============================== 创建用户信息 START =============================" . PHP_EOL);
        log_ex("wxcreateUser",PHP_EOL . "获取请求的url : " . URL::current() . PHP_EOL);
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $save['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['avatarUrl']) && NotEstr($params['avatarUrl']))
            $save['avatarUrl'] = $params['avatarUrl'];
        if(isset($params['gender']))
            $save['gender'] = $params['gender'];
        if(isset($params['nickName']) && NotEstr($params['nickName']))
            $save['nickName'] = $user::EmojinickNameHTML($params['nickName']);
        if(isset($params['unionid']) && NotEstr($params['unionid']))
            $save['unionid'] = $params['unionid'];
        $user_info = get("jy_user","openid={$save['openid']}&single=true&fields=id");
        if(isset($user_info['id']) && isINT($user_info['id']))
            $save['id'] = $user_info['id'];
        if(isset($save) && is_arr($save))
            log_ex("wxcreateUser",PHP_EOL . "保存用户信息 : " . json_encode($save) . PHP_EOL);
            $s = save("jy_user",$save);
        if(isset($s) && $s){
            log_ex("wxcreateUser",PHP_EOL ."返回 1 [请求成功]" .PHP_EOL."============================== 创建用户信息 END =============================" . PHP_EOL);
            $u = get("jy_user","id=$s&single=true");
            jsonReturn(200,"请求成功",$u);
        }
        log_ex("wxcreateUser",PHP_EOL ."返回 -1 [保存失败]" .PHP_EOL."============================== 创建用户信息 END =============================" . PHP_EOL);
        jsonReturn(201,"保存失败");
    }


    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 上传图片
     */
    public function wxupImg(){
        ini_set('upload_max_filesize', '2500M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 0);
        ini_set('post_max_size', '2500M');
        ini_set('max_input_time', 9999999);
        $allowedExts = array('jpg', 'jpeg', 'gif', 'png', 'bmp','pem','txt','mp4','xls');
        foreach ($_FILES as $key => $item) {
            if(isset($item['size']) && $item['size'] > 31457280)
                jsonReturn(201,"上传文件不能大于30M");
            $efile = explode('.', $item['name']);
            $extension = end($efile);
            $extension = strtolower($extension);
            if (in_array($extension, $allowedExts)) {
                if ($item['error'] > 0){
                    jsonReturn(201,$item['error']);
                } else {
                    $rs = [];
                    $f = $_SERVER['DOCUMENT_ROOT'] . DS . 'api' . DS . md5(uniqid()) . '.png';
                    if (move_uploaded_file($item['tmp_name'], $f)) {
                        $rs[] = str_replace(public_path(),Config("config.DNS"),$f);
                    }
                    jsonReturn(200,"请求成功",is_arr($rs) ? $rs : []);
                }
            }
        }
    }


    public function backgetuploadImg(){
        ini_set('upload_max_filesize', '2500M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 0);
        ini_set('post_max_size', '2500M');
        ini_set('max_input_time', 9999999);
        $allowedExts = array('jpg', 'jpeg', 'gif', 'png', 'bmp','pem','txt','mp4','xls');
        foreach ($_FILES as $key => $item) {
            if(isset($item['size']) && $item['size'] > 31457280)
                jsonReturn(201,"上传文件不能大于30M");
            $efile = explode('.', $item['name']);
            $extension = end($efile);
            $extension = strtolower($extension);
            if (in_array($extension, $allowedExts)) {
                if ($item['error'] > 0){
                    jsonReturn(201,$item['error']);
                } else {
                    $rs = [];
                    $f = $_SERVER['DOCUMENT_ROOT'] . DS . 'api' . DS . md5(uniqid()) . '.png';
                    if (move_uploaded_file($item['tmp_name'], $f)) {
                        $rs[] = str_replace(public_path(),Config("config.DNS"),$f);
                    }
                    jsonReturn(200,"请求成功",is_arr($rs) ? $rs : []);
                }
            }
        }
    }


    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 销售商资料入住
     */
    public function wxupSaleInfo(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['userName']) && NotEstr($params['userName']))
            $data['userName'] = $params['userName'];
        else
            jsonReturn(201,"无效的user_name");
        if(isset($params['identity']) && NotEstr($params['identity']))
            $data['identity'] = $params['identity'];
        else
            jsonReturn(201,"无效的identity");
        if(isset($params['address']) && NotEstr($params['address']))
            $data['address'] = $params['address'];
        else
            jsonReturn(201,"无效的address");
        if(isset($params['pic1']) && NotEstr($params['pic1']))
            $data['pic1'] = $params['pic1'];
        else
            jsonReturn(201,"无效的pic1");
        if(isset($params['pic2']) && NotEstr($params['pic2']))
            $data['pic2'] = $params['pic2'];
        else
            jsonReturn(201,"无效的pic2");
        if(isset($params['pic3']) && NotEstr($params['pic3']))
            $data['pic3'] = $params['pic3'];
        else
            jsonReturn(201,"无效的pic3");
        if(isset($params['phoneNumber']) && NotEstr($params['phoneNumber']))
            $data['phoneNumber'] = $params['phoneNumber'];
        else
            jsonReturn(201,"无效的phoneNumber");
        $rs = get("jy_sale","openid={$data['openid']}&single=true&fields=id,status");
        if(isset($rs['id']) && isINT($rs['id'])){
            if(isset($rs['status']) && $rs['status'] == 1){
                jsonReturn(201,"已经审核成功,请勿重新提交");
            }
            $data['id'] = $rs['id'];
            $data['status'] = 0;
        }
        $result = save("jy_sale",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(201,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 销售商资料入住
     */
    public function wxgetSaleInfo(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        $data['single'] = true;
        $rs = get("jy_sale",$data);
        $resule = $rs ? $rs : [];
        jsonReturn(200,"请求成功",$resule);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 录入商品
     */
    public function wxupSaleGoods(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['title']) && NotEstr($params['title']))
            $data['title'] = $params['title'];
        else
            jsonReturn(201,"无效的title");
        if(isset($params['stock']) && isINT($params['stock']))
            $data['actual_stock'] = $data['stock'] = $params['stock'];
        else
            jsonReturn(201,"无效的stock");
        if(isset($params['pic']) && NotEstr($params['pic']))
            $data['pic'] = $params['pic'];
        else
            jsonReturn(201,"无效的pic");
        if(isset($params['price']))
            $data['price'] = $params['price'];
        else
            jsonReturn(201,"无效的price");
        if(isset($params['dis_price']))
            $data['dis_price'] = $params['dis_price'];
        else
            jsonReturn(201,"无效的dis_price");
        if(isset($params['spec']) && NotEstr($params['spec']))
            $data['spec'] = $params['spec'];
        else
            jsonReturn(201,"无效的dis_price");
        if(isset($params['intro']) && NotEstr($params['intro']))
            $data['intro'] = $params['intro'];
        else
            jsonReturn(201,"无效的intro");
        if(isset($params['end_time']) && NotEstr($params['end_time']))
            $data['end_time'] = $params['end_time'];
        else
            jsonReturn(201,"无效的end_time");
        if(isset($params['deliver']))
            $data['deliver'] = $params['deliver'];
        $data['state'] = 1;
        $result = save("jy_sale_goods",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(201,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取拼单信息(销售商发起的)
     */
    public function wxgetActive(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];
        else
            $data['current_page'] = 1;
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        $data['order_by'] = "id desc";
        $rs = get("jy_sale_goods",$data);
        $resule = $rs ? $rs : [];
        foreach ($resule as $item=>$value){
            $user_info = get("jy_user","openid={$value['openid']}&single=true&fields=avatarUrl,nickName");
            $resule[$item]['avatarUrl'] = isset($user_info['avatarUrl']) ? $user_info['avatarUrl'] : '';
            $resule[$item]['nickName'] = isset($user_info['nickName']) ? $user_info['nickName'] : '';
            $open_ids = get("jy_order","goods_id={$value['id']}&fields=openid&no_limit=true&state=[gte]1");
            $open_ids = $open_ids ? $open_ids : [];
            foreach ($open_ids as $v){
                $resule[$item]['avatarUrls'][] = self::wxgetUseravatarUrl($v['openid']);
            }
            if(!isset($resule[$item]['avatarUrls']))
                $resule[$item]['avatarUrls'] = [];
        }
        jsonReturn(200,"请求成功",$resule);
    }

    /**
     * @param $openid
     * @return \___PHPSTORM_HELPERS\static|mixed|string
     * @throws \Exception
     * @author shidatuo
     * @description 获取用户头像
     */
    public function wxgetUseravatarUrl($openid){
        $user_info = get("jy_user","openid={$openid}&single=true&fields=avatarUrl");
        return isset($user_info['avatarUrl']) ? $user_info['avatarUrl'] : '';
    }

    /**
     * @param $openid
     * @return \___PHPSTORM_HELPERS\static|mixed|string
     * @throws \Exception
     * @author shidatuo
     * @description 获取用户信息
     */
    public function wxgetUserInfo($openid){
        $user_info = get("jy_user","openid={$openid}&single=true&fields=avatarUrl,nickName");
        return $user_info;
    }

    /**
     * @return string
     * @author shidatuo
     * @description 获取微信小程序的token
     */
    public function getAccessToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx47102dcd005677d3&secret=a3edbe21439b90a1ef0f6132a784ae2a';
        $access_token = http_request($url);
        $result = json_decode($access_token);
        return isset($result->access_token) ? $result->access_token : '';
    }

    /**
     * @param Request $req
     * @return array
     * @author shidatuo
     * @description 获取小程序的商品详情的二维码
     */
    public function wxgetQcodeGoodsInfo(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $id = $params['id'];
        else
            jsonReturn(201,"无效的id");
        //>获取二维码
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.self::getAccessToken();
        //小程序二维码路径
        $u['scene'] = "id={$id}";
        $u['width'] = 150;
        $u['page'] = 'pages/goods/goods';
        $result = http_request($url,json_encode($u));
        if(isset($result['errcode'])){
            jsonReturn(203,"图片获取错误");
        }
        $qCodePath = $_SERVER['DOCUMENT_ROOT']."/qrcodes/wx47102dcd005677d3_{$id}.jpg";
        $fp = fopen($qCodePath,"w+"); //将文件绑定到流
        fwrite($fp, $result); //写入文件
        $code = str_replace(public_path(),Config("config.DNS"),$qCodePath);
        jsonReturn(200,"请求成功",$code);
    }


        /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取商品信息
     */
    public function wxgetGoods(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];
        else
            jsonReturn(201,"无效的id");
        $data['single'] = true;
        $rs = get("jy_sale_goods",$data);
        $resule = $rs ? $rs : [];
        if(isset($resule['openid']) && NotEstr($resule['openid'])){
            $user_info = get("jy_user","openid={$resule['openid']}&single=true&fields=nickName,avatarUrl,phoneNumber");
            if($user_info){
                $resule['nickName'] = $user_info['nickName'];
                $resule['avatarUrl'] = $user_info['avatarUrl'];
                $resule['phoneNumber'] = $user_info['phoneNumber'];
            }
        }
        jsonReturn(200,"请求成功",$resule);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 提交订单
     */
     public function wxInsertBill(Request $req){
         $params = $req->all();
         if(isset($params['openid']) && NotEstr($params['openid']))
             $data['openid'] = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         if(isset($params['avatarUrl']) && NotEstr($params['avatarUrl']))
             $data['avatarUrl'] = $params['avatarUrl'];
         else
             jsonReturn(201,"无效的avatarUrl");
         if(isset($params['nickName']) && $params['nickName'] == 'undefined')
             jsonReturn(204,"请重新登陆");
         if(isset($params['nickName']) && NotEstr($params['nickName']))
             $data['nickName'] = $params['nickName'];
         else
             jsonReturn(201,"无效的nickName");
         if(isset($params['id']) && isINT($params['id']))
             $data['goods_id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['stock']) && isINT($params['stock']))
             $data['num'] = $params['stock'];
         else
             jsonReturn(201,"无效的stock");
         if(isset($params['message']) && NotEstr($params['message']))
             $data['message'] = $params['message'];
         $sale_goods = get("jy_sale_goods","id={$data['goods_id']}&single=true&fields=stock,price,deliver,actual_stock,state");
         if(!$sale_goods)
             jsonReturn(201,"商品不存在");
         if(isset($sale_goods['actual_stock']) && $sale_goods['actual_stock'] <= 0)
             jsonReturn(201,"库存不足 , 已售完");
         if(isset($sale_goods['actual_stock']) && $data['num'] > $sale_goods['actual_stock'])
             jsonReturn(202,"库存不足");
         if(isset($sale_goods['state']) && $sale_goods['state'] > 1)
             jsonReturn(203,"此商品不能购买");
         if(!isset($sale_goods['price']) || (isset($sale_goods['price']) && !isINT($sale_goods['price'])))
             jsonReturn(201,"无效的商品价格");
//         $data['amount'] = bcpow($data['stock'],$sale_goods['price'],2);
         $data['amount'] = $data['num'] * $sale_goods['price'];
         if(isset($params['is_deliver']) && isINT($params['is_deliver'])){
             if(isset($params['address']) && NotEstr($params['address']))
                 $data['address'] = $params['address'];
             else
                 jsonReturn(201,"无效的address");
             if(isset($params['detailInfo']) && NotEstr($params['detailInfo']))
                 $data['detailInfo'] = $params['detailInfo'];
             else
                 jsonReturn(201,"无效的detailInfo");
             if(isset($params['userName']) && NotEstr($params['userName']))
                 $data['userName'] = $params['userName'];
             else
                 jsonReturn(201,"无效的userName");
             if(isset($params['postalCode']) && NotEstr($params['postalCode']))
                 $data['postalCode'] = $params['postalCode'];
             else
                 jsonReturn(201,"无效的postalCode");
         }else{
//             if(isset($params['telNumber']) && NotEstr($params['telNumber']))
//                 $data['telNumber'] = $params['telNumber'];

         }
         if(isset($params['telNumber']) && NotEstr($params['telNumber']))
             $data['telNumber'] = $params['telNumber'];
         else
             jsonReturn(201,"无效的telNumber");
//         $data['is_deliver'] = isset($sale_goods['deliver']) ? $sale_goods['deliver'] : 1;
         $data['is_deliver'] = isset($params['is_deliver']) ? $params['is_deliver'] : 1;
         $result = save("jy_order",$data);
         if($result){
             //支付减去库存 , 改变活动状态
             if($sale_goods['actual_stock'] == $data['num'])
                 $state = 2;
             else
                 $state = 1;
             $s['id'] = $data['goods_id'];
             $s['state'] = $state;
             $s['actual_stock'] = $sale_goods['actual_stock'] - $data['num'];
             log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '保存商品数据 : ' .json_encode($s) . PHP_EOL);
//             dump($s);
             save("jy_sale_goods",$s);
             jsonReturn(200,"请求成功",[$result]);
         }
         jsonReturn(201,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取我的订单列表
     */
     public function wxgetOrderList(Request $req){
         $params = $req->all();
         if(isset($params['openid']) && NotEstr($params['openid']))
             $data['openid'] = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         if(isset($params['state']) && isINT($params['state']))
             $data['state'] = $params['state'];
         else
             $data['state'] = 1;
         $data['order_by'] = "id desc";
         $rs = get("jy_order",$data);
         $resule = $rs ? $rs : [];
         foreach ($resule as $item=>$value){
             $resule[$item]['stock'] = $value['num'];
             $goods_info = self::getOrderGoods($value);
             $resule[$item]['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
             $resule[$item]['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
             $resule[$item]['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
             $resule[$item]['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
             $resule[$item]['deliver'] = isset($goods_info['deliver']) ? $goods_info['deliver'] : 1;
             $resule[$item]['member'] = self::getOrderAvatarUrl($value);
         }
         jsonReturn(200,"请求成功",$resule);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取购买当前商品的人员信息
     */
     public function wxPurchaser(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['goods_id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         $data['state'] = "[gte]1";
         $data['fields'] = "avatarUrl";
         $rs = get("jy_order",$data);
         $p = count($rs);
         $resule = $rs ? $rs : [];
         $sale_goods = get("jy_sale_goods","id={$data['goods_id']}&single=true&fields=stock");
         jsonReturn(200,"请求成功",[
                 'avatarUrls'=>$resule,
                 'surplus'=>isset($sale_goods['stock']) ? $sale_goods['stock'] - $p : 0
             ]
         );
     }

    /**
     * @param Request $req
     * @author shiatuo
     * @description 微信小程序绑定手机号
     */
     public function wxgetPhonenumber(Request $req){
         $params = $req->all();
         if(isset($params['openid']) && NotEstr($params['openid']))
             $openid = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         if(isset($params['encryptedData']) && NotEstr($params['encryptedData']))
             $encryptedData = $params['encryptedData'];
         else
             jsonReturn(201,"无效的encryptedData");
         if(isset($params['iv']) && strlen($params['iv']) == 24){
             $iv = $params['iv'];
         }else{
             jsonReturn(201,"无效的iv");
         }
         if(isset($params['session_key']) && strlen($params['session_key']) == 24){
             $session_key = $params['session_key'];
         }else{
             jsonReturn(201,"无效的session_key");
         }
         $encryptedData = str_replace(' ', '+',$encryptedData);
         $encryptedData = base64_decode($encryptedData);
         $aesKey = base64_decode($session_key);
         $aesIV = str_replace(' ','+',$iv);
         $aesIV = base64_decode($aesIV);
         $result = openssl_decrypt($encryptedData,'AES-128-CBC',$aesKey,1,$aesIV);
         $res = json_decode($result,true);
         if(isset($res['watermark']['appid']) == 'wx47102dcd005677d3') {
             //成功获取手机号
             DB::table("jy_user")->where(['openid'=>$openid])->update(["phoneNumber"=>$res['phoneNumber']]);
             jsonReturn(200,"绑定成功",['phoneNumber'=>$res['phoneNumber']]);
         }
         jsonReturn(201,"绑定失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取订单详情
     */
     public function wxgetOrderDetails(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['single'] = true;
         $rs = get("jy_order",$data);
         $resule = $rs ? $rs : [];
         $resule['stock'] = $resule['num'];
         $goods_info = self::getOrderGoods($resule);
         $resule['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
         $resule['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
         $resule['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
         $resule['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
         $resule['deliver'] = isset($goods_info['deliver']) ? $goods_info['deliver'] : 1;
         $resule['avatarUrls'] = self::getOrderAvatarUrl($resule);
         jsonReturn(200,"请求成功",$resule);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 确认订单 , 已收货
     */
     public function wxtakeOver(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['state'] = 4;
         $result = save("jy_order",$data);
         if($result){
             self::market_brokerage($data['id']);
             jsonReturn(200,"请求成功",[$result]);
         }else{
             jsonReturn(201,"请求失败");
         }
     }

    /**
     * @param $id
     * @throws \Exception
     * @author shidatuo
     * @description 是否打款到销售商
     */
     public function market_brokerage($id){
         //订单详情
         $order_info = get("jy_order","id={$id}&single=true&fields=amount,goods_id");
         //获取配置文件
         $config_info = get("jy_config","single=true&fields=COMM_RATE");
         $res = $order_info['amount'] * (1 - $config_info['COMM_RATE']);
         //服务费
         $service_fee = bcmul($order_info['amount'],$config_info['COMM_RATE'],2);
         //获取销售商信息
         if(!is_null(Cache::get($id))){
             return;
         }else{
             Cache::put($id,1,0.1);
         }
         $goods_info = get("jy_sale_goods","id={$order_info['goods_id']}&fields=openid&single=true");
         DB::beginTransaction();
//         $res = 0.01;
         if($res < 0.01){}else{
             $s['amount'] = $res;
             $s['openid'] = isset($goods_info['openid']) ? $goods_info['openid'] : '';
             $s['order_id'] = $id;
             $result = save("jy_remit_record",$s);
             if($result){
                 $sale_info = get("jy_sale","openid={$s['openid']}&single=true&fields=id,amount,serviceFee");
                 $sale['id'] = isset($sale_info['id']) ? $sale_info['id'] : 0;
                 $sale['amount'] = $sale_info['amount'] + $res;
                 $sale['serviceFee'] = $sale_info['serviceFee'] + $service_fee;
                 $rs = save("jy_sale",$sale);
                 if($rs){
                     DB::commit();
                     DB::table("jy_order")->where("id",$id)->update(['is_s'=>1,'service_fee'=>$service_fee]);
                 }else{
                     DB::rollBack();
                 }
             }else{
                 DB::rollBack();
             }
         }
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取拼单信息(个人参与的)
     */
     public function wxgetOActive(Request $req){
         $params = $req->all();
         if(isset($params['openid']) && NotEstr($params['openid']))
             $data['openid'] = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         $data['state'] = "[gte]1";
         $data['order_by'] = "id desc";
         $rs = get("jy_order",$data);
         $resule = $rs ? $rs : [];
         $res = [];
         foreach ($resule as $item=>$value){
             $goods_info = self::getOrderGoods($value);
             $res[$item]['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
             $res[$item]['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
             $res[$item]['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
             $res[$item]['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
             $res[$item]['dis_price'] = isset($goods_info['dis_price']) ? $goods_info['dis_price'] : 0;
             $res[$item]['end_time'] = isset($goods_info['end_time']) ? $goods_info['end_time'] : '';
             $res[$item]['id'] = isset($goods_info['id']) ? $goods_info['id'] : 0;
             $res[$item]['intro'] = isset($goods_info['intro']) ? $goods_info['intro'] : '';
             $res[$item]['state'] = isset($goods_info['state']) ? $goods_info['state'] : 0;
             $res[$item]['stock'] = isset($goods_info['stock']) ? $goods_info['stock'] : 0;
             $res[$item]['actual_stock'] = isset($goods_info['actual_stock']) ? $goods_info['actual_stock'] : 0;
             $res[$item]['deliver'] = isset($goods_info['deliver']) ? $goods_info['deliver'] : 1;
             $res[$item]['openid'] = $value['openid'];
             $res[$item]['avatarUrls'] = self::getOrderAvatarUrl($value);
         }
         jsonReturn(200,"请求成功",$res);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取拼单人的收货信息
     */
     public function wxgetuserReceiv(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['goods_id'] = $params['id'];
         else
             jsonReturn(201,"无效的goods_id");
         $data['no_limit'] = true;
         $data['state'] = "[gte]1";
         $rs = get("jy_order",$data);
         $resule = $rs ? $rs : [];
         foreach ($resule as $item=>$value){
             if(isset($value['logistics']) && NotEstr($value['logistics']))
                 $logistics = json_decode($value['logistics'],true);
             if(isset($logistics)){
                 $resule[$item]['name'] = isset($logistics['name']) ? $logistics['name'] : '';
                 $resule[$item]['phone'] = isset($logistics['phone']) ? $logistics['phone'] : '';
                 unset($logistics);
             }
         }
         jsonReturn(200,"请求成功",$resule);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 订单发货
     */
     public function wxDeliver(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['is_deliver']) && $params['is_deliver']){

             if(isset($params['name']) && NotEstr($params['name'])
                 && isset($params['phone']) && NotEstr($params['phone'])){

                 if(isset($params['name']) && NotEstr($params['name']))
                     $l['name'] = $params['name'];
                 else
                     jsonReturn(201,"无效的name");
                 if(isset($params['phone']) && NotEstr($params['phone']))
                     $l['phone'] = $params['phone'];
                 else
                     jsonReturn(201,"无效的phone");
                 $data['logistics'] = json_encode($l);
             }else{
                 if(isset($params['express']) && NotEstr($params['express']))
                     $data['express'] = $params['express'];
                 else
                     jsonReturn(201,"无效的express");
                 if(isset($params['express_num']) && isINT($params['express_num']))
                     $data['express_num'] = $params['express_num'];
                 else
                     jsonReturn(201,"无效的express_num");
             }
         }
         if(isset($params['telNumber']) && NotEstr($params['telNumber']))
             $data['telNumber'] = $params['telNumber'];
         $data['state'] = 2;
         $result = save("jy_order",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(201,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 我的钱包
     */
     public function wxWallet(Request $req){
         $params = $req->all();
         if(isset($params['type']) && in_array($params['type'],[0,1,2])){
             switch ($params['type']){
                 case 0:
                     $data['state'] = 0;
                     break;
                 case 1:
                     $data['state'] = 4;
                     break;
                 case 2:
                     $data['state'] = "[in]1,2,3";
                     break;
             }
         }else{
             jsonReturn(201,"无效的type");
         }
         if(isset($params['openid']) && NotEstr($params['openid'])){
             $openid = $params['openid'];
             $s = get("jy_sale","openid={$openid}&fields=amount&single=true");
             $f = get("jy_sale_goods","openid={$openid}&fields=id&no_limit=true");
             if(!$f){
                 $orderlist = [];
                 $amount = 0;
                 $result = compact("orderlist","amount");
                 jsonReturn(200,"请求成功",$result);
             }
             foreach ($f as $v){
                 foreach ($v as $item){
                     $n[] = $item;
                 }
             }
             $data['goods_id'] = "[in]" . implode(",",$n);
         }else{
             jsonReturn(201,"无效的openid");
         }
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         $data['order_by'] = "updated_at desc";
         $rs = get("jy_order",$data);
         unset($data['current_page']);
         unset($data['limit']);
         $data['count'] = "id";
         $total = get("jy_order",$data);
         $orderlist = $rs ? $rs : [];
         foreach ($orderlist as $item=>$value){
             $goods_info = self::getOrderGoods($value);
             $orderlist[$item]['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
             $orderlist[$item]['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
             $orderlist[$item]['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
             $orderlist[$item]['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
             $orderlist[$item]['dis_price'] = isset($goods_info['dis_price']) ? $goods_info['dis_price'] : 0;
             $orderlist[$item]['end_time'] = isset($goods_info['end_time']) ? $goods_info['end_time'] : '';
//             $orderlist[$item]['id'] = isset($goods_info['id']) ? $goods_info['id'] : 0;
             $orderlist[$item]['intro'] = isset($goods_info['intro']) ? $goods_info['intro'] : '';
             $orderlist[$item]['state'] = isset($goods_info['state']) ? $goods_info['state'] : 0;
             $orderlist[$item]['stock'] = isset($goods_info['stock']) ? $goods_info['stock'] : 0;
             $orderlist[$item]['deliver'] = isset($goods_info['deliver']) ? $goods_info['deliver'] : 0;
             //净收入
             $orderlist[$item]['v'] = bcsub($value['amount'],$value['service_fee'],2);
             //获取支付人手机号
             $user_info = get("jy_user","openid={$value['openid']}&single=true&fields=phoneNumber,nickName");
             $orderlist[$item]['pay_phone'] = isset($user_info['phoneNumber']) ? $user_info['phoneNumber'] : '';
             $orderlist[$item]['pay_nickName'] = isset($user_info['nickName']) ? $user_info['nickName'] : '';
         }
         $amount = isset($s['amount']) ? $s['amount'] : 0;

         $result = compact("orderlist","amount","total");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @return mixed
     * @author shidatuo
     * @description 发起支付
     */
     public function wxpayment(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['openid']) && NotEstr($params['openid']))
             $api_key = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
        // $notify_url = "https://shidatuos.cn/api/wxnotifyurl";
         $notify_url = "https://www.jianlelove.com/api/wxnotifyurl";
         $order_info = get("jy_order","id={$data['id']}&single=true&fields=amount,goods_id,num");

//         if(isset($order_info['goods_id']) && isINT($order_info['goods_id'])){
//             $sale_goods_info = get("jy_sale_goods","id={$order_info['goods_id']}&single=true&fields=actual_stock");
//             $sale_goods_info['actual_stock'];
//             if(!$sale_goods_info)
//                 jsonReturn(201,"商品不存在");
//             if(isset($sale_goods_info['actual_stock']) && $sale_goods_info['actual_stock'] <= 0)
//                 jsonReturn(201,"库存不足 , 已售完");
//             if(isset($sale_goods_info['actual_stock']) && $order_info['num'] > $sale_goods_info['actual_stock'])
//                 jsonReturn(202,"库存不足");
//         }

         $total_fee = isset($order_info['amount']) && $order_info['amount'] > 0 ? $order_info['amount'] : 0;
         $total_fee = $total_fee * 100;
         $this->wxpayConfig ['appid'] = 'wx47102dcd005677d3'; // 微信公众号身份的唯一标识
         $this->wxpayConfig ['appsecret'] = 'a3edbe21439b90a1ef0f6132a784ae2a'; // JSAPI接口中获取openid
         $this->wxpayConfig ['mchid'] = '1525958851'; // 受理商ID
         $this->wxpayConfig ['key'] = 'xykjd92c8e4f2df7f54a73c563e24588'; // 商户支付密钥Key
         $this->wxpayConfig ['notifyurl'] = $notify_url;
         $this->wxpayConfig ['returnurl'] = "";
         new WxPayConf ($this->wxpayConfig);
         //订单id @ formid @ 应用id @ openid @ 类型 @ 附加类型 @ 附加数据
         $pkey = $data['id'];// 附加数据
         //$jsApiParameters = $wxPay->getJsApiPayParams($openId, $body, $out_trade_no,$total_fee, $notify_url,$pkey,'123.206.41.185');
         if ($total_fee > 0) {
             // 使用统一支付接口
             $wxQrcodePay = new WxQrcodePay ();
             $wxQrcodePay->setParameter("body", "支付订单費用"); // 商品描述
             $timeStamp = time();
             $out_trade_no = "{$timeStamp}";
             $wxQrcodePay->setParameter("out_trade_no", "$out_trade_no"); // 商户订单号
             $wxQrcodePay->setParameter("body", "商品支付");//附加数据
             $wxQrcodePay->setParameter("spbill_create_ip", "118.24.145.63"); //
             $wxQrcodePay->setParameter("trade_type", "JSAPI"); // 交易类型
             $wxQrcodePay->setParameter("fee_type", "CNY");//附加数据
             $wxQrcodePay->setParameter("total_fee", $total_fee); // 总金额
             $wxQrcodePay->setParameter("openid", $api_key);
             $wxQrcodePay->setParameter("notify_url", $notify_url); // 通知地址
             $wxQrcodePay->setParameter("attach", "$pkey"); // 附加数据
             $wxQrcodePay->SetParameter("input_charset", "UTF-8");
             // 获取统一支付接口结果
             $wxQrcodePayResult = $wxQrcodePay->getResult();
             log_ex("wxpayment",PHP_EOL ."获取统一支付接口结果 : " .PHP_EOL. json_encode($wxQrcodePayResult) . PHP_EOL);
             if (isset($wxQrcodePayResult['prepay_id'])) {
                 $wxQrcodePayResult['package'] = 'prepay_id=' . $wxQrcodePayResult['prepay_id'];
                 $wxQrcodePayResult['timeStamp'] = "{$timeStamp}";
                 $paraMap = array();
                 $paraMap['appId'] = $wxQrcodePayResult['appid'];
                 $paraMap['timeStamp'] = $wxQrcodePayResult['timeStamp'];
                 $paraMap['nonceStr'] = $wxQrcodePayResult['nonce_str'];
                 $paraMap['package'] = $wxQrcodePayResult['package'];
                 $paraMap['signType'] = 'MD5';
                 $buff = "";
                 ksort($paraMap);
                 foreach ($paraMap as $k => $v) {
                     $buff .= $k . "=" . $v . "&";
                 }
                 // 签名步骤二：在string后加入KEY
                 $String = $buff . "key={$this->wxpayConfig ['key']}";
                 // 签名步骤三：MD5加密
                 $String = md5($String);
                 // 签名步骤四：所有字符转为大写
                 $paySign = strtoupper($String);
                 $wxQrcodePayResult['paySign'] = $paySign;
             }
         } else {
             $wxQrcodePayResult['result_code'] = '价格小于0';
         }
         // 商户根据实际情况设置相应的处理流程
         if (isset($wxQrcodePayResult ["return_code"]) && $wxQrcodePayResult ["return_code"] == "FAIL") {
             // 商户自行增加处理流程
             if(isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == 'appid and openid not match'){
                 $wxQrcodePayResult ["return_msg"] = '支付失败,小程序id与openid不匹配';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == 'mch_id参数格式错误'){
                 $wxQrcodePayResult ["return_msg"] = '支付失败,支付id(mch_id)参数格式错误';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '签名错误') {
                 $wxQrcodePayResult ["return_msg"] = '支付失败,请重新设置支付密钥';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '商户号mch_id或sub_mch_id不存在') {
                 $wxQrcodePayResult ["return_msg"] = '支付失败,商户号或子商户号不存在';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '商户号mch_id与appid不匹配') {
                 $wxQrcodePayResult ["return_msg"] = '商户号mch_id与appid不匹配';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '支付失败,商户号或子商户号不存在') {
                 $wxQrcodePayResult ["return_msg"] = '支付失败,商户号或子商户号不存在';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '商户号该产品权限未开通，请前往商户平台>产品中心检查后重试') {
                 $wxQrcodePayResult ["return_msg"] = '商户号该产品权限未开通，请前往商户平台>产品中心检查后重试';
             }elseif (isset($wxQrcodePayResult ["return_msg"]) && $wxQrcodePayResult ["return_msg"] == '受理关系不存在'){
                 $wxQrcodePayResult ["return_msg"] = '受理关系不存在，请前往商户平台检查后重试';
             }else{
                 $wxQrcodePayResult ["return_msg"] = '交易异常,请联系客服';
             }
         }
         if (isset($wxQrcodePayResult['result_code']) && $wxQrcodePayResult['result_code'] == '价格小于0'){
             $wxQrcodePayResult ["return_msg"] = '交易异常,请联系客服';
         }
         $wxQrcodePayResult ["total_fee"] = isset($total_fee) ? $total_fee : 0;
         jsonReturn(200,"请求成功",$wxQrcodePayResult);
     }

    /**
     * @author shidatuo
     * @description 支付回调
     */
     public function wxnotifyurl(){
         log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '---xml--files:--debug---'  . 11111 . PHP_EOL);
         $wxQrcodePay = new WxQrcodePay();
         // 存储微信的回调
         $xml = file_get_contents("php://input");
         log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '---xml--files:--debug---' .$xml . PHP_EOL);
         if (!$xml) $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
         log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '---xml--global:--debug---' .$xml . PHP_EOL);
         $wxQrcodePay->saveData($xml);
         // 验证签名，并回应微信。
         if ($wxQrcodePay->checkSign() == FALSE) {
             $wxQrcodePay->setReturnParameter("return_code", "FAIL"); // 返回状态码
             $wxQrcodePay->setReturnParameter("return_msg", "签名失败"); // 返回信息
         } else {
             $wxQrcodePay->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
         }
         $returnXml = $wxQrcodePay->returnXml();
         log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '---xml--global:--debug---' .$returnXml . PHP_EOL);
         // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
         /** if ($wxQrcodePay->checkSign() == TRUE) {
         if ($wxQrcodePay->data ["return_code"] == "FAIL") {
         // 此处应该更新一下订单状态，商户自行增删操作
         } elseif ($wxQrcodePay->data ["result_code"] == "FAIL") {
         // 此处应该更新一下订单状态，商户自行增删操作
         } else {**/
         //$this->log_ex('log44.txt', date('Y-m-d H:i:s') . '---return_code:--debug---' . json_encode($wxQrcodePay->data["return_code"]) . PHP_EOL);
         // 此处应该更新一下订单状态，商户自行增删操作
         $order = $wxQrcodePay->getData();
         //支付订单
         $attach = explode('@', $order['attach']);
         log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '附加参数 : ' .json_encode($attach) . PHP_EOL);
         if(isset($attach[0]) && isINT($attach[0])){
             log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '订单号 : ' .$attach[0] . PHP_EOL);
             $order_info = get("jy_order","id={$attach[0]}&single=true");//actual_stock
             if(isset($order_info['goods_id']) && isINT($order_info['goods_id'])){
                 $actual = get("jy_sale_goods","id={$order_info['goods_id']}&single=true&fields=actual_stock");
                 if(isset($actual['actual_stock'])){
                     //修改活动状态
//                     if($actual['actual_stock'] == $order_info['num'])
//                         $state = 2;
//                     else
//                         $state = 1;
//                     $s['id'] = $order_info['goods_id'];
//                     $s['state'] = $state;
//                     $s['actual_stock'] = $actual['actual_stock'] - $order_info['num'];
//                     log_ex('wxnotifyurl', date('Y-m-d H:i:s') . '保存商品数据 : ' .json_encode($s) . PHP_EOL);
//                     save("jy_sale_goods",$s);
                 }
             }
             $o['transaction_id'] = isset($order['transaction_id']) ? $order['transaction_id'] : '';
             $o['id'] = $attach[0];
             $o['state'] = 1;
             $o['pay_time'] = date("Y-m-d H:i:s");
             log_ex('wxnotifyurl', date('Y-m-d H:i:s') . PHP_EOL . json_encode($o) . PHP_EOL);
             $rs = save("jy_order",$o);
             if (isset($rs) && $rs) {
                 log_ex('wxnotifyurl', date('Y-m-d H:i:s') . 'echo SUCCESS' . PHP_EOL);
                 echo 'SUCCESS';
             }
         }
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 快递鸟查看物流
     */
     public function wxseeExpress(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $order_info = get("jy_order","id={$data['id']}&fields=express,express_num&single=true");
         if(!$order_info)
             jsonReturn(201,"无效的订单号");
         if(checkEmpty($order_info['express']))
             jsonReturn(201,"无效的物流名称");
         if(checkEmpty($order_info['express_num']) || !isINT($order_info['express_num']))
             jsonReturn(201,"无效的物流单号");
         $kd['codetype'] = $order_info['express'];
         $kd['codeNo'] = $order_info['express_num'];
         $KdNiao = new KdNiaoManager();
         $result = $KdNiao->getOrderTracesByJson($kd);
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 投诉用户api
     */
     public function wxComplaint(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['orderid'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['openid']) && NotEstr($params['openid']))
             $data['openid'] = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         if(isset($params['Reason']) && NotEstr($params['Reason']))
             $data['Reason'] = $params['Reason'];
         else
             jsonReturn(201,"无效的Reason");
         $user_info = get("jy_user","openid={$data['openid']}&single=true&fields=id,nickName,avatarUrl");
         $order_info = get("jy_order","id={$data['orderid']}&single=true&fields=id,goods_id");
         $data['goods_id'] = isset($order_info['goods_id']) ? $order_info['goods_id'] : 0;
         if(isset($user_info['nickName']) && !checkEmpty($user_info['nickName']))
             $data['nickName'] = $user_info['nickName'];
         if(isset($user_info['avatarUrl']) && !checkEmpty($user_info['avatarUrl']))
             $data['avatarUrl'] = $user_info['avatarUrl'];
         $result = save("jy_complaint",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(201,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取订单数量
     */
     public function wxOrderDetaile(Request $req){
         $dsh = $dfh = 0;
         $params = $req->all();
         if(isset($params['openid']) && NotEstr($params['openid']))
             $data['openid'] = $params['openid'];
         else
             jsonReturn(201,"无效的openid");
         $da = $data;
         $da['state'] = 2;
         $data['state'] = 1;
         $dfh = DB::table("jy_order")->where($data)->count();
         $dsh = DB::table("jy_order")->where($da)->count();
         jsonReturn(200,"请求成功",compact("dfh","dsh"));
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 一键发货
     */
     public function wxOnekeyfh(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['goods_id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['state'] = 1;//代发货
         $data['fields'] = "id";//代发货
         $order_list = get("jy_order",$data);
         $rs = $order_list ? $order_list : [];
         if(!is_arr($rs))
             jsonReturn(201,"您没有代发货的订单!!");
         foreach ($rs as $item){
             if(isset($item['id']) && isINT($item['id'])){
                 save("jy_order", "id={$item['id']}&state=2");
             }
         }
         jsonReturn(200,"请求成功");
     }

    /**
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序全局配置
     */
     public function wxgetConfig(){
         $result = get("jy_config","fields=MIN_T_MONEY,COMM_RATE&single=true");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序轮播
     */
     public function wxgetCarousel(){
         $result = get("jy_carousel","no_limit=true&is_delete=0");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 物流列表
     */
     public function wxExpressList(jy_express $exModel){
         jsonReturn(200,"请求成功",$exModel::all());
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取销售商列表
     */
     public function backgetSale(Request $req){
         $params = $req->all();
         if(isset($params['type']) && in_array($params['type'],[0,1,2])){
             $data['status'] = $where = $params['type'];
         }else{
             jsonReturn(201,"无效的type");
         }
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         if(isset($params['phoneNumber']) && NotEstr($params['phoneNumber']))
             $data['phoneNumber'] = "[like]{$params['phoneNumber']}";
         if(isset($params['userName']) && NotEstr($params['userName']))
             $data['userName'] = "[like]{$params['userName']}";
         $rs = get("jy_sale",$data);
         $list = $rs ? $rs : [];
         foreach ($list as $item=>$value){
             //提现
             $withdraw_info = get("jy_withdraw","openid={$value['openid']}&status=[in]0,1&sum=amount");
             //净收入 = 余额 + 提现
             $list[$item]['j'] = bcadd($withdraw_info,$value['amount'],2);
             //总收入 = 余额 + 提现 + 手续费
             $list[$item]['z'] = bcadd($list[$item]['j'],$value['serviceFee'],2);
             //已提现
             $list[$item]['y'] = bcadd(get("jy_withdraw","openid={$value['openid']}&status=1&sum=amount"),0,2);
             //拼单量 + 拼单金额
             $sale_goods = get("jy_sale_goods","openid={$value['openid']}&fields=id&no_limit=true");
             if(count($sale_goods) > 0){
                 $ids = array_column($sale_goods,"id");
                 $ids_str = implode(",",$ids);
                 $order_count = get("jy_order","goods_id=[in]$ids_str&count=id&state=4");
                 $completemoney = get("jy_order","goods_id=[in]$ids_str&sum=amount&state=4");
                 $list[$item]['completenum'] = $order_count;
                 $list[$item]['completemoney'] = round($completemoney,2);
             }else{
                 $list[$item]['completenum'] = 0;
                 $list[$item]['completemoney'] = 0;
             }
         }
         unset($data['current_page']);
         unset($data['limit']);
         $data['count'] = "id";
         //总条数
         $total = get("jy_sale",$data);
         jsonReturn(200,"请求成功",compact("list","total"));
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取销售商详情
     */
     public function backgetSaleInfo(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['single'] = true;
         $rs = get("jy_sale",$data);
         $result = $rs ? $rs : [];
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 审核销售商
     */
     public function backupdataSale(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['status']) && in_array($params['status'],[1,2]))
             $data['status'] = $params['status'];//1通过 2拒绝
         else
             jsonReturn(201,"无效的status");
         if(isset($params['reason']) && in_array($params['reason'],[1,2])){
             $data['reason'] = $params['reason'];//1:身份证照片不清晰 2:身份证号与身份证不匹配
         }else{
             if(isset($data['status']) && $data['status'] == 2)
                 jsonReturn(201,"无效的reason");
         }
         $rs = save("jy_sale",$data);
         if($rs){
             //获取审核人的openid
             $sale_info = get("jy_sale","id={$data['id']}&single=true&fields=openid,phoneNumber");
             if(isset($sale_info['openid']) && !checkEmpty($sale_info['openid']) && $data['status'] == 1){
                 $user_info = get("jy_user","openid={$sale_info['openid']}&single=true&fields=id");
                 if(isset($user_info['id']) && isINT($user_info['id'])){
                     save("jy_user","id={$user_info['id']}&status=2&phoneNumber={$sale_info['phoneNumber']}");
                 }
             }
         }
         jsonReturn(200,"操作成功");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 后台登陆
     */
     public function backLogin(Request $req,jy_token $token_){
         $params = $req->all();
         if(isset($params['userName']) && NotEstr($params['userName']))
             $userName = $data['userName'] = $params['userName'];
         else
             jsonReturn(201,"无效的userName");
         if(isset($params['password']) && NotEstr($params['password']))
             $data['password'] = md5($params['password']);
         else
             jsonReturn(201,"无效的password");
         $d_t = $token_->createToken($data);
         $data['status'] = 1;
         $data['single'] = true;
         $data['fields'] = "id";
         $data['is_delete'] = 0;
         $user_info = get("jy_back_user",$data);
         if(!$user_info)
             jsonReturn(201,"该用户不存在 , 或者用户名密码错误 !!!");
         DB::table("jy_token")->where(['uid'=>$user_info['id']])->update(['status'=>0]);
         save("jy_token","uid={$user_info['id']}&token={$d_t}");
         jsonReturn(200,"操作成功",compact("d_t","userName"));
     }

     /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 修改账户密码
     */
    public function backmodify(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];
        else
            jsonReturn(204,"无效的ID");
        if(isset($params['password']) && NotEstr($params['password']))
            $data['password'] = md5($params['password']);
        else
            jsonReturn(204,"无效的password");
        $result = save("jy_back_user",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(204,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 修改账户密码
     */
    public function closeuser(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];
        else
            jsonReturn(204,"无效的ID");
        if(isset($params['is_delete']) && in_array($params['is_delete'],[0,1]))
            $data['is_delete'] = $params['is_delete'];
        else
            jsonReturn(204,"无效的is_delete");
        $result = save("jy_back_user",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(204,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 退出登录
     */
     public function backSignOut(Request $req){
         $params = $req->all();
         if(isset($params['d_t']) && NotEstr($params['d_t']))
             $data['token'] = $params['d_t'];
         else
             jsonReturn(201,"无效的d_t");
         $data['single'] = true;
         $data['fields'] = 'id';
         $token = get("jy_token",$data);
         if(isset($token['id']) && isINT($token['id'])){
             $s['id'] = $token['id'];
             $s['status'] = 0;
             $rs = save("jy_token",$s);
             if($rs)
                 jsonReturn(200,"退出成功");
             jsonReturn(201,"退出失败");
         }else{
             jsonReturn(201,"无效的d_t");
         }
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 添加账户
     */
     public function backadduser(Request $req){
         $params = $req->all();
         if(isset($params['userName']) && NotEstr($params['userName']))
             $data['userName'] = $params['userName'];
         else
             jsonReturn(204,"无效的userName");
         if(isset($params['password']) && NotEstr($params['password']))
             $data['password'] = md5($params['password']);
         else
             jsonReturn(204,"无效的password");
         if(isset($params['role']) && isINT($params['role'])){
             $data['role'] = $params['role'];
             $rs = get("jy_back_role","id={$data['role']}&single=true&status=1&type=2");
             if(!$rs)
                 jsonReturn(201,"无效的role");
             $data['role_title'] = $rs['title'];
         }else{
             jsonReturn(204,"无效的role");
         }
         $u = get("jy_back_user","userName={$data['userName']}&single=true&fields=id");
         if($u){
             jsonReturn(204,"用户名已存在");
         }
         $result = save("jy_back_user",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(204,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 添加角色 与 权限
     */
     public function backaddjuese(Request $req){
         $params = $req->all();
         if(isset($params['title']) && NotEstr($params['title']))
             $data['title'] = $params['title'];
         else
             jsonReturn(201,"无效的title");
         if(isset($params['type']) && in_array($params['type'],[1,2]))
             $data['type'] = $params['type'];
         else
             jsonReturn(201,"无效的type");
         $result = save("jy_back_role",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(201,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 开启或者关闭账户
     */
     public function backupjuese(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         if(isset($params['status']) && in_array($params['status'],[1,2]))
             $data['status'] = $params['status'];
         else
             jsonReturn(201,"无效的status");
         $result = save("jy_back_role",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(201,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 开启或者关闭账户
     */
     public function backCloseuser(Request $req){
//         $params = $req->all();
//         if(isset($params['id']) && isINT($params['id']))
//             $data['id'] = $params['id'];
//         else
//             jsonReturn(201,"无效的id");
//         if(isset($params['status']) && in_array($params['status'],[1,2]))
//             $data['status'] = $params['status'] > 1 ? 0 : 1;
//         else
//             jsonReturn(201,"无效的status");
//         $result = save("jy_back_user",$data);
//         if($result)
//             jsonReturn(200,"请求成功");
//         jsonReturn(201,"请求失败");
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(204,"无效的ID");
         $data['is_delete'] = 1;
         $result = save("jy_back_user",$data);
         if($result)
             jsonReturn(200,"请求成功");
         jsonReturn(204,"请求失败");
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 返回后台账户列表数据
     */
     public function backgetuser(Request $req){
         $params = $req->all();
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         $data['is_delete'] = 0;
         $rs = get("jy_back_user",$data);
         $list = $rs ? $rs : [];
         unset($data['current_page']);
         unset($data['limit']);
         $data['count'] = "id";
         $total = get("jy_back_user",$data);
//         $total = DB::table("jy_back_role")->where("is_delete",0)->count();
         $result = compact("list","total");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序的用户列表
     */
     public function backgetUserlist(Request $req){
         $params = $req->all();
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         if(isset($params['nickName']) && NotEstr($params['nickName']))
             $data['nickName'] = "[like]" . $params['nickName'];
         $data['order_by'] = "id desc";
         $rs = get("jy_user",$data);
         $list = $rs ? $rs : [];
         unset($data['current_page']);
         unset($data['limit']);
         $data['count'] = "id";
         $total = get("jy_user",$data);
         $result = compact("list","total");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取角色 , 权限列表
     */
     public function backgetjuese(Request $req){
         $params = $req->all();
         if(isset($params['current_page']) && isINT($params['current_page']))
             $data['current_page'] = $params['current_page'];
         else
             $data['current_page'] = 1;
         if(isset($params['limit']) && isINT($params['limit']))
             $data['limit'] = $params['limit'];
         else
             $data['limit'] = 10;
         if(isset($params['type']) && in_array($params['type'],[1,2]))
             $data['type'] = $params['type'];
         else
             jsonReturn(201,"无效的type");
         $rs = get("jy_back_role",$data);
         $list = $rs ? $rs : [];
         unset($data['current_page']);
         unset($data['limit']);
         $data['count'] = "id";
         $total = get("jy_back_role",$data);
         $result = compact("list","total");
         jsonReturn(200,"请求成功",$result);
     }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序用户信息
     */
     public function backgetUserinfo(Request $req){
         $params = $req->all();
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['single'] = true;
         $rs = get("jy_user",$data);
         $result = $rs ? $rs : [];
         jsonReturn(200,"请求成功",$result);
     }



    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序用户订单列表
     */
    public function backgetUserOrderList(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['state']) && in_array($params['state'],[1,4])){
            $data['state'] = $params['state'];
        }else{
            jsonReturn(201,"无效的state");
        }
        $data['no_limit'] = true;
        $rs = get("jy_order",$data);
        $list = $rs ? $rs : [];
        foreach ($list as $item=>$value){
            $goods_info = self::getOrderGoods($value);
            $list[$item]['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
            $list[$item]['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
            $list[$item]['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
            $list[$item]['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
        }
        unset($data['no_limit']);
        $data['count'] = "id";
        $total = get("jy_order",$data);
        $result = compact("list","total");
        jsonReturn(200,"请求成功",$result);
    }



    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序订单列表
     */
    public function backgetOrderList(Request $req){
        $params = $req->all();
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];
        else
            $data['current_page'] = 1;
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        if(isset($params['title']) && NotEstr($params['title']))
            $data['title'] = "[like]{$params['title']}";
        //0：未开始；1:进行中 2:已完成 3:已失败
        if(isset($params['state']) && in_array($params['state'],[1,2,3])){
            $data['state'] = $params['state'];
        }
        $rs = get("jy_sale_goods",$data);
        $list = $rs ? $rs : [];
        foreach ($list as $item=>$value){
            if(isset($value['openid']) && NotEstr($value['openid'])){
                $user_info = get("jy_user","openid={$value['openid']}&single=true&fields=nickName");
                $list[$item]['nickName'] = isset($user_info['nickName']) && !checkEmpty($user_info['nickName']) ? $user_info['nickName'] : '匿名用户';
            }
        }
        unset($data['current_page']);
        unset($data['limit']);
        $data['count'] = "id";
        $total = get("jy_sale_goods",$data);
        $result = compact("list","total");
        jsonReturn(200,"请求成功",$result);
    }


    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取商品的订单详情
     */
    public function backgetOrderinfo(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['goods_id'] = $params['id'];//商品id
        else
            jsonReturn(201,"无效的id");
        //0：未开始；1:进行中 2:已完成 3:已失败
        if(isset($params['state']) && in_array($params['state'],[1,3,4])){
            $data['state'] = $params['state'];
        }else{
            jsonReturn(200,"无效的state");
        }
        $data['no_limit'] = true;
        $rs = get("jy_order",$data);
        $list = $rs ? $rs : [];
        unset($data['no_limit']);
        $data['count'] = "id";
        $total = get("jy_order",$data);
        $result = compact("list","total");
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序投诉列表
     */
    public function backgetcomplaintList(Request $req){
        $params = $req->all();
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];
        else
            $data['current_page'] = 1;
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        $rs = get("jy_complaint",$data);
        $list = $rs ? $rs : [];
        foreach ($list as $k=>$item){
            if(isset($item['goods_id']) && isINT($item['goods_id'])){
                $sale_goods_info = get("jy_sale_goods","id={$item['goods_id']}&single=true&fields=openid");
                if(isset($sale_goods_info['openid']) && !checkEmpty($sale_goods_info['openid'])){
                    $user_info = get("jy_user","openid={$sale_goods_info['openid']}&single=true&fields=nickName,avatarUrl");
                    $list[$k]['nickName_'] = isset($user_info['nickName']) ? $user_info['nickName'] : "";
                    $list[$k]['avatarUrl_'] = isset($user_info['avatarUrl']) ? $user_info['avatarUrl'] : "";
                }
            }
        }
        unset($data['current_page']);
        unset($data['limit']);
        $data['count'] = "id";
        $total = get("jy_complaint",$data);
        $result = compact("list","total");
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取小程序投诉详情
     */
    public function backgetcomplaintinfo(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];//投诉id
        else
            jsonReturn(201,"无效的id");
        $data['single'] = true;
        $result = get("jy_complaint",$data);
        $goods_info = self::getOrderGoods($result);
        $result['pic'] = isset($goods_info['pic']) ? $goods_info['pic'] : '';
        $result['price'] = isset($goods_info['price']) ? $goods_info['price'] : 0;
        $result['title'] = isset($goods_info['title']) ? $goods_info['title'] : '';
        $result['spec'] = isset($goods_info['spec']) ? $goods_info['spec'] : '';
        $sale_goods_info = get("jy_sale_goods","id={$result['goods_id']}&single=true&fields=openid");
        if(isset($sale_goods_info['openid']) && !checkEmpty($sale_goods_info['openid'])){
            $user_info = get("jy_user","openid={$sale_goods_info['openid']}&single=true&fields=nickName,avatarUrl");
            $result['nickName_'] = isset($user_info['nickName']) ? $user_info['nickName'] : "";
            $result['avatarUrl_'] = isset($user_info['avatarUrl']) ? $user_info['avatarUrl'] : "";
        }
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 重置后台密码 12345678
     */
    public function backresetPassword(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];//用户id
        else
            jsonReturn(201,"无效的id");
        $data['password'] = md5(12345678);
        $result = save("jy_back_user",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(200,"请求失败");
    }

    /**
     * @return array
     * @throws \Exception
     * @author shidatuo
     * @description 获取首页信息
     */
    public function backgetIndexData(){
        $wc = get("jy_order","state=4&count=id");
        $jxz = get("jy_order","state=[in]1,2&count=id");
        $pd = compact("wc","jxz");
        //今日订单数
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginToday = date("Y-m-d H:i:s",$beginToday);
        $endToday = date("Y-m-d H:i:s",$endToday);
        $o['state'] = "[gte]1";
        $o['count'] = "id";
        $o['between'] = "created_at|{$beginToday},{$endToday}";
        $dds = get("jy_order",$o);
        //今日订单金额
//        $o['state'] = "[gte]1";
//        $o['sum'] = "amount";
//        $o['between'] = "created_at|{$beginToday},{$endToday}";
//        $je = get("jy_order",$o);
        $je = DB::table("jy_order")
            ->where('state','>=','1')
            ->where('created_at','>',$beginToday)
            ->where('created_at','<',$endToday)
            ->sum("amount");
        $today = compact("dds","je");
        //月订单数
        $start_time = date('Y-m-d',strtotime("-30 days"));
        $end_time = date("Y-m-d H:i:s");
        $s['state'] = "[in]1,2,4";
        $s['count'] = "id";
        $s['between'] = "created_at|{$start_time},{$end_time}";
        $dds = get("jy_order",$s);
        //月订单金额
//        $h['state'] = "[gte]1";
//        $h['sum'] = "amount";
//        $h['between'] = "created_at|{$start_time},{$end_time}";
//        $je = get("jy_order",$h);
        $je = DB::table("jy_order")
            ->where('state','>=','1')
            ->where('state','<>','3')
            ->where('created_at','>',$start_time)
            ->where('created_at','<',$end_time)
            ->sum("amount");
        $month = compact("dds","je");
        $result = ['pd'=>$pd,'jy'=>['today'=>$today,'month'=>$month]];
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 获取后台全局配置
     */
    public function backgetFit(){
        $result = get("jy_config","fields=MIN_T_MONEY,COMM_RATE&single=true");
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 后台设置最小提现金额 , 佣金比例
     */
    public function backsetFit(Request $req){
        $params = $req->except('d_t');
        $data = array_trim($params);
        DB::table("jy_config")->update($data);
        jsonReturn(200,"请求成功");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 后台提现列表接口
     */
    public function backwithdrawList(Request $req){
        $params = $req->all();
        if(isset($params['status']) && in_array($params['status'],[0,1,2]))
            $data['status'] = $params['status'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的status");
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的current_page");
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        $data['order_by'] = "id desc";
        $data['fields'] = "id,openid,amount,status,trasaction_id,failure_reason,bank_card,bank_name,succee_media,created_at,bank_site,bank_payee";
        $res = get("jy_withdraw",$data);
        $list = $res ? $res : [];
        foreach ($list as $item=>$value){
            $userinfo = self::wxgetUserInfo($value['openid']);
            $list[$item]['avatarUrl'] = $userinfo['avatarUrl'];
            $list[$item]['nickName'] = $userinfo['nickName'];
        }
        unset($data['current_page']);
        unset($data['limit']);
        $data['count'] = "id";
        $total = get("jy_withdraw",$data);
        $result = compact("list","total");
        jsonReturn(200,"请求成功",$result);
    }


    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 操作后台提现接口
     */
    public function backhandlewithdraw(Request $req){
        $params = $req->all();
        if(isset($params['status']) && in_array($params['status'],[0,1,2]))
            $data['status'] = $params['status'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(203,"无效的status");
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];
        else
            jsonReturn(203,"无效的id");
        $w = get("jy_withdraw","id={$data['id']}&single=true&fields=amount,openid,status");
        if(!$w)
            jsonReturn(203,"无效的id");
        if(isset($w['status']) && $w['status'] > 0)
            jsonReturn(203,"该提现状态不在待审核状态");
        if(isset($params['status']) && $params['status'] == 1){
            if(isset($params['succee_media']) && NotEstr($params['succee_media']))
                $data['succee_media'] = $params['succee_media'];//0:待审核1:体现通过2:提现失败
            else
                jsonReturn(203,"无效的succee_media");
            if(isset($params['trasaction_id']) && NotEstr($params['trasaction_id']))
                $data['trasaction_id'] = $params['trasaction_id'];//0:待审核1:体现通过2:提现失败
            else
                jsonReturn(203,"无效的trasaction_id");
        }
        if(isset($params['status']) && $params['status'] == 2){
            if(isset($params['failure_reason']) && NotEstr($params['failure_reason']))
                $data['failure_reason'] = $params['failure_reason'];//0:待审核1:体现通过2:提现失败
            else
                jsonReturn(203,"无效的succee_media");
            //把钱还回去
            $sale_info = get("jy_sale","openid={$w['openid']}&single=true&fields=id,amount");
            $s['id'] = $sale_info['id'];
            $s['amount'] = $sale_info['amount'] + $w['amount'];
            save("jy_sale",$s);
        }
        $result = save("jy_withdraw",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(203,"请求失败");
    }


    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 销售商申请提现
     */
    public function wxgetLaunchwithdraw(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['amount']))
            $data['amount'] = $params['amount'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的amount");
        if(isset($params['bank_payee']) && NotEstr($params['bank_payee']))
            $data['bank_payee'] = $params['bank_payee'];
        else
            jsonReturn(201,"无效的bank_payee");
        if(isset($params['bank_site']) && NotEstr($params['bank_site']))
            $data['bank_site'] = $params['bank_site'];
        else
            jsonReturn(201,"无效的bank_site");
        if(isset($params['bank_name']) && NotEstr($params['bank_name']))
            $data['bank_name'] = $params['bank_name'];
        else
            jsonReturn(201,"无效的bank_name");
        if(isset($params['bank_card']) && NotEstr($params['bank_card']))
            $data['bank_card'] = $params['bank_card'];
        else
            jsonReturn(201,"无效的bank_card");
        $sale = get("jy_sale","openid={$data['openid']}&single=true&fields=id,amount");
        if(!$sale)
            jsonReturn(201,"无效的销售商");
        if(isset($sale['amount']) && $sale['amount'] < $data['amount'])
            jsonReturn(201,"余额不足{$data['amount']}");
        DB::beginTransaction();
        $result = save("jy_withdraw",$data);
        if($result){
            $amount = $sale['amount'] - $data['amount'];
            $rs = save("jy_sale","id={$sale['id']}&amount=$amount");
            if($rs){
                DB::commit();
                jsonReturn(200,"请求成功");
            }else{
                DB::rollBack();
                jsonReturn(201,"请求失败");
            }
        }else{
            jsonReturn(201,"请求失败");
        }
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 销售商的提现记录
     */
    public function wxgetWithdrawList(Request $req){
        $params = $req->all();
        if(isset($params['openid']) && NotEstr($params['openid']))
            $data['openid'] = $params['openid'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的openid");
        if(isset($params['status']) && in_array($params['status'],[0,1,2]))
            $data['status'] = $params['status'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的status");
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的current_page");
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        $data['order_by'] = "id desc";
        $data['fields'] = "openid,amount,status,failure_reason,succee_media,bank_card,bank_name,created_at,bank_site,bank_payee";
        $res = get("jy_withdraw",$data);
        $result = $res ? $res : [];
        foreach ($result as $item=>$value){
            $userinfo = self::wxgetUserInfo($value['openid']);
            $result[$item]['avatarUrl'] = $userinfo['avatarUrl'];
            $result[$item]['nickName'] = $userinfo['nickName'];
        }
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 添加(修改)后台轮播
     */
    public function backaddcarousel(Request $req){
        $params = $req->all();
        if(isset($params['title']) && NotEstr($params['title']))
            $data['title'] = $params['title'];//标题
        if(isset($params['description']) && NotEstr($params['description']))
            $data['description'] = $params['description'];//简介
        if(isset($params['content']) && NotEstr($params['content']))
            $data['content'] = $params['content'];//内容
        else
            jsonReturn(201,"无效的content");
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];//ID
        $result = save("jy_carousel",$data);
        if($result)
            jsonReturn(200,"请求成功");
        jsonReturn(204,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 后台轮播列表
     */
    public function backcarouselList(Request $req){
        $params = $req->all();
        if(isset($params['current_page']) && isINT($params['current_page']))
            $data['current_page'] = $params['current_page'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的current_page");
        if(isset($params['limit']) && isINT($params['limit']))
            $data['limit'] = $params['limit'];
        else
            $data['limit'] = 10;
        $data['order_by'] = "id desc";
        $data['is_delete'] = 0;
        $rs = get("jy_carousel",$data);
        $result = $rs ? $rs : [];
//        if($result)
            jsonReturn(200,"请求成功",$result);
//        jsonReturn(201,"请求失败");
    }

    /**
     * @param Request $req
     * @throws \Exception
     * @author shidatuo
     * @description 删除后台轮播
     */
    public function backdeletecarousel(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];//0:待审核1:体现通过2:提现失败
        else
            jsonReturn(201,"无效的id");
        $data['is_delete'] = 1;
        $rs = save("jy_carousel",$data);
        $result = $rs ? $rs : [];
        if($result)
            jsonReturn(200,"请求成功",$result);
        jsonReturn(201,"请求失败");
    }

    /**
     * @throws \Exception
     * @author shidatuo
     * @description 后台提现明细
     */
    public function backIncomedetails(){
        //提现(待审核和审核成功)
        $y = bcadd(get("jy_withdraw","status=1&sum=amount"),0,2);
        //销售商余额
        $k = bcadd(get("jy_sale","sum=amount"),0,2);
        //服务费
        $e = bcadd(get("jy_sale","sum=serviceFee"),0,2);
        $s = bcadd($y,$k,2);
        //总收入 = 服务费 + 余额 + 提现(待审核和审核成功)
        $h = bcadd(get("jy_withdraw","status=[in]0,1&sum=amount"),0,2);
        $w = bcadd($e + $k,$h,2);
        $result = compact("y","k","s","e","w");
        jsonReturn(200,"请求成功",$result);
    }

    /**
     * @param $params
     * @return bool
     * @throws \Exception
     * @author shidatuo
     * @description 订单退款接口
     */
    public function getOrderRefund($params){
        log_ex('getOrderRefund.log',"\n===========进入到订单退款方法  START=============\n======  所有行业都可以调用  ======\n接收到调用退款打印参数 : " . json_encode($params) . "\n" );
        if(isset($params['id']) && $params['id']){
            $data['id'] = $params['id'];
        }else{
            return false;
        }
        $log = "\n接收到的退款订单号为 : [{$data['id']}]";
        $order_info = get("jy_order",$data);
        $refundOrder['refundNo'] = $order_info['id'];//我们的订单id
        $refundOrder['transactionId'] = $order_info['transaction_id'];
        $refundOrder['totalFee'] = (int)((string)($order_info['real_amount'] * 100));
        $refundOrder['refundFee'] = (int)((string)($order_info['real_amount'] * 100)); //微信是以分为单位
        $refundOrder['app_id'] = $order_info['app_id']; //小程序id
        $log .= "\n接收到的退款参数为 : ".json_encode($refundOrder) . PHP_EOL;
        $config['AppId'] = "wx47102dcd005677d3";
        $config['wx_v3_key'] = "xykjd92c8e4f2df7f54a73c563e24588";
        $config['wx_v3_mhcid'] = "1525958851";
//        $config['wx_v3_apiclient_cert_path'] = $appinfo['SSLCERT_PATH'];
//        $config['wx_v3_apiclient_key_path'] = $appinfo['SSLKEY_PATH'];
        $pay = new WxPay($config);
        $totalFee = (int)$refundOrder['totalFee'];//订单金额
        $refundFee = (int)$refundOrder['refundFee'];//退款金额
        $refundNo = $refundOrder['refundNo'];//商户退款单号
        $transactionIdOrOutTradeNo = $refundOrder['transactionId'];//微信订单号
        $return_refundOrder = $pay->refundOrder($totalFee,$refundFee,$refundNo,$transactionIdOrOutTradeNo);
        $log .= "\n调用退款接口返回值为 : ".json_encode($return_refundOrder) . PHP_EOL;
        //返回这个代表请求成功
        if(isset($return_refundOrder['result_code']) && $return_refundOrder['result_code'] == 'SUCCESS'){
            $log .= "\n订单号[{$data['id']}] ------ 退款成功 ------" . PHP_EOL;
            log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
            jsonReturn(200,"退款成功");
        }else{
            $description = isset($return_refundOrder['err_code_des']) ? $return_refundOrder['err_code_des'] : '';
            $log .= "\n订单号[{$data['id']}] ------ 退款失败 {$order_info['real_amount']} (单位:分) " . PHP_EOL;
            $log .= "\n订单号[{$data['id']}] ------ 失败原因 : {$description} " . PHP_EOL;
            log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
            jsonReturn(200,"失败原因:{$description}");
        }
    }


    public function test_(){
        $date = date("Y-m-d H:i:s");
        $result = DB::table("jy_sale_goods")
            ->select("id")
            ->where("end_time","<=",$date) 
            ->where("state","<>",2)
            ->get();
        log_ex('getOrderRefund.log',"\n过期的活动商品 : ".json_encode($result) . PHP_EOL);
        foreach ($result as $v){
            $rs[] = $v->id;
        }
        if(!isset($rs) || (isset($rs) && empty($rs)))
            return;
        $order_list = DB::table("jy_order")->whereIn("goods_id",$rs)->get();
        $log = '';
        log_ex('getOrderRefund.log',"\n订单列表 : ".json_encode($order_list) . PHP_EOL);

        if(count($order_list) > 0){
            foreach ($order_list as $value){
//                    $order_info = get("jy_order","id={$value->id}&single=true&fields=id,transaction_id");
                $order_info = DB::table("jy_order")->select("id","transaction_id","amount")->where(['id'=>$value->id,'is_refund'=>0])->first();
                if(!is_null($order_info)){
                    log_ex('getOrderRefund.log',"\n订单详情不等于null的 : ".json_encode($order_info) . PHP_EOL);
                    $log .= "\n接收到的退款订单号为 : [{$order_info->id}]";
                    $refundOrder['refundNo'] = $order_info->id;//我们的订单id
                    $refundOrder['transactionId'] = $order_info->transaction_id;
                    $refundOrder['totalFee'] = (int)((string)($order_info->amount * 100));
                    $refundOrder['refundFee'] = (int)((string)($order_info->amount * 100)); //微信是以分为单位
                    $log .= "\n接收到的退款参数为 : ".json_encode($refundOrder) . PHP_EOL;
                    $config['AppId'] = "wx47102dcd005677d3";
                    $config['wx_v3_key'] = "xykjd92c8e4f2df7f54a73c563e24588";
                    $config['wx_v3_mhcid'] = "1525958851";
//                $config['wx_v3_apiclient_cert_path'] = $_SERVER['DOCUMENT_ROOT'] . '/cert/apiclient_cert.pem';
                    $config['wx_v3_apiclient_cert_path'] = "/usr/local/nginx/html/api/public/cert/apiclient_cert.pem";
//                $config['wx_v3_apiclient_key_path'] = $_SERVER['DOCUMENT_ROOT'] . '/cert/apiclient_key.pem';
                    $config['wx_v3_apiclient_key_path'] = "/usr/local/nginx/html/api/public/cert/apiclient_key.pem";
                    log_ex('getOrderRefund.log',"\n配置文件 : ".json_encode($config) . PHP_EOL);
                    $pay = new WxPay($config);
                    $totalFee = (int)$refundOrder['totalFee'];//订单金额
                    $refundFee = (int)$refundOrder['refundFee'];//退款金额
                    $refundNo = $refundOrder['refundNo'];//商户退款单号
                    $transactionIdOrOutTradeNo = $refundOrder['transactionId'];//微信订单号
                    $return_refundOrder = $pay->refundOrder($totalFee,$refundFee,$refundNo,$transactionIdOrOutTradeNo);
                    log_ex('getOrderRefund.log',"\n调用退款接口返回值为 : ".json_encode($return_refundOrder) . PHP_EOL);
                }
                //返回这个代表请求成功
                if(isset($return_refundOrder['result_code']) && $return_refundOrder['result_code'] == 'SUCCESS'){
                    $log .= "\n订单号[{$value->id}] ------ 退款成功 ------" . PHP_EOL;
                    log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
//                save("jy_order","id={$value->id}&is_refund=1");
                    DB::table("jy_order")->where(['id'=>$value->id])->update(['is_refund'=>1,'state'=>3]);
                    unset($return_refundOrder['result_code']);
                }else{
//                 $description = isset($return_refundOrder['err_code_des']) ? $return_refundOrder['err_code_des'] : '';
//                 $log .= "\n订单号[{$order_info->id}] ------ 退款失败 {$order_info->amount} (单位:元) " . PHP_EOL;
//                 $log .= "\n订单号[{$order_info->id}] ------ 失败原因 : {$description} " . PHP_EOL;
//                 log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
                }
            }
        }
    }

    /**
     * @param Request $req
     * @author shidatuo
     * @description payment failure 支付失败
     */
    public function wxpaymentCallBack(Request $req){
        $params = $req->all();
        if(isset($params['id']) && isINT($params['id']))
            $data['id'] = $params['id'];//订单id
        else
            jsonReturn(201,"无效的id");
        log_ex('wxpaymentCallBack.log',"\n调用退款接口返回值为 : ".json_encode($params) . PHP_EOL);
        $o_list = DB::table("jy_order")->where(['is_back_stock'=>0,'state'=>0,'id'=>$data['id']])->where("transaction_id","<>","")->get();
        if(count($o_list) > 0){
            foreach ($o_list as $item=>$value){
                DB::table('jy_sale_goods')->where(['id'=>$value->goods_id])->increment('actual_stock',$value->num,['state'=>1]);
                DB::table('jy_order')->where(["id"=>$value->id])->update(['is_back_stock'=>1]);
            }
        }
        jsonReturn(200,"请求成功");
    }


    /**
     * @param $params
     * @return array|bool|\Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     * @author shidatuo
     * @description 获取拼单人头像
     */
     public function getOrderAvatarUrl($params){
         if(isset($params['id']) && isINT($params['id']))
             $data['id'] = $params['id'];
         else
             jsonReturn(201,"无效的id");
         $data['fields'] = "goods_id";
         $data['single'] = true;
         $rs = get("jy_order",$data);
         if(!$rs || !isset($rs['goods_id']))
             jsonReturn(201,"获取失败");
         $result = get("jy_order","goods_id={$rs['goods_id']}&fields=avatarUrl&state=[gte]1");
         return $result ? $result : [];
     }

    /**
     * @param $params
     * @return array|bool|\Illuminate\Database\Eloquent\Collection|static[]
     * @throws \Exception
     * @author shidatuo
     * @description 获取拼单商品信息
     */
     public function getOrderGoods($params){
         if(isset($params['goods_id']) && isINT($params['goods_id']))
             $data['id'] = $params['goods_id'];
         else
             jsonReturn(201,"无效的id");
         $result = get("jy_sale_goods","id={$data['id']}&single=true");
         return $result ? $result : [];
     }
}
######################################################################################################################################################


class UrlManager {
    public $site_url_var;
    public $current_url_var;

    public function site($add_string = false) {
        return $this->site_url($add_string);
    }

    /**
     * @return mixed
     * @author shidatuo
     * @description 获取主机名称
     */
    public function hostname() {
        static $u1;
        if ($u1==false){
            $valid_domain = parse_url($this->site_url());
            if (isset($valid_domain['host'])){
                $host = str_ireplace('www.', null, $valid_domain['host']);
                $u1 = $host;
            }
        }
        return $u1;
    }

    public function link_to_file($path) {
        $path = str_ireplace(XN_ROOTPATH, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace(XN_ROOTPATH, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace(XN_ROOTPATH, '', $path);
        $path = str_ireplace($this->site_url(), '', $path);

        if (function_exists('base_path')){
            $replace_file = base_path();
        } else {
            $replace_file = @dirname(dirname(dirname(__FILE__)));
        }

        $path = str_ireplace($replace_file, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = ltrim($path, '/');
        $path = ltrim($path, '\\');

        return $this->site_url($path);
    }

    public function set($url = false) {
        return $this->site_url_var = ($url);
    }

    public function set_current($url = false) {
        return $this->current_url_var = ($url);
    }

    public function to_path($path) {
        if (trim($path)==''){
            return false;
        }
        $path = str_ireplace($this->site_url(), XN_ROOTPATH, $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);

        return normalize_path($path, false);
    }

    public function redirect($url) {
        if (trim($url)==''){
            return false;
        }
        $url = str_ireplace('Location:', '', $url);
        $url = trim($url);

        if (headers_sent()){
            // echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
        } else {
            //  dump($url);
            return \Redirect::to($url);

            //  return;
        }
    }

    public function params($skip_ajax = false) {
        return $this->param($param = '__XN_GET_ALL_PARAMS__', $skip_ajax);
    }


    /**
     * @param $param
     * @param bool $skip_ajax
     * @param bool $force_url
     * @return array|bool|mixed|string
     */
    public function param($param, $skip_ajax = false, $force_url = false) {
        if ($_POST){
            if (isset($_POST['search_by_keyword'])){
                if ($param=='keyword'){
                    return $_POST['search_by_keyword'];
                }
            }
        }
        $url = $this->current($skip_ajax);
        if ($force_url!=false){
            $url = $force_url;
        }
        $rem = $this->site_url();
        $url = str_ireplace($rem, '', $url);
        $url = str_ireplace('?', '/', $url);
        $url = str_ireplace('=', ':', $url);
        $url = str_ireplace('&', '/', $url);
        $all_params = array();
        $segs = explode('/', $url);
        foreach ($segs as $segment) {
            $seg1 = explode(':', $segment);
            if ($param=='__XN_GET_ALL_PARAMS__'){
                if (isset($seg1[0]) and isset($seg1[1])){
                    $all_params[ $seg1[0] ] = $seg1[1];
                }
            } else {
                $param_sub_position = false;
                if (trim($seg1[0])==trim($param)){
                    if ($param_sub_position==false){
                        $the_param = str_ireplace($param . ':', '', $segment);
                        if ($param=='content_fields_criteria'){
                            $the_param1 = $this->app->format->base64_to_array($the_param);

                            return $the_param1;
                        }

                        return $the_param;
                    } else {
                        $the_param = str_ireplace($param . ':', '', $segment);
                        $params_list = explode(',', $the_param);
                        if ($param=='content_fields_criteria'){
                            $the_param1 = base64_decode($the_param);
                            $the_param1 = unserialize($the_param1);

                            return $the_param1;
                        }

                        return $the_param;
                    }
                }
            }
        }

        if (empty($all_params)){
            return false;
        }

        return $all_params;
    }



    public function param_set($param, $value = false, $url = false) {
        if ($url==false){
            $url = $this->string();
        }
        $site = $this->site_url();
        $url = str_ireplace($site, '', $url);
        $segs = explode('/', $url);
        $segs_clean = array();
        $found = false;
        foreach ($segs as $segment) {
            $origsegment = ($segment);
            $segment = explode(':', $segment);
            if ($segment[0]==$param){
                $segment[1] = $value;

                $origsegment = implode(':', $segment);
                $found = true;
                $segs_clean[] = $origsegment;
            } else {
                $segs_clean[] = $origsegment;
            }
        }

        if ($found==false){

            $segment = array();
            $segment[] = $param;
            $segment[] = $value;
            $origsegment = implode(':', $segment);
            $segs_clean[] = $origsegment;

        }

        $segs_clean = implode('/', $segs_clean);
        $site = ($segs_clean);

        return $site;
    }
    public function param_unset($param, $url = false) {
        if ($url==false){
            $url = $this->string();
        }
        $site = $this->site_url();
        $url = str_ireplace($site, '', $url);
        $segs = explode('/', $url);
        $segs_clean = array();
        foreach ($segs as $segment) {
            $origsegment = ($segment);
            $segment = explode(':', $segment);
            if ($segment[0]==$param){
            } else {
                $segs_clean[] = $origsegment;
            }
        }
        $segs_clean = implode('/', $segs_clean);
        $site = ($segs_clean);

        return $site;
    }
    /**
     * Returns the current url path, does not include the domain name.
     *
     * @param bool $skip_ajax If true it will try to get the referring url from ajax request
     *
     * @return string the url string
     */
    public function string($skip_ajax = false) {
        if ($skip_ajax==true){
            $url = $this->current($skip_ajax);
        } else {
            $url = false;
        }
        $u1 = implode('/', $this->segment(- 1, $url));
        return $u1;
    }
    /**
     * Returns the current url as a string.
     *
     * @param bool $skip_ajax If true it will try to get the referring url from ajax request
     * @param bool $no_get    If true it will remove the params after '?'
     *
     * @return string the url string
     */
    public function current($skip_ajax = false, $no_get = false) {
        $u = false;
        if ($skip_ajax==true){
            $is_ajax = $this->is_ajax();
            if ($is_ajax==true){
                if ($_SERVER['HTTP_REFERER']!=false){
                    $u = $_SERVER['HTTP_REFERER'];
                }
            }
        }
        if ($u==false and $this->current_url_var!=false){
            $u = $this->current_url_var;
        }
        if ($u==false){
            if (!isset($_SERVER['REQUEST_URI'])){
                $serverrequri = $_SERVER['PHP_SELF'];
            } else {
                $serverrequri = $_SERVER['REQUEST_URI'];
            }
            $s = '';
            if(is_https()){
                $s = 's';
            }
            $protocol = 'http';
            $port = 80;
            if (isset($_SERVER['SERVER_PROTOCOL'])){
                $protocol = $this->strleft(strtolower($_SERVER['SERVER_PROTOCOL']), '/') . $s;
            }
            if (isset($_SERVER['SERVER_PORT'])){
                $port = ($_SERVER['SERVER_PORT']=='80' || $_SERVER['SERVER_PORT']=='443') ? '' : (':' . $_SERVER['SERVER_PORT']);
            }

            if (isset($_SERVER['SERVER_PORT']) and isset($_SERVER['HTTP_HOST'])){
                if (strstr($_SERVER['HTTP_HOST'], ':')){
                    // port is contained in HTTP_HOST
                    $u = $protocol . '://' . $_SERVER['HTTP_HOST'] . $serverrequri;
                } else {
                    $u = $protocol . '://' . $_SERVER['HTTP_HOST'] . $port . $serverrequri;
                }
            } elseif (isset($_SERVER['HOSTNAME'])) {
                $u = $protocol . '://' . $_SERVER['HOSTNAME'] . $port . $serverrequri;
            }


        }

        if ($no_get==true){
            $u = strtok($u, '?');
        }
        if (is_string($u)){
            $u = str_replace(' ', '%20', $u);
        }

        return $u;
    }

    /**
     * Return true if the current request is via ajax.
     *
     * @return true|false
     */
    public function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');
    }

    public function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    /**
     * Returns single URL segment.
     *
     * @param      $num      The segment number
     * @param bool $page_url If false it will use the current URL
     *
     * @return string|false the url segment or false
     */
    public function segment($num = - 1, $page_url = false) {
        $u = false;
        if ($page_url==false or $page_url==''){
            $current_url = $this->current();
        } else {
            $current_url = $page_url;
        }
        $site_url = $this->site_url();
        $site_url = rtrim($site_url, '\\');
        $site_url = rtrim($site_url, '/');
        $site_url = reduce_double_slashes($site_url);
        $site_url = rawurldecode($site_url);

        $current_url = rtrim($current_url, '\\');
        $current_url = rtrim($current_url, '/');
        $current_url = rawurldecode($current_url);
        $current_url = str_replace($site_url, '', $current_url);
        $current_url = str_replace(' ', '%20', $current_url);
        $current_url = reduce_double_slashes($current_url);

        if (!isset($u) or $u==false){
            $u = explode('/', trim(preg_replace('/([^\w\:\-\.\%\/])/i', '', current(explode('?', $current_url, 2))), '/'));
            if (isset($u[0])){
                //check for port
                $string = substr($u[0], 0, 1);
                if ($string==':'){
                    unset($u[0]);
                    $u = array_values($u);
                }
            }
        }

        if ($num!=- 1){
            if (isset($u[ $num ])){
                return $u[ $num ];
            } else {
                return;
            }
        } else {
            return $u;
        }
    }

    public function site_url($add_string = false) {
        return site_url($add_string);
    }

    /**
     * Returns ALL URL segments as array.
     *
     * @param bool $page_url If false it will use the current URL
     *
     * @return array|false the url segments or false
     */
    public function segments($page_url = false) {
        return $this->segment($k = - 1, $page_url);
    }

    /**
     * 过滤转换别名
     * @param $text
     * @return mixed|string
     */
    public function slug($text) {
        // Swap out Non "Letters" with a -
        $text = str_replace('&quot;', '-', $text);
        $text = str_replace('&#039;', '-', $text);
        $text = preg_replace('/[^\\pL\d]+/u', '-', $text);
        // Trim out extra -'s
        $text = trim($text, '-');
        $text = str_replace('""', '-', $text);
        $text = str_replace("'", '-', $text);

        $text = URLify::filter($text);
        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^-\w]+/', '', $text);
        $text = str_replace(':', '-', $text);

        return $text;
    }

    /**
     * 下载地址
     * @param $requestUrl
     * @param bool $post_params
     * @param bool $save_to_file
     * @return bool|mixed|string
     */
    public function download($requestUrl, $post_params = false, $save_to_file = false) {
        if ($post_params!=false and is_array($post_params)){
            $postdata = http_build_query($post_params);
        } else {
            $postdata = false;
        }
        $ref = site_url();

        $opts = array('http' => array('method' => 'POST', 'header' => 'User-Agent: Xiaonr/' . XN_VERSION . "\r\n" . 'Content-type: application/x-www-form-urlencoded' . "\r\n" . 'Referer: ' . $ref . "\r\n", 'content' => $postdata));
        $requestUrl = str_replace(' ', '%20', $requestUrl);

        if (function_exists('curl_init')){
            $ch = curl_init($requestUrl);
            curl_setopt($ch, CURLOPT_COOKIEJAR, xn_cache_path() . 'global/cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, xn_cache_path() . 'global/cookie.txt');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Xiaonr ' . XN_VERSION . ';)');
            if ($post_params!=false){
                curl_setopt($ch, CURLOPT_POST, count($post_params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
            }
            //	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            //curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            $result = curl_exec($ch);

            curl_close($ch);
        } else {
            $context = stream_context_create($opts);
            $result = file_get_contents($requestUrl, false, $context);
        }

        if ($save_to_file==true){
            file_put_contents($save_to_file, $result);
        } else {
            return $result;
        }

        return false;
    }

    public function replace_site_url($arr) {
        $site = $this->site_url();
        if (is_string($arr)){
            $ret = str_ireplace($site, '{SITE_URL}', $arr);

            return $ret;
        }
        if (is_array($arr) and !empty($arr)){
            $ret = array();
            foreach ($arr as $k => $v) {
                if (is_array($v) and !empty($v)){
                    $v = $this->replace_site_url($v);
                } elseif (is_string($v)) {
                    $v = str_ireplace($site, '{SITE_URL}', $v);
                }
                $ret[ $k ] = $v;
            }

            return $ret;
        }
        return $arr;
    }

    public $repaced_urls = array();

    public function replace_site_url_back($arr) {
        if ($arr==false){
            return;
        }

        if (is_string($arr)){
            $parser_mem_crc = 'replace_site_vars_back_' . crc32($arr);
            if (isset($this->repaced_urls[ $parser_mem_crc ])){
                $ret = $this->repaced_urls[ $parser_mem_crc ];
            } else {
                $site = site_url_img();
                $ret = str_replace('{SITE_URL}', $site, $arr);
                $this->repaced_urls[ $parser_mem_crc ] = $ret;
            }

            return $ret;
        }

        if (is_array($arr) and !empty($arr)){
            $ret = array();
            foreach ($arr as $k => $v) {
                $parser_mem_crc = 'replace_site_vars_back_' . crc32(serialize($k).serialize($v));
                if (isset($this->repaced_urls[ $parser_mem_crc ])){
                    $ret[ $k ] = $this->repaced_urls[ $parser_mem_crc ];
                } else {
                    if (is_array($v)){
                        $v = $this->replace_site_url_back($v);
                    } elseif (is_string($v) and $v!=='0') {
                        $v = $this->replace_site_url_back($v);
                    }
                    $this->repaced_urls[ $parser_mem_crc ] = $v;

                    $ret[ $k ] = $v;
                }

            }

            return $ret;
        }
    }

    public function api_link($str = '') {
        $str = ltrim($str, '/');

        return $this->site_url('api/' . $str);
    }
}

/**
 * A PHP port of URLify.js from the Django project
 * (https://github.com/django/django/blob/master/django/contrib/admin/static/admin/js/urlify.js).
 * Handles symbols from Latin languages, Greek, Turkish, Russian, Ukrainian,
 * Czech, Polish, and Latvian. Symbols it cannot transliterate
 * it will simply omit.
 *
 * Usage:
 *
 * echo URLify::filter (' J\'étudie le français ');
 * // "jetudie-le-francais"
 *
 * echo URLify::filter ('Lo siento, no hablo español.');
 * // "lo-siento-no-hablo-espanol"
 */
class URLify {
    public static $maps = array('latin_map' => array('À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y'), 'latin_symbols_map' => array('©' => '(c)'), 'greek_map' => array('α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w', 'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's', 'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i', 'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W', 'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I', 'Ϋ' => 'Y'), 'turkish_map' => array('ş' => 's', 'Ş' => 'S', 'ı' => 'i', 'İ' => 'I', 'ç' => 'c', 'Ç' => 'C', 'ü' => 'u', 'Ü' => 'U', 'ö' => 'o', 'Ö' => 'O', 'ğ' => 'g', 'Ğ' => 'G'), 'russian_map' => array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'), 'ukrainian_map' => array('Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G', 'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g'), 'czech_map' => array('č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u', 'ž' => 'z', 'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 'Ž' => 'Z'), 'polish_map' => array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'), 'latvian_map' => array('ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n', 'š' => 's', 'ū' => 'u', 'ž' => 'z', 'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z'), 'vietnamese_map' => array('à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u', 'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e', 'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'đ' => 'd', 'Đ' => 'D'));
    /**
     * List of words to remove from URLs.
     */
    public static $remove_list = array();
    /**
     * The character map.
     */
    private static $map = array();
    /**
     * The character list as a string.
     */
    private static $chars = '';
    /**
     * The character list as a regular expression.
     */
    private static $regex = '';

    /**
     * Add new characters to the list.
     * `$map` should be a hash.
     */
    public static function add_chars($map) {
        if (!is_array($map)){
            throw new LogicException('$map must be an associative array.');
        }
        self::$maps[] = $map;
        self::$map = array();
        self::$chars = '';
    }
    /**
     * Append words to the remove list.
     * Accepts either single words
     * or an array of words.
     */
    public static function remove_words($words) {
        $words = is_array($words) ? $words : array($words);
        self::$remove_list = array_merge(self::$remove_list, $words);
    }
    /**
     * Filters a string, e.g., "Petty theft" to "petty-theft".
     */
    public static function filter($text, $length = 60) {
        $text = self::downcode($text);

        // remove all these words from the string before urlifying
        $text = preg_replace('/\b(' . implode('|', self::$remove_list) . ')\b/i', '', $text);

        // if downcode doesn't hit, the char will be stripped here
        $text = preg_replace('/[^-\w\s]/', '', $text);
        // remove unneeded chars
        $text = preg_replace('/^\s+|\s+$/', '', $text);
        // trim
        // leading/trailing
        // spaces
        $text = preg_replace('/[-\s]+/', '-', $text);
        // convert spaces to
        // hyphens
        $text = strtolower($text);

        // convert to lowercase
        return trim(substr($text, 0, $length), '-');
        // trim to first
        // $length
        // chars
    }
    /**
     * Alias of `URLify::downcode()`.
     */
    public static function transliterate($text) {
        return self::downcode($text);
    }

    /**
     * Transliterates characters to their ASCII equivalents.
     */
    public static function downcode($text) {
        self::init();
        if (preg_match_all(self::$regex, $text, $matches)){
            for ($i = 0; $i < count($matches[0]); ++ $i) {
                $char = $matches[0][ $i ];
                if (isset(self::$map[ $char ])){
                    $text = str_replace($char, self::$map[ $char ], $text);
                }
            }
        }
        return $text;
    }
    /**
     * Initializes the character map.
     */
    private static function init() {
        if (count(self::$map) > 0){
            return;
        }
        foreach (self::$maps as $map) {
            foreach ($map as $orig => $conv) {
                self::$map[ $orig ] = $conv;
                self::$chars .= $orig;
            }
        }
        self::$regex = '/[' . self::$chars . ']/u';
    }
}



class KdNiaoManager{

    public $EBusinessID;
    public $AppKey;
    public $app;

    public function __construct($app = null){
        if (!is_object($this->app)){
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = xn();
            }
        }
        $this->EBusinessID = '1437692'; //用户ID
        $this->AppKey = 'c638fcca-1057-4ea5-a7fb-3ac0a5c6e607'; //API key
    }

    /**
     * @return url响应返回的html
     * @author shidatuo
     * @description 测试电子面单
     */
    function get_v3_electronic_order($params){
        if(isset($params['id']) && $params['id'] != false){
            $id = $params['id'];
        }else{
            return ["errcode"=>70001,"errmsg"=>"保存参数缺失,缺少必填参数id"];
        }
        if(isset($params['id']) && $params['id'] != false){
            $id = $params['id'];
        }else{
            return ["errcode"=>70001,"errmsg"=>"保存参数缺失,缺少必填参数id"];
        }
        $order_info = xn()->order_manager->get_order_info(['id'=>$id]);
        if(!$order_info){
            return ["errcode"=>70111,"errmsg"=>"订单号不存在"];
        }
        //构造电子面单提交信息
        $receiver = $commodity = $commodityOne = $sender = $eorder = [];

        $eorder["ShipperCode"] = "SF";//快递公司编码
        $eorder["OrderCode"] = "012657700387";//订单编号
        $eorder["PayType"] = 1;//邮费支付方式:1-现付，2-到付，3-月结，4-第三方支付
        $eorder["ExpType"] = 1;//快递类型：1-标准快件
        $eorder["ThrOrderCode"] = 1;//第三方订单号
        $eorder["Cost"] = 1;//寄件费(运费)
        $eorder["OtherCost"] = 0;//其他费用

        /* 收货人信息 start */
        $receiver["Name"] = "李先生";//收件人
        $receiver["Mobile"] = "18888888888";//电话与手机，必填一个
        $receiver["ProvinceName"] = "广东省";//收件省（如广东省，不要缺少“省”
        $receiver["CityName"] = "深圳市";//收件市（如深圳市，不要缺少“市”）
        $receiver["ExpAreaName"] = "福田区";//收件区（如福田区，不要缺少“区”或“县”）
        $receiver["Address"] = "赛格广场5401AB";//收件人详细地址
        $eorder["Receiver"] = $receiver;
        /* 收货人信息 end */

        /* 发件人信息 start */
        $sender["Name"] = "李先生";//发件人
        $sender["Mobile"] = "18888888888";
        $sender["ProvinceName"] = "李先生";
        $sender["CityName"] = "深圳市";
        $sender["ExpAreaName"] = "福田区";
        $sender["Address"] = "赛格广场5401AB";
        $eorder["Sender"] = $sender;
        /* 发件人信息 end */

        /* 其他信息 start */
        $eorder['IsNotice'] = 1;//是否通知快递员上门揽件：0-通知；1-不通知；不填则默认为1
        $eorder['Weight'] = 1;//物品总重量kg
        $eorder['Quantity'] = 1;//件数/包裹数
        $eorder['Volume'] = 1;//物品总体积m3
        $eorder['Remark'] = "";//备注

        /* 商品信息 start */
        $commodityOne["GoodsName"] = "其他";//商品名称
        $commodityOne["Goodsquantity"] = 1;//商品数量
        $commodityOne["GoodsPrice"] = 1;//商品价格
        $eorder["Commodity"][] = $commodityOne;
        /* 商品信息 end */

        $eorder['IsReturnPrintTemplate'] = 1;//返回电子面单模板：0-不需要；1-需要
        $eorder['IsSendMessage'] = 1;//是否订阅短信：0-不需要；1-需要
        $eorder['TemplateSize'] = 180;//模板规格(默认的模板无需传值，非默认模板传对应模板尺寸)
        /* 其他信息 end */
        //调用电子面单
        $jsonParam = json_encode($eorder,JSON_UNESCAPED_UNICODE);
        //$jsonParam = JSON($eorder);//兼容php5.2（含）以下
        // echo "电子面单接口提交内容：<br/>".$jsonParam;
        $jsonResult = $this->submitEOrder($jsonParam);
        return $jsonResult;
        dd();
        echo "<br/><br/>电子面单提交结果:<br/>".$jsonResult;

        //解析电子面单返回结果
        $result = json_decode($jsonResult, true);
        echo "<br/><br/>返回码:".$result["ResultCode"];
        if($result["ResultCode"] == "100") {
            echo "<br/>是否成功:".$result["Success"];
        }
        else {
            echo "<br/>电子面单下单失败";
        }
    }

    /**
     * getOrderTracesByJson
     *
     * @desc        查询订单物流轨迹
     *
     * @author      yuluo
     *
     * @param $params['codetype']           Required             快递单号
     * @param $params['codeNo']             Required             快递编码
     * @param $params['orderid']            Not Required         订单号
     * @return json
     */
    function getOrderTracesByJson($params){
        //快递单号
        $codetype = $params['codetype'];
        //快递编码
        $codeNo = $params['codeNo'];
        $ReqURL = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx"; //正式
        $requestData = "{'OrderCode':'','ShipperCode':'".$codetype."','LogisticCode':'".$codeNo."'}";
        $datas = array(
            'EBusinessID' =>  $this->EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] =  $this->encrypt($requestData,  $this->AppKey);
        $result = $this->sendPost($ReqURL, $datas);
        $guiji = json_decode($result,true);
        if(isset($params['id']) && isINT($params['id'])) {
            if (isset($guiji['State']) && $guiji['State'] == 3) {
                $data['logistics'] = $result;
                $data['id'] = $params['id'];
                //更新快递信息
                save("jy_order",$data);
            }
        }
        return $result;
    }


    /**
     * Json方式 调用电子面单接口
     */
    function submitEOrder($requestData){
//        $ReqURL = "http://testapi.kdniao.cc:8080/api/EOrderService"; //测试
        $ReqURL = "http://sandboxapi.kdniao.cc:8080/kdniaosandbox/gateway/exterfaceInvoke.json"; //测试
        //	$ReqURL = "http://api.kdniao.cc/api/Eorderservice"; //正式
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
//        dd($ReqURL,$datas);
        $result=$this->sendPost($ReqURL, $datas);
        //根据公司业务处理返回的信息......
        return $result;
    }

    /**
     * Json方式  物流信息订阅
     */
    function orderTracesSubByJson(){

        $ReqURL = "http://testapi.kdniao.cc:8081/api/dist"; //测试
        //	$ReqURL = "http://api.kdniao.cc/api/dist"; //正式

        $requestData="{'OrderCode': 'SF201608081055208281',".
            "'ShipperCode':'SF',".
            "'LogisticCode':'3100707578976',".
            "'PayType':1,".
            "'ExpType':1,".
            "'IsNotice':0,".
            "'Cost':1.0,".
            "'OtherCost':1.0,".
            "'Sender':".
            "{".
            "'Company':'LV','Name':'Taylor','Mobile':'15018442396','ProvinceName':'上海','CityName':'上海','ExpAreaName':'青浦区','Address':'明珠路73号'},".
            "'Receiver':".
            "{".
            "'Company':'GCCUI','Name':'Yann','Mobile':'15018442396','ProvinceName':'北京','CityName':'北京','ExpAreaName':'朝阳区','Address':'三里屯街道雅秀大厦'},".
            "'Commodity':".
            "[{".
            "'GoodsName':'鞋子','Goodsquantity':1,'GoodsWeight':1.0}],".
            "'Weight':1.0,".
            "'Quantity':1,".
            "'Volume':0.0,".
            "'Remark':'小心轻放'}";


        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1008',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $result=$this->sendPost($ReqURL, $datas);

        //根据公司业务处理返回的信息......

        return $result;
    }



    /**
     * Json方式 单号识别
     */
//    function getOrderTracesByJson(){
//
//        var_dump('sssssssssss');
//        //$ReqURL = "http://testapi.kdniao.cc:8081/Ebusiness/EbusinessOrderHandle.aspx"; //测试
//        $ReqURL = "http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx"; //正式
//
//        $requestData= "{'LogisticCode':'600432190772'}";
//        $datas = array(
//            'EBusinessID' => $this->EBusinessID,
//            'RequestType' => '2002',
//            'RequestData' => urlencode($requestData) ,
//            'DataType' => '2',
//        );
//        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
//        $result=$this->sendPost($ReqURL, $datas);
//
//        //根据公司业务处理返回的信息......
//
//        return $result;
//    }



    /**
     * post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], isset($url_info['port']) ? $url_info['port'] : 80);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);
        return $gets;
    }


    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }


    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }


    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

    function getcode($getcom)
    {
        switch ($getcom) {
            case "EMS"://后台中显示的快递公司名称
                $postcom = 'EMS';//快递公司代码
                break;
            case "中通速递":
                $postcom = 'ZTO';
                break;
            case "7天连锁物流":
                $postcom = '7TLSWL';
                break;
            case "安捷快递":
                $postcom = 'AJ';
                break;
            case "安能物流":
                $postcom = 'ANE';
                break;
            case "安信达快递":
                $postcom = 'AXD';
                break;
            case "巴伦支快递":
                $postcom = 'BALUNZHI';
                break;
            case "百福东方":
                $postcom = 'BFDF';
                break;
            case "宝凯物流":
                $postcom = 'BKWL';
                break;
            case "北青小红帽":
                $postcom = 'BQXHM';
                break;
            case "邦送物流":
                $postcom = 'BSWL';
                break;
            case "百世物流":
                $postcom = 'BTWL';
                break;
            case "CCES快递":
                $postcom = 'CCES';
                break;
            case "城市100":
                $postcom = 'CITY100';
                break;
            case "COE东方快递":
                $postcom = 'COE';
                break;
            case "长沙创一":
                $postcom = 'CSCY';
                break;
            case "传喜物流":
                $postcom = 'CXWL';
                break;
            case "德邦":
                $postcom = 'DBL';
                break;
            case "德创物流":
                $postcom = 'DCWL';
                break;
            case "东红物流":
                $postcom = 'DHWL';
                break;
            case "D速物流":
                $postcom = 'DSWL';
                break;
            case "店通快递":
                $postcom = 'DTKD';
                break;
            case "大田物流":
                $postcom = 'DTWL';
                break;
            case "大洋物流快递":
                $postcom = 'DYWL';
                break;
            case "快捷速递":
                $postcom = 'FAST';
                break;
            case "飞豹快递":
                $postcom = 'FBKD';
                break;
            case "FedEx联邦快递":
                $postcom = 'FEDEX';
                break;
            case "飞狐快递":
                $postcom = 'FHKD';
                break;
            case "飞康达":
                $postcom = 'FKD';
                break;
            case "飞远配送":
                $postcom = 'FYPS';
                break;
            case "凡宇速递":
                $postcom = 'FYSD';
                break;
            case "广东邮政":
                $postcom = 'GDEMS';
                break;
            case "冠达快递":
                $postcom = 'GDKD';
                break;
            case "挂号信":
                $postcom = 'GHX';
                break;
            case "港快速递":
                $postcom = 'GKSD';
                break;
            case "共速达":
                $postcom = 'GSD';
                break;
            case "广通速递":
                $postcom = 'GTKD';
                break;
            case "国通快递":
                $postcom = 'GTO';
                break;
            case "高铁速递":
                $postcom = 'GTSD';
                break;
            case "河北建华":
                $postcom = 'HBJH';
                break;
            case "汇丰物流":
                $postcom = 'HFWL';
                break;
            case "华航快递":
                $postcom = 'HHKD';
                break;
            case "天天快递":
                $postcom = 'HHTT';
                break;
            case "韩润物流":
                $postcom = 'HLKD';
                break;
            case "恒路物流":
                $postcom = 'HLWL';
                break;
            case "黄马甲快递":
                $postcom = 'HMJKD';
                break;
            case "海盟速递":
                $postcom = 'HMSD';
                break;
            case "天地华宇":
                $postcom = 'HOAU';
                break;
            case "华强物流":
                $postcom = 'hq568';
                break;
            case "华企快运":
                $postcom = 'HQKY';
                break;
            case "昊盛物流":
                $postcom = 'HSWL';
                break;
            case "百世汇通":
                $postcom = 'HTKY';
                break;
            case "户通物流":
                $postcom = 'HTWL';
                break;
            case "华夏龙物流":
                $postcom = 'HXLWL';
                break;
            case "好来运快递":
                $postcom = 'HYLSD';
                break;
            case "京东快递":
                $postcom = 'JD';
                break;
            case "京广速递":
                $postcom = 'JGSD';
                break;
            case "九曳供应链":
                $postcom = 'JIUYE';
                break;
            case "佳吉快运":
                $postcom = 'JJKY';
                break;
            case "嘉里大通":
                $postcom = 'JLDT';
                break;
            case "捷特快递":
                $postcom = 'JTKD';
                break;
            case "急先达":
                $postcom = 'JXD';
                break;
            case "晋越快递":
                $postcom = 'JYKD';
                break;
            case "加运美":
                $postcom = 'JYM';
                break;
            case "久易快递":
                $postcom = 'JYSD';
                break;
            case "佳怡物流":
                $postcom = 'JYWL';
                break;
            case "康力物流":
                $postcom = 'KLWL';
                break;
            case "快淘快递":
                $postcom = 'KTKD';
                break;
            case "快优达速递":
                $postcom = 'KYDSD';
                break;
            case "跨越速递":
                $postcom = 'KYWL';
                break;
            case "龙邦快递":
                $postcom = 'LB';
                break;
            case "联邦快递":
                $postcom = 'LBKD';
                break;
            case "蓝弧快递":
                $postcom = 'LHKD';
                break;
            case "联昊通速递":
                $postcom = 'LHT';
                break;
            case "乐捷递":
                $postcom = 'LJD';
                break;
            case "立即送":
                $postcom = 'LJS';
                break;
            case "民邦速递":
                $postcom = 'MB';
                break;
            case "门对门":
                $postcom = 'MDM';
                break;
            case "民航快递":
                $postcom = 'MHKD';
                break;
            case "明亮物流":
                $postcom = 'MLWL';
                break;
            case "闽盛快递":
                $postcom = 'MSKD';
                break;
            case "能达速递":
                $postcom = 'NEDA';
                break;
            case "南京晟邦物流":
                $postcom = 'NJSBWL';
                break;
            case "平安达腾飞快递":
                $postcom = 'PADTF';
                break;
            case "陪行物流":
                $postcom = 'PXWL';
                break;
            case "全晨快递":
                $postcom = 'QCKD';
                break;
            case "全峰快递":
                $postcom = 'QFKD';
                break;
            case "全日通快递":
                $postcom = 'QRT';
                break;
            case "如风达":
                $postcom = 'RFD';
                break;
            case "日昱物流":
                $postcom = 'RLWL';
                break;
            case "赛澳递":
                $postcom = 'SAD';
                break;
            case "圣安物流":
                $postcom = 'SAWL';
                break;
            case "盛邦物流":
                $postcom = 'SBWL';
                break;
            case "山东海红":
                $postcom = 'SDHH';
                break;
            case "上大物流":
                $postcom = 'SDWL';
                break;
            case "顺丰快递":
                $postcom = 'SF';
                break;
            case "盛丰物流":
                $postcom = 'SFWL';
                break;
            case "上海林道货运":
                $postcom = 'SHLDHY';
                break;
            case "盛辉物流":
                $postcom = 'SHWL';
                break;
            case "穗佳物流":
                $postcom = 'SJWL';
                break;
            case "速通物流":
                $postcom = 'ST';
                break;
            case "申通快递":
                $postcom = 'STO';
                break;
            case "三态速递":
                $postcom = 'STSD';
                break;
            case "速尔快递":
                $postcom = 'SURE';
                break;
            case "山西红马甲":
                $postcom = 'SXHMJ';
                break;
            case "沈阳佳惠尔":
                $postcom = 'SYJHE';
                break;
            case "世运快递":
                $postcom = 'SYKD';
                break;
            case "通和天下":
                $postcom = 'THTX';
                break;
            case "唐山申通":
                $postcom = 'TSSTO';
                break;
            case "全一快递":
                $postcom = 'UAPEX';
                break;
            case "优速快递":
                $postcom = 'UC';
                break;
            case "万家物流":
                $postcom = 'WJWL';
                break;
            case "微特派":
                $postcom = 'WTP';
                break;
            case "万象物流":
                $postcom = 'WXWL';
                break;
            case "新邦物流":
                $postcom = 'XBWL';
                break;
            case "信丰快递":
                $postcom = 'XFEX';
                break;
            case "香港邮政":
                $postcom = 'XGYZ';
                break;
            case "祥龙运通":
                $postcom = 'XLYT';
                break;
            case "希优特":
                $postcom = 'XYT';
                break;
            case "源安达快递":
                $postcom = 'YADEX';
                break;
            case "邮必佳":
                $postcom = 'YBJ';
                break;
            case "远成物流":
                $postcom = 'YCWL';
                break;
            case "韵达快递":
                $postcom = 'YD';
                break;
            case "义达国际物流":
                $postcom = 'YDH';
                break;
            case "越丰物流":
                $postcom = 'YFEX';
                break;
            case "原飞航物流":
                $postcom = 'YFHEX';
                break;
            case "亚风快递":
                $postcom = 'YFSD';
                break;
            case "银捷速递":
                $postcom = 'YJSD';
                break;
            case "亿领速运":
                $postcom = 'YLSY';
                break;
            case "英脉物流":
                $postcom = 'YMWL';
                break;
            case "亿顺航":
                $postcom = 'YSH';
                break;
            case "音素快运":
                $postcom = 'YSKY';
                break;
            case "易通达":
                $postcom = 'YTD';
                break;
            case "一统飞鸿":
                $postcom = 'YTFH';
                break;
            case "运通快递":
                $postcom = 'YTKD';
                break;
            case "圆通速递":
                $postcom = 'YTO';
                break;
            case "宇鑫物流":
                $postcom = 'YXWL';
                break;
            case "邮政平邮/小包":
                $postcom = 'YZPY';
                break;
            case "增益快递":
                $postcom = 'ZENY';
                break;
            case "汇强快递":
                $postcom = 'ZHQKD';
                break;
            case "宅急送":
                $postcom = 'ZJS';
                break;
            case "芝麻开门":
                $postcom = 'ZMKM';
                break;
            case "中睿速递":
                $postcom = 'ZRSD';
                break;
            case "众通快递":
                $postcom = 'ZTE';
                break;
            case "中铁快运":
                $postcom = 'ZTKY';
                break;
            case "中铁物流":
                $postcom = 'ZTWL';
                break;
            case "中天万运":
                $postcom = 'ZTWY';
                break;
            case "中外运速递":
                $postcom = 'ZWYSD';
                break;
            case "中邮物流":
                $postcom = 'ZYWL';
                break;
            case "郑州建华":
                $postcom = 'ZZJH';
                break;
            default:
                $postcom = '';
        }
        return $postcom;
    }
}
