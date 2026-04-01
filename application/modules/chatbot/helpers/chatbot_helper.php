<?php
if (!function_exists('format_phone')) {
    function format_phone($phone) {
        $phone = str_replace('@s.whatsapp.net', '', $phone);
        return $phone;
    }
}

if (!function_exists('is_admin')) {
    function is_admin($phone) {
        $CI =& get_instance();
        $CI->config->load('chatbot/config', TRUE);
        $admins = $CI->config->item('admin_phones', 'chatbot/config');
        return in_array($phone, $admins);
    }
}