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
if(!function_exists("standard_json_decode")){
    /**
     * @param $json
     * @return string
     * @author shidatuo
     * @description 解析规格json字符串 0:{id: "10", name: "颜色", subModelId: "32", subModelName: "棕色"}
     */
    function standard_json_decode($json = null , $type = 'array'){
        if (isset($json) && NotEstr($json) && $json != '[]' && !empty($json)) {
            $Arr = json_decode($json, true);
            if($type === 'array')
                return $Arr;
            $modelIds = [];
            foreach ($Arr as $value){
                array_push($modelIds, $value['subModelId']);
            }
            sort($modelIds);
            $modelIds = implode(',', $modelIds);
            return $modelIds;
        }
        return '';
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