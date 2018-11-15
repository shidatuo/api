<?php
$functions_dir = __DIR__.DS;
include_once $functions_dir.'apis.php';
include_once $functions_dir.'api.php';
include_once $functions_dir.'common.php';
include_once $functions_dir.'format.php';
include_once $functions_dir.'date.php';
include_once $functions_dir.'data.php';
include_once $functions_dir.'url.php';
include_once $functions_dir.'http.php';
include_once $functions_dir.'databases.php';


/**
 * 记录用户ip
 */
if (!defined('USER_IP')){
    if (isset($_SERVER["REMOTE_ADDR"])){
        define("USER_IP", $_SERVER["REMOTE_ADDR"]);
    } else {
        define("USER_IP", getIP());
    }
}
/**
 * 记录服务ip
 */
if (!defined('SERVE_IP')){
    define("SERVE_IP", serverIP());
}



api_expose('get_v3_register_integral');
