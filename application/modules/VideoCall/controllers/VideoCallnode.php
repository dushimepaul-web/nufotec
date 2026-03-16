<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Videocallnode extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }
    
    public function index() {
        // Génère un ID de room aléatoire si pas fourni
        $room_id = $this->input->get('room') ?: $this->generate_room_id();
        
        $data = [
            'room_id' => $room_id,
            'user_id' => rand(1000, 9999),  // Temporaire, remplace par session
            'username' => 'User_' . rand(100, 999),
            'ws_url' => 'http://localhost:3001'  // Ton serveur Node.js
        ];
        
        $this->load->view('video_call_peerjs', $data);
    }
    

     private function generate_room_id() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}