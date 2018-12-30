<?php
if(!function_exists("strip_sql")){
    /**
     * @param $string
     * @return array|null|string|string[]
     * @author shidatuo
     * @description 转化sql关键字
     */
    function strip_sql($string) {
        $pattern_arr = array(
            "/\bunion\b/i",
            "/\bselect\b/i",
            "/\bupdate\b/i",
            "/\bdelete\b/i",
            "/\boutfile\b/i",
            "/\bor\b/i",
            "/\bchar\b/i",
            "/\bconcat\b/i",
            "/\btruncate\b/i",
            "/\bdrop\b/i",
            "/\binsert\b/i",
            "/\brevoke\b/i",
            "/\bgrant\b/i",
            "/\breplace\b/i",
            "/\balert\b/i",
            "/\brename\b/i",
            "/\bcreate\b/i",
            "/\bmaster\b/i",
            "/\bdeclare\b/i",
            "/\bsource\b/i",
            "/\bload\b/i",
            "/\bcall\b/i",
            "/\bexec\b/i",
        );
        $replace_arr = array(
            'ｕｎｉｏｎ',
            'ｓｅｌｅｃｔ',
            'ｕｐｄａｔｅ',
            'ｄｅｌｅｔｅ',
            'ｏｕｔｆｉｌｅ',
            'ｏｒ',
            'ｃｈａｒ',
            'ｃｏｎｃａｔ',
            'ｔｒｕｎｃａｔｅ',
            'ｄｒｏｐ',
            'ｉｎｓｅｒｔ',
            'ｒｅｖｏｋｅ',
            'ｇｒａｎｔ',
            'ｒｅｐｌａｃｅ',
            'ａｌｅｒｔ',
            'ｒｅｎａｍｅ',
            'ｃｒｅａｔｅ',
            'ｍａｓｔｅｒ',
            'ｄｅｃｌａｒｅ',
            'ｓｏｕｒｃｅ',
            'ｌｏａｄ',
            'ｃａｌｌ',
            'ｅｘｅｃ',
        );
        return is_array($string) ? array_map('strip_sql', $string) : preg_replace($pattern_arr, $replace_arr, $string);
    }
}
if(!function_exists("convert_arr_key")){
    /**
     * @param $arr
     * @param $key_name
     * @return array
     * @author shidatuo
     * @description 将数据库中查出的列表以指定的 id 作为数组的键名
     */
    function convert_arr_key($arr, $key_name){
        $arr2 = array();
        foreach($arr as $key => $val){
            $arr2[$val[$key_name]] = $val;
        }
        return $arr2;
    }
}
if(!function_exists("array_sorts")){
    /**
     * @param $arr
     * @param $keys 那个字段
     * @param string $type 排序规则
     * @return array
     * @author shidato
     * @description 二维数组排序
     */
    function array_sorts($arr, $keys, $type = 'desc'){
        $key_value = $new_array = array();
        foreach ($arr as $k => $v) {
            $key_value[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($key_value);
        } else {
            arsort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
}
if(!function_exists("array_multi2single")){
    /**
     * @param $array
     * @return array
     * @author shidatuo
     * @description 多维数组转化为一维数组
     */
    function array_multi2single($array){
        static $result_array = array();
        foreach ($array as $value) {
            if (is_array($value)) {
                array_multi2single($value);
            } else
                $result_array [] = $value;
        }
        return $result_array;
    }
}
if(!function_exists("recurse_copy")){
    /**
     * @param $src 原目录
     * @param $dst 复制到的目录
     * @author shidatuo
     * @description 自定义函数递归的复制带有多级子目录的目录
     */
    function recurse_copy($src, $dst){
        $now = time();
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== $file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                }
                else {
                    if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                        if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
                            exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
                        }
                        @unlink($dst . DIRECTORY_SEPARATOR . $file);
                    }
                    if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                        @unlink($dst . DIRECTORY_SEPARATOR . $file);
                    }
                    $copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                    if (!$copyrt) {
                        echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
                    }
                }
            }
        }
        closedir($dir);
    }
}
if(!function_exists("delFile")){
    /**
     * @param $dir 文件目录
     * @param string $file_type 匹配指定文件
     * @author shidatuo
     * @description 递归删除文件夹
     */
    function delFile($dir,$file_type = '') {
        if(is_dir($dir)){
            $files = scandir($dir);
            //打开目录 //列出目录中的所有文件并去掉 . 和 ..
            foreach($files as $filename){
                if($filename!='.' && $filename!='..'){
                    if(!is_dir($dir.'/'.$filename)){
                        if(empty($file_type)){
                            unlink($dir.'/'.$filename);
                        }else{
                            if(is_array($file_type)){
                                //正则匹配指定文件
                                if(preg_match($file_type[0],$filename)){
                                    unlink($dir.'/'.$filename);
                                }
                            }else{
                                //指定包含某些字符串的文件
                                if(false!=stristr($filename,$file_type)){
                                    unlink($dir.'/'.$filename);
                                }
                            }
                        }
                    }else{
                        delFile($dir.'/'.$filename);
                        rmdir($dir.'/'.$filename);
                    }
                }
            }
        }else{
            if(file_exists($dir)) unlink($dir);
        }
    }
}


