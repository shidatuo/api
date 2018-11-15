<?php
/**
 * @param $str
 * @return array
 * @author shidatuo
 * @description 获取url参数 类似于 pay_code=alipay&bank_code=ICBC-DEBIT
 */
function parse_url_param($str){
    $data = array();
    $parameter = explode('&',end(explode('?',$str)));
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}