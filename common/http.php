<?php
/**
 * @return array|false|string
 * @author shidatuo
 * @description 获取客服端ip
 */
function getIP(){
    if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow";

    if(preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip))
        return $ip;
    else
        return '127.0.0.1';
}
/**
 * @return string
 * @author shidatuo
 * @description 获取服务端ip
 */
function serverIP(){
    return gethostbyname($_SERVER["SERVER_NAME"]);
}
/**
 * @param $url
 * @param null $data
 * @return mixed
 * @author shidatuo
 * @description http请求支持GET和POST
 */
function http_request($url,$data=null){
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
    if (!empty($data)){
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}