if(!function_exists("encode")){
    /**
     * 对称加密算法之加密
     * @param String $string 需要加密的字串
     * @param String $skey 加密EKY
     * @author Anyon Zou <zoujingli@qq.com>
     * @date 2013-08-13 19:30
     * @update 2014-10-10 10:10
     * @return String
     */
    function encode($string = '', $skey = 'xiaochengxu') {
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value){
            $key < $strCount && $strArr[$key].=$value;
        }
        return str_replace(array('=', '+', '/'), array('XXXXX', 'oOOOo', 'ooAAo'), join('', $strArr));
    }
}
if(!function_exists("decode")){
    /**
     * 对称加密算法之解密
     * @param String $string 需要解密的字串
     * @param String $skey 解密KEY
     * @return String
     */
    function decode($string = '', $skey = 'xiaochengxu') {
        $strArr = str_split(str_replace(array('XXXXX', 'oOOOo', 'ooAAo'), array('=', '+', '/'), $string), 2);
        $strCount = count($strArr);
        $sp_array=str_split($skey);
        if(is_arr($sp_array)){
            foreach ($sp_array as $key => $value){
                if(isset($strArr[$key][1])&&isset($strArr[$key][0])){
                    $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
                }
            }
            $str=base64_decode(join('', $strArr));
            return $str;
        }
        return '';
    }
}
if(!function_exists("unique")){
    /**
     * @param array $data
     * @return array
     * @author shidatuo
     * @description 多维数组去重
     */
    function unique($data = array()){
        $tmp = array();
        foreach($data as $key => $value){
            //把一维数组键值与键名组合
            foreach($value as $key1 => $value1){
                $value[$key1] = $key1 . '_|_' . $value1;//_|_分隔符复杂点以免冲突
            }
            $tmp[$key] = implode(',|,', $value);//,|,分隔符复杂点以免冲突
        }
        //对降维后的数组去重复处理
        $tmp = array_unique($tmp);
        //重组二维数组
        $newArr = array();
        foreach($tmp as $k => $tmp_v){
            $tmp_v2 = explode(',|,', $tmp_v);
            foreach($tmp_v2 as $k2 => $v2){
                $v2 = explode('_|_', $v2);
                $tmp_v3[$v2[0]] = $v2[1];
            }
            $newArr[$k] = $tmp_v3;
        }
        return $newArr;
    }
}
if(!function_exists("NotEstr")){
    /**
     * @param string $string
     * @return bool
     * @author shidatuo
     * @description 过滤空字符串
     */
    function NotEstr($string = ''){
        if(is_null($string)) return false;
        if(is_string($string) && trim($string) != ''){
            return true;
        }
        return false;
    }
}
if(!function_exists("checkNotNull")){
    /**
     * @param $fieldName
     * @param $value
     * @throws Exception
     * @author shidatuo
     * @description 检测字段是否存在
     */
    function checkNotNull($fieldName,$value) {
        if(checkEmpty($value)){
            return $fieldName;
        }
    }
}
if(!function_exists("checkEmpty")){
    /**
     * @param $value
     * @return bool
     * @author shidatuo
     * @description 检测字段是否空
     */
    function checkEmpty($value){
        if (!isset($value)) {
            return true;
        }
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === "") {
            return true;
        }
        return false;
    }
}
if(!function_exists("isINT")){
    /**
     * @param $numeric
     * @return bool
     * @author shidatuo
     * @description 检测改值是否正整数
     */
    function isINT($numeric){
        if(is_numeric($numeric) && $numeric)
            return true;
        return false;
    }
}
if(!function_exists("is_arr")){
    /**
     * @param array $params
     * @return bool
     * @author shidatuo
     * @descripion 判断是否空数组
     */
    function is_arr($params = array()){
        if (is_array($params) && !empty($params))
            return true;
        else
            return false;
    }
}
if(!function_exists("convert_arr_key")){
    /**
     * @param $arr
     * @param $key_name
     * @return array
     * @author shidatuo
     * @description 将数据库中查出的列表以指定的 id 作为数组的键名
     */
    function convert_arr_key($arr, $key_name){
        $arr2 = array();
        foreach($arr as $key => $val){
            $arr2[$val[$key_name]] = $val;
        }
        return $arr2;
    }
}
if(!function_exists("jsonReturn")){
    /**
     * @param int $status
     * @param string $msg
     * @param string $data
     * @author shidatuo
     * @description 返回json数据
     */
    function jsonReturn($status=0,$msg='',$data=''){
        if(empty($data))
            $data = '';
        $info['status'] = $status;
        $info['msg'] = $msg;
        $info['result'] = $data;
        exit(json_encode($info));
    }
}
if(!function_exists("parse_params")){
    /**
     * @param $params
     * @return array|void
     * @author shidatuo
     * @description 字符转换数组
     */
    function parse_params($params){
        $params2 = array();
        if (is_string($params)) {
            $params = parse_str($params, $params2);
            $params = $params2;
            unset($params2);
        }
        return $params;
    }
}


