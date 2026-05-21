<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AntiBan {
    
    private $CI;
    private $settings = [];
    private $message_count = 0;
    private $last_send_time = null;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->load_settings();
    }
    
    private function load_settings() {
        // Valeurs par défaut
        $defaults = [
            'min_delay_micro' => '500000',
            'max_delay_micro' => '1500000',
            'min_delay_seconds' => '2',
            'max_delay_seconds' => '4',
            'long_pause_probability' => '20',
            'long_pause_min' => '5',
            'long_pause_max' => '10',
            'batch_size' => '5',
            'batch_interval' => '60',
            'max_messages_per_hour' => '60'
        ];
        
        // Essayer de charger depuis la base de données (si la table existe)
        if ($this->CI->db->table_exists('antiban_settings')) {
            $query = $this->CI->db->get('antiban_settings');
            foreach ($query->result() as $row) {
                if (isset($row->setting_key)) {
                    $this->settings[$row->setting_key] = $row->setting_value;
                }
            }
        }
        
        // Remplir les valeurs manquantes avec les defaults
        foreach ($defaults as $key => $value) {
            if (!isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }
    }
    
    public function smart_delay($is_media = false) {
        if ($is_media) {
            usleep(rand(1000000, 3000000));
            sleep(rand(3, 8));
        } else {
            $min_micro = (int)$this->settings['min_delay_micro'];
            $max_micro = (int)$this->settings['max_delay_micro'];
            $min_sec = (int)$this->settings['min_delay_seconds'];
            $max_sec = (int)$this->settings['max_delay_seconds'];
            
            usleep(rand($min_micro, $max_micro));
            sleep(rand($min_sec, $max_sec));
        }
        
        $prob = (int)$this->settings['long_pause_probability'];
        if (rand(1, 100) <= $prob) {
            $pause_min = (int)$this->settings['long_pause_min'];
            $pause_max = (int)$this->settings['long_pause_max'];
            sleep(rand($pause_min, $pause_max));
        }
        
        $this->message_count++;
        $this->last_send_time = microtime(true);
    }
    
    public function can_send($phone_number = null) {
        $max = (int)$this->settings['max_messages_per_hour'];
        return $this->message_count < $max;
    }
    
    public function batch_delay($batch_number) {
        if ($batch_number > 1) {
            $interval = (int)$this->settings['batch_interval'];
            sleep(max(5, $interval));
        }
    }
    
    public function reset_counter() {
        $this->message_count = 0;
    }
    
    public function get_stats() {
        return [
            'message_count' => $this->message_count,
            'last_send_time' => $this->last_send_time,
            'settings' => $this->settings
        ];
    }
}