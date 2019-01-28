<?php
/**
 * @param $table
 * @return \Illuminate\Database\Eloquent\Builder
 * @author shidatuo
 * @description 实例化单个表
 */
function M($table){
    if($table == 'app')
        return App\Model\App::query();
    elseif ($table == 'users')
        return App\Model\User::query();
    elseif ($table == 'jy_sale_goods')
        return App\Model\jy_sale_goods::query();
    elseif ($table == 'jy_user')
        return App\Model\jy_user::query();
    elseif ($table == 'jy_sale')
        return App\Model\jy_sale::query();
    elseif ($table == 'jy_order')
        return App\Model\jy_order::query();
       elseif ($table == 'jy_complaint')
        return App\Model\jy_complaint::query();
    return DB::table($table);
}
/**
 * @param $table
 * @param null $params
 * @return array|bool|\Illuminate\Database\Eloquent\Collection|static[]
 * @throws Exception
 * @author shidatuo
 * @description 获取数据库数据
 */
function get($table,$params = null){
    if(is_null($params)){
        $params = $table;
    }else{
        if ($params) {
            $params = parse_params($params);
        } else {
            $params = array();
        }
        $params['table'] = $table;
    }
    if(is_string($params)){
        $field = parse_params($params);
    }
    if(is_arr($params)){
        $field = parse_params($params);
    }
    if(!isset($field['table'])){
        return false;
    }else{
        $table = trim($field['table']);
        unset($field['table']);
    }
    if (!Schema::hasTable($table)) {
        //>抛出异常,该表不存在
        throw new Exception("Table $table doesn't exist");
    }
    $query = M($table);
    $origin_params = $field;
    if(!isset($field['limit'])){
        $field['limit'] = LIMIT;
    }
    if(isset($field['single']))
        unset($field['single']);
    if (isset($field['no_limit']))
        unset($field['limit']);
    //>设置缓存时间
    if (isset($field['cache_ttl'])){
        $ttl = $field['cache_ttl'];
    }else{
        $ttl = CACHE_TTL;
    }
    fields_map_filters($query,$field,$table);
    //>聚合
    if (isset($field['min']) && $field['min'])
        return $query->min($field['min']);
    if (isset($field['max']) && $field['max'])
        return $query->max($field['max']);
    if (isset($field['avg']) && $field['avg'])
        return $query->avg($field['avg']);
    if (isset($field['sum']) && $field['sum'])
        return $query->sum($field['sum']);
    if (isset($field['count']) && $field['count'])
        return $query->count($field['count']);
    //>组合where条件
    if (is_array($field) && !empty($field)) {
        foreach ($field as $k => $v) {
            $query = $query->where($table . '.' . $k, '=', $v);
        }
    }
    //>获取数据
    $data = $query->get();
    if(!$data)
        return false;
    //>转化成数组
    $data = $data->toArray();
    if (isset($origin_params['single'])) {
        if (!isset($data[0]))
            return false;
        if (is_object($data[0]) && isset($data[0]->id))
            return (array)$data[0];
        return $data[0];
    }
    //>返回数据
    return $data;
}
/**
 * @param $table
 * @param bool $data
 * @return bool|int|String
 * @throws Exception
 * @author shidatuo
 * @description 保存数据方法
 */
