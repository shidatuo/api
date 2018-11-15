<?php
/**
 * @param $arr
 * @param string $ul_tag
 * @param string $li_tag
 * @return string
 * @author shidatuo
 * @description 数组转化成ul标签与li标签
 */
function array_to_ul($arr, $ul_tag = 'ul', $li_tag = 'li'){
    $has_items = false;
    $retStr = '<' . $ul_tag . '>';
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            if (!is_array($key) and $key and $val) {
                $key = str_replace('_', ' ', $key);
                $key = ucwords($key);

                if (is_array($val)) {
                    if (!empty($val)) {
                        $has_items = true;
                        if (is_numeric($key)) {
                            $retStr .= '<' . $ul_tag . '>';
                            $retStr .= '<' . $li_tag . '>' . $this->array_to_ul($val, $ul_tag, $li_tag) . '</' . $li_tag . '>';
                            $retStr .= '</' . $ul_tag . '>';
                        } else {
                            $retStr .= '<' . $li_tag . '>' . $key . ': ' . $this->array_to_ul($val, $ul_tag, $li_tag) . '</' . $li_tag . '>';
                        }
                    }
                } else {
                    if (is_string($val) != false and trim($val) != '') {
                        $has_items = true;

                        $retStr .= '<' . $li_tag . '>' . $key . ': ' . $val . '</' . $li_tag . '>';
                    }
                }
            } else {
                if (!empty($val)) {
                    $has_items = true;
                    $retStr .= $this->array_to_ul($val, $ul_tag, $li_tag);
                }
            }
        }
    }
    $retStr .= '</' . $ul_tag . '>';
    if ($has_items) {
        return $retStr;
    }
}
/**
 * @param $variable
 * @return array
 * @author shidatuo
 * @description 数组里面的值去空格
 */
function array_trim($variable){
    $result = array_map('trim', $variable);
    return $result;
}
/**
 * @param $variable
 * @return array|string
 * @author shidatuo
 * @description 递归返回转义后的字符
 */
function add_slashes_recursive($variable){
    if (is_string($variable)) {
        return addslashes($variable);
    } elseif (is_array($variable)) {
        foreach ($variable as $i => $value) {
            $variable[$i] = add_slashes_recursive($value);
        }
    }
    return $variable;
}
/**
 * @param $variable
 * @return array|string
 * @author shidatuo
 * @description 反引用一个引用字符串 (与如上方法相反)
 */
function strip_slashes_recursive($variable){
    if (is_string($variable)) {
        return stripslashes($variable);
    }
    if (is_array($variable)) {
        foreach ($variable as $i => $value) {
            $variable[$i] = strip_slashes_recursive($value);
        }
    }
    return $variable;
}
/**
 * @param $text
 * @return null|string|string[]
 * @author shidatuo
 * @description http://stackoverflow.com/a/1971451/731166 自动生成文本链接
 */
function auto_link_text($text) {
    $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
    return preg_replace_callback($pattern, 'auto_link_text_callback', $text);
}
function auto_link_text_callback($matches) {
    $max_url_length = 50;
    $max_depth_if_over_length = 2;
    $ellipsis = '&hellip;';

    $url_full = $matches[0];
    $url_short = '';
    if (strlen($url_full) > $max_url_length) {
        $parts = parse_url($url_full);
        $url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';
        $path_components = explode('/', trim($parts['path'], '/'));
        foreach ($path_components as $dir) {
            $url_string_components[] = $dir . '/';
        }
        if (!empty($parts['query'])) {
            $url_string_components[] = '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $url_string_components[] = '#' . $parts['fragment'];
        }
        for ($k = 0; $k < count($url_string_components); $k++) {
            $curr_component = $url_string_components[$k];
            if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
                if ($k == 0 && strlen($url_short) < $max_url_length) {
                    // Always show a portion of first directory
                    $url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
                }
                $url_short .= $ellipsis;
                break;
            }
            $url_short .= $curr_component;
        }

    } else {
        $url_short = $url_full;
    }
