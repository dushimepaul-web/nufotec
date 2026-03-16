<?php
if (!function_exists('generate_uuid')) {
    function generate_uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('hash_password')) {
    function hash_password($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

if (!function_exists('verify_password')) {
    function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

if (!function_exists('generate_token')) {
    function generate_token($length = 64)
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('clean_input')) {
    function clean_input($data)
    {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generate_2fa_secret')) {
    function generate_2fa_secret()
    {
        return bin2hex(random_bytes(10));
    }
}

if (!function_exists('check_csrf_token')) {
    function check_csrf_token($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token()
    {
        if(session_status() !== PHP_SESSION_ACTIVE) session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}

if (!function_exists('generate_consultation_number')) {
    function generate_consultation_number()
    {
        return 'CNS-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}