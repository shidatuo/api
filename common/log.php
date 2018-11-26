<?php
/**
 * @param $filename
 * @param $msg
 * @author shidatuo
 * @description 当前目录的cache目录记录日志 (根据年月日产生日志文件)
 */
function log_ex($filename,$msg){
    date_default_timezone_set('Asia/Shanghai');
    $file_path = $_SERVER['DOCUMENT_ROOT'] . "/log/";
    if (!file_exists($file_path)) @mkdir($file_path);
    $file_path .= $filename . date("Y-m-d");
    file_put_contents($file_path, date("Y-m-d H:i:s") . "\t" . $msg . "\n", FILE_APPEND);
}