<?php

use HyperDown\Parser;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

if ( !function_exists('ajax_return') ) {
    /**
     * ajax返回数据
     *
     * @param string $data 需要返回的数据
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    function ajax_return($status_code = 200, $data = '')
    {
        //如果如果是错误 返回错误信息
        if ($status_code != 200) {
            //增加status_code
            $data = ['status_code' => $status_code, 'message' => $data,];
            return response()->json($data, $status_code);
        }
        //如果是对象 先转成数组
        if (is_object($data)) {
            $data = $data->toArray();
        }
        /**
         * 将数组递归转字符串
         * @param  array $arr 需要转的数组
         * @return array       转换后的数组
         */
        function to_string($arr)
        {
            // app 禁止使用和为了统一字段做的判断
            $reserved_words = [];
            foreach ($arr as $k => $v) {
                //如果是对象先转数组
                if (is_object($v)) {
                    $v = $v->toArray();
                }
                //如果是数组；则递归转字符串
                if (is_array($v)) {
                    $arr[$k] = to_string($v);
                } else {
                    //判断是否有移动端禁止使用的字段
                    in_array($k, $reserved_words, true) && die('不允许使用【' . $k . '】这个键名 —— 此提示是helper.php 中的ajaxReturn函数返回的');
                    //转成字符串类型
                    $arr[$k] = strval($v);
                }
            }
            return $arr;
        }

        //判断是否有返回的数据
        if (is_array($data)) {
            //先把所有字段都转成字符串类型
            $data = to_string($data);
        }
        return response()->json($data, $status_code);
    }
}