//    return "<a rel=\"nofollow\" href=\"$url_full\">$url_short</a>";
//    return "<a rel=\"nofollow\" href=\"$url_full\" target="_blank">$url_short</a>";
    return "<a href=\"$url_full\">$url_short</a>";
}
/**
 * @param $size                字节数
 * @param string $delimiter    数字和单位分隔符
 * @return string              格式化后的带单位的大小
 * @author shidatuo
 * @description 格式化字节大小
 */
function format_filesize($bytes, $dec = 2){
    $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}
/**
 * @param $size                字节数
 * @param string $delimiter    数字和单位分隔符
 * @return string              格式化后的带单位的大小
 * @author shidatuo
 * @description 格式化字节大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
/**
 * @param $input
 * @return array|null|string|string[]
 * @author shidatuo
 * @description 清空scripts标签
 */
function clean_scripts($input){
    if (is_array($input)) {
        $output = array();
        foreach ($input as $var => $val) {
            $output[$var] = clean_scripts($val);
        }
    } elseif (is_string($input)) {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<![\s\S]*?--[ \t\n\r]*>@', // Strip multi-line comments
        );
        $output = preg_replace($search, '', $input);
    } else {
        return $input;
    }
    return $output;
}

/**
 * @param $string
 * @param bool $img
 * @return array|null|string|string[]
 * @author 删除不安全的 html 标签
 */
function strip_unsafe($string, $img = false){
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = strip_unsafe($val, $img);
        }
        return $string;
    } else {
        // Unsafe HTML tags that members may abuse  (成员可能滥用的不安全HTML标签)
        $unsafe = array(
            '/<iframe(.*?)<\/iframe>/is',
            '/<title(.*?)<\/title>/is',
            //'/<pre(.*?)<\/pre>/is',
            '/<audio(.*?)<\/audio>/is',
            '/<video(.*?)<\/video>/is',
            '/<frame(.*?)<\/frame>/is',
            '/<frameset(.*?)<\/frameset>/is',
            '/<object(.*?)<\/object>/is',
            '/<script(.*?)<\/script>/is',
            '/<embed(.*?)<\/embed>/is',
            '/<applet(.*?)<\/applet>/is',
            '/<meta(.*?)>/is',
            '/<!doctype(.*?)>/is',
            '/<link(.*?)>/is',
            '/<style(.*?)<\/style>/is',
            '/<body(.*?)>/is',
            '/<\/body>/is',
            '/<head(.*?)>/is',
            '/<\/head>/is',
            '/onload="(.*?)"/is',
            '/onunload="(.*?)"/is',
            '/onafterprint="(.*?)"/is',
            '/onbeforeprint="(.*?)"/is',
            '/onbeforeunload="(.*?)"/is',
            '/onerrorNew="(.*?)"/is',
            '/onhaschange="(.*?)"/is',
            '/onoffline="(.*?)"/is',
            '/ononline="(.*?)"/is',
            '/onpagehide="(.*?)"/is',
            '/onpageshow="(.*?)"/is',
            '/onpopstate="(.*?)"/is',
            '/onredo="(.*?)"/is',
            '/onresize="(.*?)"/is',
            '/onstorage="(.*?)"/is',
            '/onundo="(.*?)"/is',
            '/onunload="(.*?)"/is',
            '/onblur="(.*?)"/is',
            '/onchange="(.*?)"/is',
            '/oncontextmenu="(.*?)"/is',
            '/onfocus="(.*?)"/is',
            '/onformchange="(.*?)"/is',
            '/onforminput="(.*?)"/is',
            '/oninput="(.*?)"/is',
            '/oninvalid="(.*?)"/is',
            '/onreset="(.*?)"/is',
            '/onselect="(.*?)"/is',
            '/onblur="(.*?)"/is',
            '/onsubmit="(.*?)"/is',
            '/onkeydown="(.*?)"/is',
            '/onkeypress="(.*?)"/is',
            '/onkeyup="(.*?)"/is',
            '/onclick="(.*?)"/is',
            '/ondblclick="(.*?)"/is',
            '/ondrag="(.*?)"/is',
            '/ondragend="(.*?)"/is',
            '/ondragenter="(.*?)"/is',
            '/ondragleave="(.*?)"/is',
            '/ondragover="(.*?)"/is',
            '/ondragstart="(.*?)"/is',
            '/ondrop="(.*?)"/is',
            '/onmousedown="(.*?)"/is',
            '/onmousemove="(.*?)"/is',
            '/onmouseout="(.*?)"/is',
            '/onmouseover="(.*?)"/is',
            '/onmousewheel="(.*?)"/is',
            '/onmouseup="(.*?)"/is',
            '/ondragleave="(.*?)"/is',
            '/onabort="(.*?)"/is',
            '/oncanplay="(.*?)"/is',
            '/oncanplaythrough="(.*?)"/is',
            '/ondurationchange="(.*?)"/is',
            '/onended="(.*?)"/is',
            '/onerror="(.*?)"/is',
            '/onloadedmetadata="(.*?)"/is',
            '/onloadstart="(.*?)"/is',
            '/onpause="(.*?)"/is',
            '/onplay="(.*?)"/is',
            '/onabort="(.*?)"/is',
            '/onplaying="(.*?)"/is',
            '/onprogress="(.*?)"/is',
            '/onratechange="(.*?)"/is',
            '/onreadystatechange="(.*?)"/is',
            '/onseeked="(.*?)"/is',
            '/onseeking="(.*?)"/is',
            '/onstalled="(.*?)"/is',
            '/onsuspend="(.*?)"/is',
            '/ontimeupdate="(.*?)"/is',
            '/onvolumechange="(.*?)"/is',
            '/onwaiting="(.*?)"/is',
            '/href="javascript:[^"]+"/',
            '/href=javascript:/is',
            '/<html(.*?)>/is',
            '/<iframe(.*?)>/is',
            '/<iframe(.*?)/is',
            '/<\/html>/is',);
        // Remove graphic too if the user wants (也可以删除图片)
        if ($img == true) {
            $unsafe[] = '/<img(.*?)>/is';
        }
        // Remove these tags and all parameters within them (删除这些标记和它们中的所有参数)
        $string = preg_replace($unsafe, '', $string);
        return $string;
    }
}
/**
 * @param $string
 * @param $start
 * @param $end
 * @return bool|string
 * @author shidatuo
 * @description 截取字符串中间的  string_between("zhouwang",'z','a'); 'houw'
 */
