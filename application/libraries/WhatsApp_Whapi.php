<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WhatsApp_Whapi {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    private $retry_attempts;
    private $retry_delay;
    private $rate_limit_delay;
    private $CI;
    private $last_response = null;
    private $last_error = null;
    
    public function __construct() {
        $this->CI =& get_instance();
        $whapi_config = $this->CI->config->item('whapi');
        
        $this->api_key = $whapi_config['api_key'];
        $this->base_url = rtrim($whapi_config['base_url'], '/');
        $this->timeout = $whapi_config['timeout'] ?? 60;
        $this->debug = $whapi_config['debug'] ?? false;
        $this->retry_attempts = $whapi_config['retry_attempts'] ?? 3;
        $this->retry_delay = $whapi_config['retry_delay'] ?? 2000;
        $this->rate_limit_delay = $whapi_config['rate_limit_delay'] ?? 1000;
    }
    
    public function send_text($to, $message, $options = []) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        if (!empty($options['reply_to'])) {
            $payload['context'] = ['message_id' => $options['reply_to']];
        }
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_image($to, $image_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'image',
            'image' => ['link' => $image_url]
        ];
        if ($caption) $payload['image']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_video($to, $video_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'video',
            'video' => ['link' => $video_url]
        ];
        if ($caption) $payload['video']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_audio($to, $audio_url) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'audio',
            'audio' => ['link' => $audio_url]
        ];
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_document($to, $doc_url, $filename, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'document',
            'document' => [
                'link' => $doc_url,
                'filename' => $filename
            ]
        ];
        if ($caption) $payload['document']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_sticker($to, $sticker_url) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'sticker',
            'sticker' => ['link' => $sticker_url]
        ];
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function send_location($to, $latitude, $longitude, $name = null, $address = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'location',
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ]
        ];
        if ($name) $payload['location']['name'] = $name;
        if ($address) $payload['location']['address'] = $address;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    public function react_to_message($message_id, $emoji) {
        return $this->request('POST', '/messages/' . $message_id . '/react', ['emoji' => $emoji]);
    }
    
    public function get_groups() {
        $response = $this->request('GET', '/groups');
        return $response['success'] ? ($response['data']['groups'] ?? []) : [];
    }
    
    public function get_group_participants($group_id) {
        $response = $this->request('GET', '/groups/' . $group_id . '/participants');
        return $response['success'] ? ($response['data']['participants'] ?? []) : [];
    }
    
    public function download_media($media_url, $destination_path) {
        $ch = curl_init($media_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $data) {
            file_put_contents($destination_path, $data);
            return true;
        }
        return false;
    }
    
    private function format_number($number) {
        $number = preg_replace('/[^0-9+]/', '', $number);
        if (substr($number, 0, 2) === '00') {
            $number = '+' . substr($number, 2);
        }
        if (substr($number, 0, 1) !== '+') {
            $number = '+237' . ltrim($number, '0');
        }
        return $number;
    }
    
    private function request($method, $endpoint, $data = null) {
        $attempt = 0;
        
        while ($attempt < $this->retry_attempts) {
            $attempt++;
            
            $ch = curl_init($this->base_url . $endpoint);
            $headers = [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json',
                'Accept: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->last_response = $response;
            
            if ($this->debug) {
                log_message('debug', "Whapi API [$method $endpoint] - HTTP $http_code");
            }
            
            // Succès
            if ($http_code >= 200 && $http_code < 300) {
                return [
                    'success' => true,
                    'data' => json_decode($response, true),
                    'http_code' => $http_code
                ];
            }
            
            // Erreurs critiques : ne pas réessayer
            if (in_array($http_code, [401, 403, 404])) {
                $this->last_error = $error ?: $response;
                return [
                    'success' => false,
                    'error' => "HTTP $http_code: " . ($error ?: $response),
                    'http_code' => $http_code
                ];
            }
            
            // Rate limiting
            if ($http_code === 429) {
                $retry_after = $this->rate_limit_delay * $attempt;
                if (preg_match('/Retry-After:\s*(\d+)/i', $response, $matches)) {
                    $retry_after = (int)$matches[1];
                }
                sleep(min($retry_after, 60));
                continue;
            }
            
            // Erreurs 5xx : réessai avec backoff exponentiel
            if ($http_code >= 500 && $http_code < 600) {
                $wait = min(pow(2, $attempt) * 1000, 30000);
                usleep($wait * 1000);
                continue;
            }
            
            // Autres erreurs
            if ($attempt < $this->retry_attempts) {
                usleep($this->retry_delay * 1000 * $attempt);
                continue;
            }
            
            $this->last_error = $error ?: $response;
            return [
                'success' => false,
                'error' => $error ?: $response,
                'http_code' => $http_code
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Échec après ' . $this->retry_attempts . ' tentatives'
        ];
    }
    
    public function get_last_response() {
        return $this->last_response;
    }
    
    public function get_last_error() {
        return $this->last_error;
    }
}