if ( !function_exists('send_email') ) {
    /**
     * 发送邮件函数
     *
     * @param $email            收件人邮箱  如果群发 则传入数组
     * @param $name             收件人名称
     * @param $subject          标题
     * @param array  $data      邮件模板中用的变量 示例：['name'=>'帅白','phone'=>'110']
     * @param string $template  邮件模板
     * @return array            发送状态
     */
    function send_email($email, $name, $subject, $data = [], $template = 'emails.test')
    {
        Mail::send($template, $data, function ($message) use ($email, $name, $subject) {
            //如果是数组；则群发邮件
            if (is_array($email)) {
                foreach ($email as $k => $v) {
                    $message->to($v, $name)->subject($subject);
                }
            } else {
                $message->to($email, $name)->subject($subject);
            }
        });
        if (count(Mail::failures()) > 0) {
            $data = array('status_code' => 500, 'message' => '邮件发送失败');
        } else {
            $data = array('status_code' => 200, 'message' => '邮件发送成功');
        }
        return $data;
    }
}

if ( !function_exists('upload') ) {
    /**
     * 上传文件函数
     *
     * @param $file             表单的name名
     * @param string $path 上传的路径
     * @param bool $childPath 是否根据日期生成子目录
     * @return array            上传的状态
     */
    function upload($file, $path = 'upload', $childPath = true)
    {
        //判断请求中是否包含name=file的上传文件
        if (!request()->hasFile($file)) {
            $data = ['status_code' => 500, 'message' => '上传文件为空'];
            return $data;
        }
        $file = request()->file($file);
        //判断文件上传过程中是否出错
        if (!$file->isValid()) {
            $data = ['status_code' => 500, 'message' => '文件上传出错'];
            return $data;
        }
        //兼容性的处理路径的问题
        if ($childPath == true) {
            $path = './' . trim($path, './') . '/' . date('Ymd') . '/';
        } else {
            $path = './' . trim($path, './') . '/';
        }
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        //获取上传的文件名
        $oldName = $file->getClientOriginalName();
        //组合新的文件名
        $newName = uniqid() . '.' . $file->getClientOriginalExtension();
        //上传失败
        if (!$file->move($path, $newName)) {
            $data = ['status_code' => 500, 'message' => '保存文件失败'];
            return $data;
        }
        //上传成功
        $data = ['status_code' => 200, 'message' => '上传成功', 'data' => ['old_name' => $oldName, 'new_name' => $newName, 'path' => trim($path, '.')]];
        return $data;
    }
}

if ( !function_exists('get_uid') ) {
    /**
     * 返回登录的用户id
     *
     * @return mixed 用户id
     */
    function get_uid()
    {
        return Auth::id();
    }
}

if (!function_exists('save_to_file')) {
    /**
     * 将数组已json格式写入文件
     * @param  string $fileName 文件名
     * @param  array $data 数组
     */
    function save_to_file($fileName = 'test', $data = array())
    {
        $path = storage_path('tmp' . DIRECTORY_SEPARATOR);
        is_dir($path) || mkdir($path);
        $fileName = str_replace('.php', '', $fileName);
        $fileName = $path . $fileName . '_' . date('Y-m-d_H-i-s', time()) . '.php';
        file_put_contents($fileName, json_encode($data));
    }
}

if ( !function_exists('re_substr') ) {
    /**
     * 字符串截取，支持中文和其他编码
     *
     * @param string  $str 需要转换的字符串
     * @param integer $start 开始位置
     * @param string  $length 截取长度
     * @param boolean $suffix 截断显示字符
     * @param string  $charset 编码格式
     * @return string
     */
    function re_substr($str, $start = 0, $length, $suffix = true, $charset = "utf-8") {
        $slice = mb_substr($str, $start, $length, $charset);
        $omit = mb_strlen($str) >= $length ? '...' : '';
        return $suffix ? $slice.$omit : $slice;
    }
}

if ( !function_exists('Add_text_water') ) {
    /**
     * 给图片添加文字水印
     *
     * @param $file
     * @param $text
     * @param string $color
     * @return mixed
     */
    function Add_text_water($file, $text, $color = '#0B94C1') {
        $image = Image::make($file);
        $image->text($text, $image->width()-20, $image->height()-30, function($font) use($color) {
            $font->file(public_path('fonts/msyh.ttf'));
            $font->size(15);
            $font->color($color);
            $font->align('right');
            $font->valign('bottom');
        });
        $image->save($file);
        return $image;
    }
}

