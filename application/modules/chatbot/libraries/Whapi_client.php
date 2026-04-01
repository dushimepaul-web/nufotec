<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whapi_client {
    
    private $token;
    private $api_url;
    private $client;
    
    public function __construct() {
        $CI =& get_instance();
        $CI->config->load('chatbot/config', TRUE);
        $this->token = $CI->config->item('whapi_token', 'chatbot/config');
        $this->api_url = $CI->config->item('whapi_url', 'chatbot/config');
        
        $this->client = new GuzzleHttp\Client([
            'base_uri' => $this->api_url,
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ]
        ]);
    }
    
    /**
     * Envoie un message texte
     */
    public function sendText($to, $message) {
        return $this->request('POST', 'messages/text', [
            'to' => $to,
            'body' => $message
        ]);
    }
    
    /**
     * Envoie une image
     */
    public function sendImage($to, $image_url, $caption = '') {
        return $this->request('POST', 'messages/image', [
            'to' => $to,
            'media' => $image_url,
            'caption' => $caption
        ]);
    }
    
    /**
     * Envoie un document
     */
    public function sendDocument($to, $file_url, $caption = '') {
        return $this->request('POST', 'messages/document', [
            'to' => $to,
            'media' => $file_url,
            'caption' => $caption
        ]);
    }
    
    /**
     * Envoie une vidéo
     */
    public function sendVideo($to, $video_url, $caption = '') {
        return $this->request('POST', 'messages/video', [
            'to' => $to,
            'media' => $video_url,
            'caption' => $caption
        ]);
    }
    
    /**
     * Crée un groupe
     */
    public function createGroup($name, $participants = []) {
        return $this->request('POST', 'groups', [
            'subject' => $name,
            'participants' => $participants
        ]);
    }
    
    /**
     * Récupère les groupes
     */
    public function getGroups($limit = 10) {
        return $this->request('GET', 'groups', ['count' => $limit]);
    }
    
    /**
     * Vérifie le statut
     */
    public function getStatus() {
        return $this->request('GET', 'status');
    }
    
    /**
     * Requête API
     */
    private function request($method, $endpoint, $data = []) {
        try {
            $options = [];
            if ($method === 'POST' && !empty($data)) {
                $options['json'] = $data;
            } elseif ($method === 'GET' && !empty($data)) {
                $options['query'] = $data;
            }
            
            $response = $this->client->request($method, $endpoint, $options);
            return json_decode($response->getBody(), true);
            
        } catch (Exception $e) {
            log_message('error', 'Whapi Error: ' . $e->getMessage());
            return false;
        }
    }
}