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
        if ($this->CI->db->table_exists('antiban_settings')) {
            $query = $this->CI->db->get('antiban_settings');
            foreach ($query->result() as $row) {
                $this->settings[$row->setting_key] = $row->setting_value;
            }
        }
        
        $defaults = [
            'min_delay_micro' => 500000,
            'max_delay_micro' => 1500000,
            'min_delay_seconds' => 2,
            'max_delay_seconds' => 4,
            'long_pause_probability' => 20,
            'long_pause_min' => 5,
            'long_pause_max' => 10,
            'batch_size' => 5,
            'batch_interval' => 60,
            'max_messages_per_hour' => 60
        ];
        
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
            usleep(rand((int)$this->settings['min_delay_micro'], (int)$this->settings['max_delay_micro']));
            sleep(rand((int)$this->settings['min_delay_seconds'], (int)$this->settings['max_delay_seconds']));
        }
        
        if (rand(1, 100) <= (int)$this->settings['long_pause_probability']) {
            $pause = rand((int)$this->settings['long_pause_min'], (int)$this->settings['long_pause_max']);
            sleep($pause);
        }
        
        $this->message_count++;
        $this->last_send_time = microtime(true);
    }
    
    public function can_send($phone_number = null) {
        $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $this->CI->db->where('created_at >', $hour_ago);
        if ($phone_number) {
            $this->CI->db->where('sender_number', $phone_number);
        }
        $count = $this->CI->db->count_all_results('wa_messages_queue');
        
        return $count < (int)$this->settings['max_messages_per_hour'];
    }
    
    public function batch_delay($batch_number) {
        if ($batch_number > 1) {
            $delay = rand((int)$this->settings['batch_interval'] - 10, (int)$this->settings['batch_interval'] + 10);
            sleep(max(5, $delay));
        }
    }
}