if ( !function_exists('word_time') ) {
    /**
     * 把日期或者时间戳转为距离现在的时间
     *
     * @param $time
     * @return bool|string
     */
    function word_time($time) {
        // 如果是日期格式的时间;则先转为时间戳
        if (!is_integer($time)) {
            $time = strtotime($time);
        }
        $int = time() - $time;
        if ($int <= 2){
            $str = sprintf('刚刚', $int);
        }elseif ($int < 60){
            $str = sprintf('%d秒前', $int);
        }elseif ($int < 3600){
            $str = sprintf('%d分钟前', floor($int / 60));
        }elseif ($int < 86400){
            $str = sprintf('%d小时前', floor($int / 3600));
        }elseif ($int < 1728000){
            $str = sprintf('%d天前', floor($int / 86400));
        }else{
            $str = date('Y-m-d H:i:s', $time);
        }
        return $str;
    }
}

if ( !function_exists('markdown_to_html') ) {
    /**
     * 把markdown转为html
     *
     * @param $markdown
     * @return mixed|string
     */
    function markdown_to_html($markdown){
        // 正则匹配到全部的iframe
        preg_match_all('/&lt;iframe.*iframe&gt;/', $markdown, $iframe);
        // 如果有iframe 则先替换为临时字符串
        if (!empty($iframe[0])) {
            $tmp = [];
            // 组合临时字符串
            foreach ($iframe[0] as $k => $v) {
                $tmp[] = '【iframe'.$k.'】';
            }
            // 替换临时字符串
            $markdown = str_replace($iframe[0], $tmp, $markdown);
            // 讲iframe转义
            $replace = array_map(function ($v){
                return htmlspecialchars_decode($v);
            }, $iframe[0]);
        }
        // markdown转html
        $parser = new Parser();
        $html = $parser->makeHtml($markdown);
        $html = str_replace('<code class="', '<code class="lang-', $html);
        // 将临时字符串替换为iframe
        if (!empty($iframe[0])) {
            $html = str_replace($tmp, $replace, $html);
        }
        return $html;
    }
}

if (!function_exists('strip_html_tags')) {
    /**
     * 删除指定标签
     *
     * @param array $tags     删除的标签  数组形式
     * @param string $str     html字符串
     * @param bool $content   true保留标签的内容text
     * @return mixed
     */
    function strip_html_tags($tags, $str, $content = true){
        $html = [];
        // 是否保留标签内的text字符
        if($content){
            foreach ($tags as $tag) {
                $html[] = '/(<' . $tag . '.*?>(.|\n)*?<\/' . $tag . '>)/is';
            }
        }else{
            foreach ($tags as $tag) {
                $html[] = "/(<(?:\/" . $tag . "|" . $tag . ")[^>]*>)/is";
            }
        }
        $data = preg_replace($html, '', $str);
        return $data;
    }
}

if (!function_exists('curl_get_contents')) {
    /**
     * 使用curl获取远程数据
     * @param  string $url url连接
     * @return string      获取到的数据
     */
    function curl_get_contents($url){
        set_time_limit(0);
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                //设置访问的url地址
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);               //设置超时
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   //用户访问代理 User-Agent
        curl_setopt($ch, CURLOPT_REFERER,$_SERVER['HTTP_HOST']);        //设置 referer
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);          //跟踪301
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
        $r=curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}

if (!function_exists('redis')) {
    /**
     * redis的便捷操作方法
     *
     * @param $key
     * @param null $value
     * @param null $expire
     * @return bool|string
     */
    function redis($key = null, $value = null, $expire = null){
        if (is_null($key)) {
            return app('redis');
        }
        if (is_null($value)) {
            $content = Redis::get($key);
            if (is_null($content)) {
                return null;
            }
            return is_null($content) ? null : unserialize($content);
        }
        Redis::set($key, serialize($value));
        if (! is_null($expire)) {
            Redis::expire($key, $expire);
        }
    }
}

