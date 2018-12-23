<?php
/**
 * @param null $class
 * @return \Illuminate\Foundation\Application|mixed
 * @author shidatuo
 * @description 调用全局的类库
 */
function xn($class = null){
    return app($class);
}

if (!function_exists('is_https')) {
    /**
     * @return bool
     * @author shidatuo
     * @description 是否https请求
     */
    function is_https(){
        if (isset($_SERVER['HTTPS']) and (strtolower($_SERVER['HTTPS']) == 'on')) {
            return true;
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) {
            return true;
        }
        return false;
    }
}
if (!function_exists('site_url')) {
    function site_url($add_string = false){
        static $site_url;
        if (defined('XN_SITE_URL')) {
            $site_url = XN_SITE_URL;
        }
        if ($site_url == false) {
            $pageURL = 'http';
            if (is_https()) {
                $pageURL .= 's';
            }
            $subdir_append = false;
            if (isset($_SERVER['PATH_INFO'])) {
                // $subdir_append = $_SERVER ['PATH_INFO'];
            } elseif (isset($_SERVER['REDIRECT_URL'])) {
                $subdir_append = $_SERVER['REDIRECT_URL'];
            }
            $pageURL .= '://';
            if (isset($_SERVER['HTTP_HOST'])) {
                $pageURL .= $_SERVER['HTTP_HOST'];
            } elseif (isset($_SERVER['SERVER_NAME']) and isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] != '80' and $_SERVER['SERVER_PORT'] != '443') {
                $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $pageURL .= $_SERVER['SERVER_NAME'];
            } elseif (isset($_SERVER['HOSTNAME'])) {
                $pageURL .= $_SERVER['HOSTNAME'];
            }
            $pageURL_host = $pageURL;
            $pageURL .= $subdir_append;
            $d = '';
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $d = dirname($_SERVER['SCRIPT_NAME']);
                $d = trim($d, DIRECTORY_SEPARATOR);
            }
            if ($d == '') {
                $pageURL = $pageURL_host;
            } else {
                $pageURL_host = rtrim($pageURL_host, '/') . '/';
                $d = ltrim($d, '/');
                $d = ltrim($d, DIRECTORY_SEPARATOR);
                $pageURL = $pageURL_host . $d;
            }
            if (isset($_SERVER['QUERY_STRING'])) {
                $pageURL = str_replace($_SERVER['QUERY_STRING'], '', $pageURL);
            }

            $uz = parse_url($pageURL);
            if (isset($uz['query'])) {
                $pageURL = str_replace($uz['query'], '', $pageURL);
                $pageURL = rtrim($pageURL, '?');
            }

            $url_segs = explode('/', $pageURL);

            $i = 0;
            $unset = false;
            foreach ($url_segs as $v) {
                if ($unset == true and $d != '') {
                    unset($url_segs[$i]);
                }
                if ($v == $d and $d != '') {
                    $unset = true;
                }

                ++$i;
            }
            $url_segs[] = '';
            $site_url = implode('/', $url_segs);
        }
        return $site_url . $add_string;
    }
}
if (!function_exists('reduce_double_slashes')) {
    /**
     * Removes double slashes from sting.
     *
     * @param $str
     *
     * @return string
     */
    function reduce_double_slashes($str)
    {
        return preg_replace('#([^:])//+#', '\\1/', $str);
    }
}

/**
 * @param $param
 * @param bool $skip_ajax
 * @param bool $force_url
 * @return array|bool|mixed|string
 */
function params($param = '__XN_GET_ALL_PARAMS__', $skip_ajax = false, $force_url = false) {
    if ($_POST){
        if (isset($_POST['search_by_keyword'])){
            if ($param=='keyword'){
                return $_POST['search_by_keyword'];
            }
        }
    }
    $url = url_current($skip_ajax);
    if ($force_url!=false){
        $url = $force_url;
    }
    $rem = site_url();
    dd($rem);
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
                        $the_param1 = base64_to_array($the_param);

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

/**
 * Returns the current url as a string.
 *
 * @param bool $skip_ajax If true it will try to get the referring url from ajax request
 * @param bool $no_get    If true it will remove the params after '?'
 *
 * @return string the url string
 */
function url_current($skip_ajax = false, $no_get = false) {
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

if (!function_exists('flash_success')){
    /**
     * @param string $message
     * @author shidatuo
     * @description 添加成功提示
     * @link https://laravel.com/docs/5.5/session
     * flash方法执行此操作。使用此方法存储在会话中的数据仅在后续HTTP请求期间可用，然后将被删除
     */
    function flash_success($message = '成功'){
        session()->flash('alert-message', $message);
        session()->flash('alert-type', 'success');
    }
}

if (!function_exists('flash_error')){
    /**
     * @param string $message
     * @author shidatuo
     * @description 添加失败提示
     */
    function flash_error($message = '失败'){
        session()->flash('alert-message', $message);
        session()->flash('alert-type', 'error');
    }
}