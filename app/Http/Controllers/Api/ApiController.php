<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use \App\Model\User;

class ApiController extends Controller{

    public $app;

    /**
     * @description 使用登录凭证 code 获取 session_key 和 openid
     * @link https://www.w3cschool.cn/weixinapp/weixinapp-api-login.html
     */
    const API_WX_LOGIN = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

    public function __construct($app = null){
        if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = xn();
            }
        }
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
        $request_url = sprintf(self::API_WX_LOGIN,'wxfb7ee7bea70315cd','c462b7e6b84990f480a9e309ac2fe617',$req->input("code",""));
        log_ex("wxLogin",PHP_EOL . "请求微信服务器url : " . $request_url . PHP_EOL);
        $json = http_request($request_url);
        log_ex("wxLogin",PHP_EOL . "微信服务器返回值 : " . $json . PHP_EOL);
        $result = json_decode($json,true);
        if(!isset($result['openid'])){
            log_ex("wxLogin",PHP_EOL ."返回 -1 [无效的code]" .PHP_EOL."============================== 微信小程序code换取session_key END =============================" . PHP_EOL);
            jsonReturn(-1,"无效的code");
        }
        jsonReturn(1,"请求成功",$result);
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
            jsonReturn(-1,"无效的openid");
        log_ex("wxLogin",PHP_EOL . "获取open_id : " . $open_id . PHP_EOL);
        $user_info = get("jy_user","openid={$open_id}&single=true&fields=id");
        log_ex("wxLogin",PHP_EOL . "根据open_id 获取到用户信息" . json_encode($user_info) . PHP_EOL);
        jsonReturn(1,"请求成功",$user_info ? $user_info : []);
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
            jsonReturn(-1,"无效的openid");
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
            jsonReturn(1,"请求成功");
        }
        log_ex("wxcreateUser",PHP_EOL ."返回 -1 [保存失败]" .PHP_EOL."============================== 创建用户信息 END =============================" . PHP_EOL);
        jsonReturn(-1,"保存失败");
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