function string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) {
        return '';
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}



    function replace_once($needle, $replace, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }

        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    function prep_url($str = '')
    {
        if ($str === 'http://' or $str === 'https://' or $str === '') {
            return '';
        }
        $url = parse_url($str);
        if (!$url or !isset($url['scheme'])) {
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                return 'https://' . $str;
            } else {
                return 'http://' . $str;
            }
        }

        return $str;
    }

    function percent($num_amount, $num_total, $format = true)
    {
        if ($num_amount == 0 or $num_total == 0) {
            return 0;
        }
        $count1 = $num_amount / $num_total;
        $count2 = $count1 * 100;

        if (!$format) {
            return $count2;
        }
        $count = number_format($count2, 0);

        return $count;
    }

    /**
     * Encodes a variable with json_encode and base64_encode.
     *
     * @param mixed $var Your $var
     *
     * @return string Your encoded $var
     *
     * @category Strings
     *
     * @see      $this->base64_to_array()
     */
    function array_to_base64($var)
    {
        if ($var == '') {
            return '';
        }

        $var = json_encode($var);
        $var = base64_encode($var);

        return $var;
    }

    /**
     * Decodes a variable with base64_decode and json_decode.
     *
     * @param string $var Your var that has been put trough encode_var
     *
     * @return string|array Your encoded $var
     *
     * @category Strings
     *
     * @see      $this->array_to_base64()
     */
    function base64_to_array($var)
    {
        if (is_array($var)) {
            return $var;
        }
        if ($var == '') {
            return false;
        }

        $var = base64_decode($var);
        try {
            $var = @json_decode($var, 1);
        } catch (Exception $exc) {
            return false;
        }

        return $var;
    }

    function titlelize($string)
    {
        $slug = preg_replace('/-/', ' ', $string);
        $slug = preg_replace('/_/', ' ', $slug);
        $slug = ucwords($slug);

        return $slug;
    }