function save($table, $data = false){
    if (is_string($data))
        $data = parse_params($data);
    if (is_arr($table) && isset($table['table'])){
        $data = $table;
        $table = $table['table'];
        unset($data['table']);
    }
    if (!is_arr($data))
        return false;
    if (!Schema::hasTable($table)) {
        //>抛出异常,该表不存在
        throw new Exception("Table $table doesn't exist");
    }
    if(isset($data['appId']) && NotEstr($data['appId'])){
        $data['app_id'] = decode($data['appId']);
        unset($data['appId']);
    }
    //>统一处理openid
    if (isset($data['openid']) && NotEstr($data['openid'])){
        $user_info = \App\Model\User::api_login($data['openid']);
        $data['uid'] = isset($user_info['id']) ? $user_info['id'] : 0;
    }else{
        if(isset($data['uid']))
            $data['uid'] = $data['uid'];
    }
    //>users 表没有uid
    if(in_array($table,['users']) && isset($data['uid'])){
        $data['id'] = $data['uid'];
        unset($data['uid']);
    }
    //>app 表没有app_id
    if(in_array($table,['app']) && isset($data['app_id'])){
        $data['id'] = $data['app_id'];
        unset($data['app_id']);
    }
    if(isset($data['uid']) && !isINT($data['uid'])){
        unset($data['uid']);
    }
    //>表里是否存在 user_ip 字段
    if(Schema::hasColumn($table, 'user_ip')){
        $data['user_ip'] = USER_IP;
    }
    //>表里是否存在 server_ip 字段
    if(Schema::hasColumn($table, 'server_ip')){
        $data['server_ip'] = SERVE_IP;
    }
    if(isset($data['id']) && isINT($data['id'])){
        //>update
        $data['updated_at'] = date("Y-m-d H:i:s");
        M($table)->where("id",$data['id'])->update($data);
        $rs_id = $data['id'];
    }else{
        //>insert
        $data['updated_at'] = $data['created_at'] = date("Y-m-d H:i:s");
        $rs_id = M($table)->insertGetId($data);
    }
    return $rs_id;
}
/**
 * @param $query
 * @param $params
 * @param $table
 * @return mixed
 * @author shidatuo
 * @description 过滤字段
 */
