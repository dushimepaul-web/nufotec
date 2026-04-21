<?php
if (!function_exists('t')) {
    function t($key, $params = []) {
        $CI =& get_instance();
        $text = $CI->lang->line($key);
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $text = str_replace('{' . $k . '}', $v, $text);
            }
        }
        return !empty($text) ? $text : $key;
    }
}