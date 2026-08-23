<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('notify_ifttt')) {
    function notify_ifttt($data, $file_path = null) {
        log_message('info', 'IFTTT notification skipped: helper not configured');
        return true;
    }
}