function fields_map_filters($query, &$params, $table){
    if(isset($params['count']) && $params['count'] === true){
        if(isset($params['current_page']))
            unset($params['current_page']);
        if(isset($params['limit']))
            unset($params['limit']);
    }
    $limit = false;
    if (isset($params['limit'])) {
        $limit = $params['limit'];
    }
    //过滤相关运算符
    foreach ($params as $filter => $value) {
        $compare_sign = false;
        $compare_value = false;
        if (is_string($value)) {
            if (stristr($value, '[lt]')) {
                $compare_sign = '<';
                $value = str_replace('[lt]', '', $value);
            } elseif (stristr($value, '[lte]')) {
                $compare_sign = '<=';
                $value = str_replace('[lte]', '', $value);
            } elseif (stristr($value, '[st]')) {
                $compare_sign = '<';
                $value = str_replace('[st]', '', $value);
            } elseif (stristr($value, '[ste]')) {
                $compare_sign = '<=';
                $value = str_replace('[ste]', '', $value);
            } elseif (stristr($value, '[gt]')) {
                $compare_sign = '>';
                $value = str_replace('[gt]', '', $value);
            } elseif (stristr($value, '[gte]')) {
                $compare_sign = '>=';
                $value = str_replace('[gte]', '', $value);
            } elseif (stristr($value, '[mt]')) {
                $compare_sign = '>';
                $value = str_replace('[mt]', '', $value);
            } elseif (stristr($value, '[md]')) {
                $compare_sign = '>';
                $value = str_replace('[md]', '', $value);
            } elseif (stristr($value, '[mte]')) {
                $compare_sign = '>=';
                $value = str_replace('[mte]', '', $value);
            } elseif (stristr($value, '[mde]')) {
                $compare_sign = '>=';
                $value = str_replace('[mde]', '', $value);
            } elseif (stristr($value, '[neq]')) {
                $compare_sign = '!=';
                $value = str_replace('[neq]', '', $value);
            } elseif (stristr($value, '[eq]')) {
                $compare_sign = '=';
                $value = str_replace('[eq]', '', $value);
            } elseif (stristr($value, '[int]')) {
                $value = str_replace('[int]', '', $value);
                $value = intval($value);
            } elseif (stristr($value, '[is]')) {
                $compare_sign = '=';
                $value = str_replace('[is]', '', $value);
            } elseif (stristr($value, '[like]')) {
                $compare_sign = 'LIKE';
                $value = str_replace('[like]', '', $value);
                $compare_value = '%' . $value . '%';
            } elseif (stristr($value, '[not_like]')) {
                $value = str_replace('[not_like]', '', $value);
                $compare_sign = 'NOT LIKE';
                $compare_value = '%' . $value . '%';
            } elseif (stristr($value, '[is_not]')) {
                $value = str_replace('[is_not]', '', $value);
                $compare_sign = 'NOT LIKE';
                $compare_value = '%' . $value . '%';
            } elseif (stristr($value, '[in]')) {
                $value = str_replace('[in]', '', $value);
                $compare_sign = 'in';
            } elseif (stristr($value, '[not_in]')) {
                $value = str_replace('[not_in]', '', $value);
                $compare_sign = 'not_in';
            } elseif (strtolower($value) == '[null]') {
                $value = str_replace('[null]', '', $value);
                $compare_sign = 'null';
            } elseif (strtolower($value) == '[not_null]') {
                $value = str_replace('[not_null]', '', $value);
                $compare_sign = 'not_null';
            }
            if ($filter == 'created_at' or $filter == 'updated_at') {
                $compare_value = date('Y-m-d H:i:s', strtotime($value));
            }
        }
        switch ($filter) {
            case 'fields':
                if ($value != false and is_string($value)) {
                    $value = explode(',', $value);
                }
                if (is_array($value) and !empty($value)) {
                    $query = $query->select($value);
                }
                unset($params[$filter]);
                break;
            case 'order_by':
                $order_by_criteria = explode(',', $value);
                foreach ($order_by_criteria as $c) {
                    $c = urldecode($c);
                    $c = explode(' ', $c);
                    if (isset($c[0]) and trim($c[0]) != '') {
                        $c[0] = trim($c[0]);
                        if (isset($c[1])) {
                            $c[1] = trim($c[1]);
                        }
                        if (isset($c[1]) and ($c[1]) != '') {
                            $query = $query->orderBy($c[0], $c[1]);
                        } elseif (isset($c[0])) {
                            $query = $query->orderBy($c[0]);
                        }
                    }
                }
                unset($params[$filter]);
                break;
            case 'group_by':
                $group_by_criteria = explode(',', $value);
                if (!empty($group_by_criteria)) {
                    $group_by_criteria = array_map('trim', $group_by_criteria);
                }
                $query = $query->groupBy($group_by_criteria);
                unset($params[$filter]);
                break;
            case 'limit':
                $query = $query->take(intval($value));
                unset($params['limit']);
                break;
            case 'current_page':
                $criteria = 0;
                if ($value > 1) {
                    if ($limit != false) {
                        $criteria = intval($value - 1) * intval($limit);
                    }
                }
                if ($criteria >= 1) {
                    $query = $query->skip($criteria);
                }
                unset($params[$filter]);
                break;
            case 'ids':
                $ids = $value;
                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                } elseif (is_int($ids)) {
                    $ids = array($ids);
                }
                if (isset($ids) and is_array($ids) == true) {
                    foreach ($ids as $idk => $idv) {
                        $ids[$idk] = intval($idv);
                    }
                }
                if (is_array($ids)) {
                    $query = $query->whereIn($table . '.id', $ids);
                }
                unset($params[$filter]);
                break;
            case 'remove_ids':
            case 'exclude_ids':
                unset($params[$filter]);
                $ids = $value;
                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                } elseif (is_int($ids)) {
                    $ids = array($ids);
                }

                if (isset($ids) and is_array($ids) == true) {
                    foreach ($ids as $idk => $idv) {
                        $ids[$idk] = intval($idv);
                    }
                }
                if (is_array($ids)) {
                    $query = $query->whereNotIn($table . '.id', $ids);
                }

                break;
            case 'id':
                unset($params[$filter]);
                $criteria = trim($value);
                if ($compare_sign != false) {
                    if ($compare_value != false) {
                        $val = $compare_value;
                    } else {
                        $val = $value;
                    }

                    $query = $query->where($table . '.id', $compare_sign, $val);
                } else {
                    $query = $query->where($table . '.id', $criteria);
                }
                break;
            case 'search_params':
                $search_items = $value;


                if (is_array($search_items) and !empty($search_items)) {
                    foreach ($search_items as $search_item_key => $search_key_value)
                        switch ($search_item_key) {
                            case 'custom_field':
                                if (is_array($search_key_value) and !empty($search_key_value)) {
                                    // $query =  $query->select('content_fields.id');
                                    $query = $query->join('content_fields', 'content_fields.rel_id', '=', $table . '.id')
                                        ->where('content_fields.rel_type', $table);


                                    $query = $query->join('content_fields_values', function ($join) use ($table, $search_key_value, $query) {
                                        $join->on('content_fields_values.custom_field_id', '=', 'content_fields.id');
                                        foreach ($search_key_value as $search_key_value_k => $search_key_value_v) {
                                            //  $join->on('content_fields.name_key', '=', $search_key_value_k);
                                            //   $join->on('content_fields.name_key', '=', $search_key_value_k);
                                            $join->on('content_fields_values.value', '=', $search_key_value_v);
                                        }

                                    });

                                    $query = $query->join('content_fields as content_fields_names_q', function ($join) use ($table, $search_key_value, $query) {

                                        foreach ($search_key_value as $search_key_value_k => $search_key_value_v) {
                                            $join->orOn('content_fields_names_q.name_key', '=', $search_key_value_k);
                                            $join->orOn('content_fields_names_q.name', '=', $search_key_value_k);
                                            $join->orOn('content_fields_names_q.type', '=', $search_key_value_k);
                                        }

                                    });

                                    $query = $query->distinct();

                                }

                                break;
                        }

                }

                break;
            case 'between':
                $fields = $value;
                if ($fields != false and is_string($fields)) {
                    $fields = explode('|', $fields);
                }
                if (is_array($fields) and !empty($fields)) {
                    if(!empty($fields[1])){
                        $param=explode(',',$fields[1]);
                        $query = $query->whereBetween($fields[0],$param);
                    }
                }
                unset($params[$filter]);
                break;
            case 'orwhere':
                $fields = $value;
                if ($fields != false and is_string($fields)) {
                    $fields = explode('|', $fields);
                }
                if (is_array($fields) and !empty($fields)) {
                    $query = $query->orwhere($fields[0],$fields[1]);
                }
                unset($params[$filter]);
                break;
            default:
                if ($compare_sign != false) {
                    unset($params[$filter]);
                    if ($compare_value != false) {
                        $query = $query->where($table . '.' . $filter, $compare_sign, $compare_value);
                    } else {
                        if ($compare_sign == 'null' || $compare_sign == 'not_null') {
                            if ($compare_sign == 'null') {
                                $query = $query->whereNull($table . '.' . $filter);
                            }
                            if ($compare_sign == 'not_null') {
                                $query = $query->whereNotNull($table . '.' . $filter);
                            }
                        } else if ($compare_sign == 'in' || $compare_sign == 'not_in') {
                            if (is_string($value)) {
                                $value = explode(',', $value);
                            } elseif (is_int($value)) {
                                $value = array($value);
                            }
                            if (is_array($value)) {
                                if ($compare_sign == 'in') {
                                    $query = $query->whereIn($table . '.' . $filter, $value);
                                } elseif ($compare_sign == 'not_in') {
                                    $query = $query->whereIn($table . '.' . $filter, $value);
                                }
                            }
                        } else {
                            $query = $query->where($table . '.' . $filter, $compare_sign, $value);
                        }
                    }
                }
                break;
        }
    }
    return $query;
}