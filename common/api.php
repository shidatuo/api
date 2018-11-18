<?php
/**
 * api 过滤器普通
 * @param $function_name
 * @param null $callback
 * @return array|string
 */
function api_expose($function_name, $callback = null){
    static $index = ' ';
    if (is_bool($function_name)) {
        return $index;
    }
    if (is_callable($callback)) {
        $index .= ' ' . $function_name;
        return api_bind($function_name, $callback);
    } else {
        $index .= ' ' . $function_name;
    }
}
/**
 * 带有admin的API定义器
 * @param $function_name
 * @param null $callback
 * @return array|string
 */
function api_expose_admin($function_name, $callback = null){
    static $index = ' ';
    if (is_bool($function_name)) {
        return $index;
    }
    if (is_callable($callback)) {
        $index .= ' ' . $function_name;

        return api_bind_admin($function_name, $callback);
    } else {
        $index .= ' ' . $function_name;
    }
}
/**
 * 带有user的API定义器
 * @param $function_name
 * @param null $callback
 * @return array|string
 */
function api_expose_user($function_name, $callback = null){
    static $index = ' ';
    if (is_bool($function_name)) {
        return $index;
    }
    if (is_callable($callback)) {
        $index .= ' ' . $function_name;

        return api_bind_user($function_name, $callback);
    } else {
        $index .= ' ' . $function_name;
    }
}
/**
 * api 绑定直接接用
 * @param $function_name
 * @param bool $callback
 * @return array
 */
function api_bind($function_name, $callback = false){
    static $xn_api_binds;
    if (is_bool($function_name)) {
        if (is_array($xn_api_binds)) {
            $index = ($xn_api_binds);
            return $index;
        }
    } else {
        $function_name = trim($function_name);
        $xn_api_binds[$function_name][] = $callback;
    }
}
/**
 * 带有admin绑定的api
 * @param $function_name
 * @param bool $callback
 * @return array
 */
function api_bind_admin($function_name, $callback = false){
    static $xn_api_binds;
    if (is_bool($function_name)) {
        if (is_array($xn_api_binds)) {
            $index = ($xn_api_binds);

            return $index;
        }
    } else {
        $function_name = trim($function_name);
        $xn_api_binds[$function_name][] = $callback;
    }
}
/**
 * 带有user绑定的api
 * @param $function_name
 * @param bool $callback
 * @return array
 */
function api_bind_user($function_name, $callback = false){
    static $xn_api_binds_user;
    if (is_bool($function_name)) {
        if (is_array($xn_api_binds_user)) {
            $index = ($xn_api_binds_user);

            return $index;
        }
    } else {
        $function_name = trim($function_name);
        $xn_api_binds_user[$function_name][] = $callback;
    }
}



function document_ready($function_name)
{
    static $index = ' ';
    if (is_bool($function_name)) {
        return $index;
    } else {
        $index .= ' ' . $function_name;
    }
}

/**
 * 处理结构
 * @param $l
 * @return mixed
 */
function execute_document_ready($l)
{
    $document_ready_exposed = (document_ready(true));

    if ($document_ready_exposed != false) {
        $document_ready_exposed = explode(' ', $document_ready_exposed);
        $document_ready_exposed = array_unique($document_ready_exposed);
        $document_ready_exposed = array_trim($document_ready_exposed);

        foreach ($document_ready_exposed as $api_function) {
            if (function_exists($api_function)) {
                $l = $api_function($l);
            }
        }
    }

    return $l;
}
/**
 * 数组转换参数
 * @param $params
 * @param bool $filter
 * @return string
 */
function array_to_module_params($params, $filter = false){
    $str = '';
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            if ($filter == false) {
                $str .= $key . '="' . $value . '" ';
            } elseif (is_array($filter) and !empty($filter)) {
                if (in_array($key, $filter, true)) {
                    $str .= $key . '="' . $value . '" ';
                }
            } else {
                if ($key == $filter) {
                    $str .= $key . '="' . $value . '" ';
                }
            }
        }
    }
    return $str;
}