//    function array_values($ary)
//    {
//        $lst = array();
//        foreach (array_keys($ary) as $k) {
//            $v = $ary[$k];
//            if (is_scalar($v)) {
//                $lst[] = $v;
//            } elseif (is_array($v)) {
//                $lst = array_merge($lst, array_values($v));
//            }
//        }
//
//        return $lst;
//    }

    function lipsum($number_of_characters = false)
    {
        if ($number_of_characters == false) {
            $number_of_characters = 100;
        }
        $lipsum = array();
        $rand = rand(0, (sizeof($lipsum) - 1));

        return $this->limit($lipsum[$rand], $number_of_characters, '');
    }

    /**
     * Limits a string to a number of characters.
     *
     * @param        $str
     * @param int $n
     * @param string $end_char
     *
     * @return string
     *
     * @category Strings
     */
    function limit($str, $n = 500, $end_char = '&#8230;')
    {
        if (strlen($str) < $n) {
            return $str;
        }
        $str = strip_tags($str);
        $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));
        if (strlen($str) <= $n) {
            return $str;
        }
        $out = '';
        foreach (explode(' ', trim($str)) as $val) {
            $out .= $val . ' ';
            if (strlen($out) >= $n) {
                $out = trim($out);

                return (strlen($out) == strlen($str)) ? $out : $out . $end_char;
            }
        }
    }

    function random_color()
    {
        return '#' . sprintf('%02X%02X%02X', mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

    function lnotif($text, $class = 'success')
    {
        $editmode_sess = xn()->user_manager->session_get('editmode');

        if ($editmode_sess == false) {
            if (defined('IN_EDITOR_TOOLS') and IN_EDITOR_TOOLS != false) {
                $editmode_sess = true;
            }
        }

        if ($editmode_sess == true) {
            return $this->notif($text, $class);
        }
    }

    function notif($text, $class = 'success')
    {
        if ($class === true) {
            $to_print = '<div><div class="xn-notification-text xn-open-module-settings">';
            $to_print = $to_print . ($text) . '</div></div>';
        } else {
            $to_print = '<div class="xn-notification xn-' . $class . ' "><div class="xn-notification-text xn-open-module-settings">';
            $to_print = $to_print . $text . '</div></div>';
        }

        return $to_print;
    }

    function no_dashes($string)
    {
        $slug = preg_replace('/-/', ' ', $string);
        $slug = preg_replace('/_/', ' ', $slug);

        return $slug;
    }

    function unvar_dump($str)
    {
        if (strpos($str, "\n") === false) {
            //Add new lines:
            $regex = array(
                '#(\\[.*?\\]=>)#',
                '#(string\\(|int\\(|float\\(|array\\(|NULL|object\\(|})#',
            );
            $str = preg_replace($regex, "\n\\1", $str);
            $str = trim($str);
        }
        $regex = array(
            '#^\\040*NULL\\040*$#m',
            '#^\\s*array\\((.*?)\\)\\s*{\\s*$#m',
            '#^\\s*string\\((.*?)\\)\\s*(.*?)$#m',
            '#^\\s*int\\((.*?)\\)\\s*$#m',
            '#^\\s*float\\((.*?)\\)\\s*$#m',
            '#^\\s*\[(\\d+)\\]\\s*=>\\s*$#m',
            '#\\s*?\\r?\\n\\s*#m',
        );
        $replace = array(
            'N',
            'a:\\1:{',
            's:\\1:\\2',
            'i:\\1',
            'd:\\1',
            'i:\\1',
            ';',
        );
        $serialized = preg_replace($regex, $replace, $str);
        $func = create_function(
            '$match',
            'return "s:".strlen($match[1]).":\\"".$match[1]."\\"";'
        );
        $serialized = preg_replace_callback(
            '#\\s*\\["(.*?)"\\]\\s*=>#',
            $func,
            $serialized
        );
        $func = create_function(
            '$match',
            'return "O:".strlen($match[1]).":\\"".$match[1]."\\":".$match[2].":{";'
        );
        $serialized = preg_replace_callback(
            '#object\\((.*?)\\).*?\\((\\d+)\\)\\s*{\\s*;#',
            $func,
            $serialized
        );
        $serialized = preg_replace(
            array('#};#', '#{;#'),
            array('}', '{'),
            $serialized
        );

        return unserialize($serialized);
    }

    function is_base64($data)
    {
        $decoded = base64_decode($data, true);
        if (false === $decoded || base64_encode($decoded) != $data) {
            return false;
        }

        return true;
    }
    function is_fqdn($FQDN)
    {
        return !empty($FQDN) && preg_match('/(?=^.{1,254}$)(^(?:(?!\d|-)[a-z0-9\-]{1,63}(?<!-)\.)+(?:[a-z]{2,})$)/i', $FQDN) > 0;
    }

    function render_item_content_fields_data($item)
    {
        if (isset($item['content_fields_data']) and $item['content_fields_data'] != '') {
            $item['content_fields_data'] = $this->base64_to_array($item['content_fields_data']);
            if (isset($item['content_fields_data']) and is_array($item['content_fields_data']) and !empty($item['content_fields_data'])) {
                $tmp_val = $this->array_to_ul($item['content_fields_data']);
                $item['content_fields'] = $tmp_val;
            }
        }

        return $item;
    }


//    function encrypt($string)
//    {
//        return Crypt::encrypt($string);
//    }

//    function decrypt($string)
//    {
//        return Crypt::decrypt($string);
//    }


    function encode_ids($data)
    {
        $hashids = new \Hashids\Hashids();
        return $hashids->encode($data);;
    }

    function decode_ids($data)
    {
        $hashids = new \Hashids\Hashids();
        return $hashids->decode($data);
    }



    function split_dates($min, $max, $parts = 7, $output = "Y-m-d") {
        $dataCollection[] = date($output, strtotime($min));
        $diff = (strtotime($max) - strtotime($min)) / $parts;
        $convert = strtotime($min) + $diff;

        for ($i = 1; $i < $parts; $i++) {
            $dataCollection[] = date($output, $convert);
            $convert += $diff;
        }
        $dataCollection[] = date($output, strtotime($max));
        return $dataCollection;
    }


    /**
     * 内容转换成图片
     * @param $text
     * @return string
     */
    function text_to_image($text)
    {
        $options = array();
        if (is_array($text)) {
            $options = $text;
            if (isset($options['text'])) {
                $text = $options['text'];
            } else {
                $text = 'Hello world!';
            }

        }


        $simple_text_image = new lib\SimpleTextImage($text);
        if (isset($options['font_size'])) {
            $simple_text_image->setFontSize(intval($options['font_size']));
        }

        if (isset($options['padding'])) {
            $simple_text_image->setPadding(intval($options['padding']));
        }

        if (isset($options['bg_color'])) {
            $color = $options['bg_color'];
            $rgb = $this->hex_to_rgb($color);
            $simple_text_image->setBackground($rgb['r'],$rgb['g'],$rgb['b']);
        }

        if (isset($options['fg_color'])) {
            $color = $options['fg_color'];
            $rgb = $this->hex_to_rgb($color);
            $simple_text_image->setForeground($rgb['r'],$rgb['g'],$rgb['b']);
        }

        // Enable output buffering
        ob_start();
        $simple_text_image->render('png');
        $imagedata = ob_get_contents();

        ob_end_clean();


        return 'data:image/png;base64,' . base64_encode($imagedata);


    }

    /**
     *
     * @param $hex
     * @param bool $alpha
     * @return mixed
     */
    function hex_to_rgb($hex, $alpha = false)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 6) {
            $rgb['r'] = hexdec(substr($hex, 0, 2));
            $rgb['g'] = hexdec(substr($hex, 2, 2));
            $rgb['b'] = hexdec(substr($hex, 4, 2));
        } else if (strlen($hex) == 3) {
            $rgb['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $rgb['r'] = '0';
            $rgb['g'] = '0';
            $rgb['b'] = '0';
        }
        if ($alpha) {
            $rgb['a'] = $alpha;
        }
        return $rgb;
    }

