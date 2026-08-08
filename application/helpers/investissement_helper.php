<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_montant')) {
    function format_montant($montant, $devise = 'USD')
    {
        $symbol = $devise == 'USD' ? '$' : ($devise == 'EUR' ? '€' : $devise);
        return $symbol . ' ' . number_format($montant, 2, ',', ' ');
    }
}

if (!function_exists('get_allocation_array')) {
    function get_allocation_array($json_data)
    {
        if (empty($json_data)) return array();
        $data = json_decode($json_data, true);
        return is_array($data) ? $data : array();
    }
}

if (!function_exists('get_allocation_color')) {
    function get_allocation_color($key)
    {
        $colors = [
            'construction' => 'primary',
            'lab_equipment' => 'info',
            'processing_lines' => 'success',
            'cleanrooms' => 'warning',
            'regulatory' => 'danger',
            'working_capital' => 'secondary',
            'r_and_d' => 'dark',
            'marketing' => 'primary',
            'infrastructure' => 'info'
        ];

        return $colors[$key] ?? 'secondary';
    }
}

if (!function_exists('get_allocation_label')) {
    function get_allocation_label($key)
    {
        $labels = [
            'construction' => 'Construction',
            'lab_equipment' => 'Équipement labo',
            'processing_lines' => 'Lignes production',
            'cleanrooms' => 'Salles propres',
            'regulatory' => 'Réglementaire',
            'working_capital' => 'Fonds de roulement',
            'r_and_d' => 'R&D',
            'marketing' => 'Marketing',
            'infrastructure' => 'Infrastructure'
        ];

        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}