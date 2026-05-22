<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class Whapi_library {
    private $CI;
    private $client;
    private $api_url = 'https://gate.whapi.cloud/';
    private $api_token;
    private $webhook_token;
    private $master_group_id;
    private $rate_limit;
    private $delay_min;
    private $delay_max;
    private $max_retries;
    private $cooldown;
    private $cache = [];
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->helper('whapi');
        $this->load_settings();
        
        // Initialiser Guzzle Client
        if (!empty($this->api_token)) {
            $this->client = new Client([
                'base_uri' => $this->api_url,
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_token,
                    'Content-Type' => 'application/json'
                ]
            ]);
        }
    }
    
    private function load_settings() {
        $settings = $this->CI->db->get('whatsapp_settings')->result_array();
        foreach ($settings as $setting) {
            $key = $setting['setting_key'];
            if ($key === 'api_token') {
                $token_data = json_decode($setting['setting_value'], true);
                $this->api_token = $token_data['token'] ?? null;
            } else {
                $this->$key = $setting['setting_value'];
            }
        }
    }
    
    public function get_setting($key) {
        $this->CI->db->where('setting_key', $key);
        $result = $this->CI->db->get('whatsapp_settings')->row();
        return $result ? $result->setting_value : null;
    }
    
    public function update_setting($key, $value) {
        $this->CI->db->where('setting_key', $key);
        return $this->CI->db->update('whatsapp_settings', ['setting_value' => $value]);
    }
    
    // ==============================================
    // API REQUESTS WITH GUZZLE
    // ==============================================
    
    private function api_request($endpoint, $method = 'GET', $data = null) {
        if (empty($this->api_token)) {
            throw new Exception("API token not configured");
        }
        
        if (!$this->client) {
            throw new Exception("Guzzle client not initialized");
        }
        
        try {
            $options = [];
            
            if ($method === 'POST' && $data) {
                $options['json'] = $data;
            } elseif ($method === 'DELETE') {
                // DELETE method
            }
            
            $response = $this->client->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();
            
            return json_decode($body, true);
            
        } catch (ConnectException $e) {
            // Erreur de connexion réseau
            throw new Exception("Network error: " . $e->getMessage());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody()->getContents();
                $error = json_decode($body, true);
                $errorMsg = $error['message'] ?? $error['error'] ?? $e->getMessage();
                throw new Exception($errorMsg);
            }
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception("API request failed: " . $e->getMessage());
        }
    }
    
    // ==============================================
    // SEND MESSAGES WITH GUZZLE
    // ==============================================
    
    public function send_text($to, $text) {
        $data = [
            'to' => format_phone($to),
            'body' => sanitize_message($text)
        ];
        
        try {
            $result = $this->api_request('messages/text', 'POST', $data);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function send_media($to, $type, $media_url, $caption = null) {
        $data = [
            'to' => format_phone($to),
            'media' => $media_url
        ];
        
        if ($caption) {
            $data['caption'] = sanitize_message($caption);
        }
        
        $endpoint = "messages/$type";
        
        try {
            $result = $this->api_request($endpoint, 'POST', $data);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function delete_message($message_id) {
        try {
            $result = $this->api_request("messages/$message_id", 'DELETE');
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function get_groups() {
        try {
            $result = $this->api_request('groups');
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function get_group_participants($group_id) {
        try {
            $result = $this->api_request("groups/$group_id/participants");
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ==============================================
    // QUEUE MANAGEMENT (inchangé)
    // ==============================================
    
    public function enqueue($data) {
        $queue_data = array(
            'target_type' => $data['target_type'] ?? 'both',
            'target_id' => $data['target_id'] ?? null,
            'phone_number' => isset($data['phone_number']) ? format_phone($data['phone_number']) : null,
            'message_type' => $data['message_type'],
            'message_data' => $data['message_data'],
            'media_url' => $data['media_url'] ?? null,
            'priority' => $data['priority'] ?? 1,
            'scheduled_at' => $data['scheduled_at'] ?? date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        );
        
        $this->CI->db->insert('whatsapp_queue', $queue_data);
        return $this->CI->db->insert_id();
    }
    
    public function dequeue() {
        if ($this->get_setting('queue_paused') == '1') {
            return null;
        }
        
        $this->CI->db->where('status', 'pending');
        $this->CI->db->where('scheduled_at <=', date('Y-m-d H:i:s'));
        $this->CI->db->order_by('priority DESC, created_at ASC');
        $this->CI->db->limit(1);
        $queue = $this->CI->db->get('whatsapp_queue')->row();
        
        if ($queue) {
            $this->CI->db->where('id', $queue->id);
            $this->CI->db->update('whatsapp_queue', ['status' => 'processing']);
            return $queue;
        }
        
        return null;
    }
    
    public function dequeue_batch($batch_size = 10) {
        if ($this->get_setting('queue_paused') == '1') {
            return [];
        }
        
        $this->CI->db->where('status', 'pending');
        $this->CI->db->where('scheduled_at <=', date('Y-m-d H:i:s'));
        $this->CI->db->order_by('priority DESC, created_at ASC');
        $this->CI->db->limit($batch_size);
        $queue_items = $this->CI->db->get('whatsapp_queue')->result();
        
        foreach ($queue_items as $item) {
            $this->CI->db->where('id', $item->id);
            $this->CI->db->update('whatsapp_queue', ['status' => 'processing']);
        }
        
        return $queue_items;
    }
    
    public function process_queue_item($queue_item) {
        $result = false;
        $error = null;
        
        try {
            if (!$this->check_rate_limit($queue_item->phone_number ?? $queue_item->target_id, 'send')) {
                throw new Exception("Rate limit exceeded for this recipient");
            }
            
            switch ($queue_item->message_type) {
                case 'text':
                    $result = $this->send_text($queue_item->phone_number, $queue_item->message_data);
                    break;
                case 'image':
                case 'video':
                case 'document':
                case 'audio':
                case 'sticker':
                    if ($queue_item->media_url) {
                        $this->validate_media($queue_item->media_url, $queue_item->message_type);
                    }
                    $result = $this->send_media($queue_item->phone_number, $queue_item->message_type, $queue_item->media_url, $queue_item->message_data);
                    break;
            }
            
            if ($result && isset($result['success']) && $result['success']) {
                $this->mark_queue_completed($queue_item->id);
                log_whatsapp(null, null, $queue_item->phone_number, $queue_item->message_data, $queue_item->message_type, 'sent');
                return true;
            } else {
                $error = $result['error'] ?? 'Unknown error';
                throw new Exception($error);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->mark_queue_failed($queue_item->id, $error);
            log_whatsapp(null, null, $queue_item->phone_number, $queue_item->message_data, $queue_item->message_type, 'failed', $error);
            return false;
        }
    }
    
    private function check_rate_limit($identifier, $action, $limit = 30, $window = 3600) {
        if (empty($identifier)) return true;
        
        $this->CI->db->where('sender', $identifier);
        $this->CI->db->where('action', $action);
        $this->CI->db->where('last_attempt >', date('Y-m-d H:i:s', time() - $window));
        $record = $this->CI->db->get('whatsapp_rate_limits')->row();
        
        if ($record && $record->attempts >= $limit) {
            return false;
        }
        
        if ($record) {
            $this->CI->db->where('id', $record->id);
            $this->CI->db->set('attempts', 'attempts+1', FALSE);
            $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
            $this->CI->db->update('whatsapp_rate_limits');
        } else {
            $this->CI->db->insert('whatsapp_rate_limits', [
                'sender' => $identifier,
                'action' => $action,
                'attempts' => 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }
    
    private function validate_media($media_url, $type) {
        $headers = @get_headers($media_url, 1);
        if (!$headers) {
            throw new Exception("Cannot access media URL");
        }
        
        $size = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
        
        $max_sizes = [
            'image' => 16 * 1024 * 1024,
            'video' => 64 * 1024 * 1024,
            'audio' => 16 * 1024 * 1024,
            'document' => 100 * 1024 * 1024,
            'sticker' => 512 * 1024
        ];
        
        $max_size = $max_sizes[$type] ?? 16 * 1024 * 1024;
        if ($size > $max_size) {
            throw new Exception("Media size exceeds WhatsApp limit (" . round($max_size / 1024 / 1024) . "MB)");
        }
        
        return true;
    }
    
    private function mark_queue_completed($id) {
        $this->CI->db->where('id', $id);
        $this->CI->db->update('whatsapp_queue', [
            'status' => 'completed',
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function mark_queue_failed($id, $error) {
        $this->CI->db->where('id', $id);
        $queue = $this->CI->db->get('whatsapp_queue')->row();
        
        if ($queue && $queue->retry_count < $this->max_retries) {
            $backoff = pow(2, $queue->retry_count);
            $this->CI->db->where('id', $id);
            $this->CI->db->update('whatsapp_queue', [
                'status' => 'retry',
                'retry_count' => $queue->retry_count + 1,
                'error_message' => $error,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime("+{$backoff} minutes"))
            ]);
        } else {
            $this->CI->db->where('id', $id);
            $this->CI->db->update('whatsapp_queue', [
                'status' => 'failed',
                'error_message' => $error,
                'processed_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // ==============================================
    // MESSAGE DISTRIBUTION (inchangé)
    // ==============================================
    
    public function distribute_message($message_data, $sender_phone) {
        $target_type = $message_data['target_type'] ?? 'both';
        $distributed_to = [];
        
        $is_master_group = ($message_data['group_id'] === $this->master_group_id);
        $is_admin = $this->is_group_admin($message_data['group_id'], $sender_phone);
        
        if (!$is_admin) {
            if ($message_data['has_media']) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_media', 'Non-admin tried to send media');
                $this->delete_message($message_data['message_id']);
                return ['error' => 'Non-admins cannot send media in any group'];
            }
            
            if (contains_link($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_link', 'Non-admin tried to send link');
                $this->delete_message($message_data['message_id']);
                return ['error' => 'Non-admins cannot send links in any group'];
            }
            
            if (contains_mention($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_mention', 'Non-admin tried to mention someone');
                $this->delete_message($message_data['message_id']);
                return ['error' => 'Non-admins cannot use mentions'];
            }
            
            if (contains_phone($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_phone', 'Non-admin tried to share phone number');
                $this->delete_message($message_data['message_id']);
                return ['error' => 'Non-admins cannot share phone numbers'];
            }
        }
        
        if (!$is_master_group) {
            return ['success' => true, 'type' => 'group_message', 'action' => 'allowed'];
        }
        
        $groups = $this->CI->db->get_where('groupes_whatsapp', ['actif' => 1])->result();
        
        if ($target_type == 'group' || $target_type == 'both') {
            foreach ($groups as $group) {
                $this->enqueue([
                    'target_type' => 'group',
                    'target_id' => $group->groupe_id,
                    'message_type' => $message_data['type'],
                    'message_data' => $message_data['text'],
                    'media_url' => $message_data['media_url'] ?? null,
                    'priority' => 1
                ]);
                $distributed_to[] = "group:{$group->groupe_id}";
                smart_delay();
            }
        }
        
        if ($target_type == 'inbox' || $target_type == 'both') {
            $inboxes = $this->CI->db->get_where('whatsapp_inbox', ['is_blacklisted' => 0])->result();
            shuffle($inboxes);
            
            foreach ($inboxes as $inbox) {
                $this->enqueue([
                    'target_type' => 'inbox',
                    'phone_number' => $inbox->phone_number,
                    'message_type' => $message_data['type'],
                    'message_data' => $message_data['text'],
                    'media_url' => $message_data['media_url'] ?? null,
                    'priority' => 1
                ]);
                $distributed_to[] = "inbox:{$inbox->phone_number}";
                smart_delay();
            }
        }
        
        return ['success' => true, 'distributed_to' => $distributed_to];
    }
    
    private function is_group_admin($group_id, $phone) {
        $this->CI->db->where('groupe_id', $group_id);
        $this->CI->db->where('phone_formatted', format_phone($phone));
        $this->CI->db->where('is_admin', 1);
        return $this->CI->db->get('whatsapp_participants')->num_rows() > 0;
    }
    
    private function log_security_event($group_id, $sender, $action_type, $reason) {
        $this->CI->db->insert('whatsapp_security_logs', [
            'group_id' => $group_id,
            'sender' => format_phone($sender),
            'action_type' => $action_type,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // ==============================================
    // SYNC METHODS (inchangé)
    // ==============================================
    
    public function sync_all_groups() {
        $result = $this->get_groups();
        
        if (!$result['success']) {
            log_message('error', 'Failed to sync groups: ' . ($result['error'] ?? 'Unknown error'));
            return false;
        }
        
        $groups = $result['data'];
        $synced_count = 0;
        
        foreach ($groups as $group) {
            $exists = $this->CI->db->get_where('groupes_whatsapp', ['groupe_id' => $group['id']])->row();
            
            $group_data = [
                'groupe_id' => $group['id'],
                'nom' => $group['name'],
                'actif' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($exists) {
                $this->CI->db->where('groupe_id', $group['id']);
                $this->CI->db->update('groupes_whatsapp', $group_data);
            } else {
                $group_data['created_at'] = date('Y-m-d H:i:s');
                $this->CI->db->insert('groupes_whatsapp', $group_data);
            }
            
            $synced_count++;
            $this->sync_group_participants($group['id']);
            usleep(500000);
        }
        
        $this->update_setting('last_sync', date('Y-m-d H:i:s'));
        return $synced_count;
    }
    
    public function sync_group_participants($group_id) {
        $result = $this->get_group_participants($group_id);
        
        if (!$result['success']) {
            return false;
        }
        
        $participants = $result['data'];
        $synced_count = 0;
        
        foreach ($participants as $participant) {
            $participant_data = [
                'groupe_id' => $group_id,
                'phone' => $participant['phone'],
                'phone_formatted' => format_phone($participant['phone']),
                'is_admin' => in_array('admin', $participant['roles'] ?? []) ? 1 : 0,
                'is_creator' => in_array('creator', $participant['roles'] ?? []) ? 1 : 0,
                'profile_name' => $participant['name'] ?? null,
                'synced_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $exists = $this->CI->db->get_where('whatsapp_participants', [
                'groupe_id' => $group_id,
                'phone_formatted' => $participant_data['phone_formatted']
            ])->row();
            
            if ($exists) {
                $this->CI->db->where('id', $exists->id);
                $this->CI->db->update('whatsapp_participants', $participant_data);
            } else {
                $participant_data['created_at'] = date('Y-m-d H:i:s');
                $this->CI->db->insert('whatsapp_participants', $participant_data);
            }
            
            $synced_count++;
            
            $blacklisted = $this->CI->db->get_where('whatsapp_blacklist', ['phone_number' => $participant_data['phone_formatted']])->row();
            $inbox_exists = $this->CI->db->get_where('whatsapp_inbox', ['phone_number' => $participant_data['phone_formatted']])->row();
            
            if (!$inbox_exists && !$blacklisted) {
                $this->CI->db->insert('whatsapp_inbox', [
                    'phone_number' => $participant_data['phone_formatted'],
                    'full_name' => $participant_data['profile_name'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        return $synced_count;
    }
    
    public function cleanup_deleted_groups() {
        $local_groups = $this->CI->db->get('groupes_whatsapp')->result();
        $remote_result = $this->get_groups();
        
        if (!$remote_result['success']) {
            return false;
        }
        
        $remote_group_ids = array_column($remote_result['data'], 'id');
        
        $deleted_count = 0;
        foreach ($local_groups as $local_group) {
            if (!in_array($local_group->groupe_id, $remote_group_ids)) {
                $this->CI->db->where('groupe_id', $local_group->groupe_id);
                $this->CI->db->delete('groupes_whatsapp');
                
                $this->CI->db->where('groupe_id', $local_group->groupe_id);
                $this->CI->db->delete('whatsapp_participants');
                
                $deleted_count++;
            }
        }
        
        return $deleted_count;
    }
    
    // Anti-Ban System
    public function send_with_antiban($messages) {
        $sent_count = 0;
        $batch_count = 0;
        
        shuffle($messages);
        
        foreach ($messages as $message) {
            smart_delay();
            
            $result = $this->send_message($message);
            
            if ($result) {
                $sent_count++;
                $batch_count++;
            }
            
            if ($batch_count >= 25) {
                $break = rand(30, 60);
                sleep($break);
                $batch_count = 0;
            }
            
            if ($sent_count >= $this->rate_limit) {
                $cooldown = rand($this->cooldown, $this->cooldown * 2);
                sleep($cooldown);
                $sent_count = 0;
            }
        }
        
        return true;
    }
    
    private function send_message($message_data) {
        try {
            if ($message_data['type'] == 'text') {
                return $this->send_text($message_data['to'], $message_data['content']);
            } else {
                return $this->send_media($message_data['to'], $message_data['type'], $message_data['media_url'], $message_data['caption']);
            }
        } catch (Exception $e) {
            log_message('error', 'Send message failed: ' . $e->getMessage());
            return false;
        }
    }
}