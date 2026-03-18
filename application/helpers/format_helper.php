<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2) {
        if ($bytes === 0 || $bytes === null || $bytes === '') return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((int)$bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        if ($pow < 0) $pow = 0;
        
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}