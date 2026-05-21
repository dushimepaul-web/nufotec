<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_phone')) {
    function format_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 1) != '6') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}

if (!function_exists('random_delay')) {
    function random_delay($min = 5, $max = 15) {
        $delay = rand($min, $max);
        usleep($delay * 1000000);
        return $delay;
    }
}

if (!function_exists('smart_delay')) {
    function smart_delay() {
        $hour = date('H');
        
        // Délais intelligents selon l'heure
        if ($hour >= 22 || $hour < 6) {
            // Nuit : plus lent (15-30 secondes)
            $delay = rand(15, 30);
        } elseif ($hour >= 12 && $hour <= 14) {
            // Heure de pointe : moyen (8-20 secondes)
            $delay = rand(8, 20);
        } else {
            // Journée normale : standard (5-15 secondes)
            $delay = rand(5, 15);
        }
        
        // Variation aléatoire ±20%
        $variation = $delay * (rand(-20, 20) / 100);
        $final_delay = max(3, $delay + $variation);
        
        usleep($final_delay * 1000000);
        return $final_delay;
    }
}

if (!function_exists('sanitize_message')) {
    function sanitize_message($message) {
        $message = strip_tags($message);
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $message = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $message);
        return trim($message);
    }
}

if (!function_exists('contains_link')) {
    function contains_link($message) {
        $pattern = '/(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?)/';
        return preg_match($pattern, $message);
    }
}

if (!function_exists('contains_mention')) {
    function contains_mention($message) {
        return preg_match('/@[a-zA-Z0-9]+/', $message);
    }
}

if (!function_exists('contains_phone')) {
    function contains_phone($message) {
        return preg_match('/[0-9]{10,}/', $message);
    }
}

if (!function_exists('log_whatsapp')) {
    function log_whatsapp($order_request_id, $product_id, $phone_number, $message_content, $message_type, $status, $error_message = null) {
        $CI = &get_instance();
        $data = array(
            'order_request_id' => $order_request_id,
            'product_id' => $product_id,
            'phone_number' => format_phone($phone_number),
            'message_content' => $message_content,
            'message_type' => $message_type,
            'status' => $status,
            'error_message' => $error_message,
            'sent_at' => date('Y-m-d H:i:s')
        );
        return $CI->db->insert('whatsapp_logs', $data);
    }
}

if (!function_exists('media_type_detect')) {
    function media_type_detect($file_path) {
        $mime_types = array(
            'image' => array('image/jpeg', 'image/png', 'image/gif', 'image/webp'),
            'video' => array('video/mp4', 'video/webm', 'video/quicktime'),
            'audio' => array('audio/mpeg', 'audio/ogg', 'audio/wav'),
            'document' => array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'sticker' => array('image/webp')
        );
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        foreach ($mime_types as $type => $mimes) {
            if (in_array($mime, $mimes)) {
                return $type;
            }
        }
        return 'document';
    }
}

if (!function_exists('is_admin_number')) {
    function is_admin_number($phone) {
        $CI = &get_instance();
        $CI->load->library('whapi_library');
        $admins = $CI->whapi_library->get_setting('admin_numbers');
        $admin_numbers = json_decode($admins, true) ?: [];
        return in_array(format_phone($phone), $admin_numbers);
    }
}

if (!function_exists('parse_template')) {
    function parse_template($content, $variables = []) {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}