<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Charger les modèles
        $this->load->model('Group_model');
        $this->load->model('Participant_model');
        $this->load->model('Queue_model');
        $this->load->model('Inbox_model');
        $this->load->helper('whatsapp');
        
        // Charger la configuration
        if ($this->config->item('whapi') === null) {
            $this->config->load('whapi', TRUE);
        }
        
        // Charger les libraries
        $this->load->library('WhatsApp_Whapi');
        $this->load->library('AntiBan');
    }
    
    public function index() {
        // ============================================
        // RÉPONSE POUR LA MÉTHODE GET (vérification Whapi)
        // ============================================
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ok',
                'message' => 'Webhook is active and ready',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return;
        }
        
        // ============================================
        // TRAITEMENT POUR LA MÉTHODE POST (messages réels)
        // ============================================
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Si pas de données, répondre no_data
        if (!$input) {
            echo json_encode(['status' => 'no_data']);
            return;
        }
        
        // Log pour debug
        log_message('info', 'Webhook Whapi reçu: ' . json_encode($input));
        
        // Traiter les messages
        if (isset($input['messages']) && is_array($input['messages'])) {
            foreach ($input['messages'] as $message) {
                $this->process_message($message);
            }
        }
        
        // Traiter les status
        if (isset($input['statuses'])) {
            $this->process_statuses($input['statuses']);
        }
        
        // Toujours répondre ok à Whapi
        echo json_encode(['status' => 'ok']);
    }
    
    private function process_message($message) {
        $sender = $message['from'] ?? null;
        $sender_name = $message['pushName'] ?? $message['author'] ?? 'Unknown';
        $message_type = $message['type'] ?? 'unknown';
        $chat_id = $message['chatId'] ?? $sender;
        $is_group = strpos($chat_id, '@g.us') !== false;
        
        if (!$sender) return;
        
        $media_data = $this->extract_media_content($message, $message_type);
        
        if ($is_group && $chat_id) {
            $this->Group_model->upsert_group($chat_id, $message['chatName'] ?? 'Groupe WhatsApp');
            $this->Participant_model->upsert_participant($chat_id, $sender, $sender_name);
        }
        
        if ($this->Participant_model->is_blocked($sender)) {
            log_message('info', 'Message ignoré - utilisateur bloqué: ' . $sender);
            return;
        }
        
        $admin_numbers = $this->config->item('admin_numbers');
        $is_admin = in_array($sender, $admin_numbers);
        
        $is_valid = $this->validate_message($message_type, $media_data, $is_admin);
        
        if (!$is_valid) {
            try {
                $this->whatsapp_whapi->react_to_message($message['id'], '🚫');
            } catch (Exception $e) {
                log_message('error', 'Erreur reaction: ' . $e->getMessage());
            }
            $this->handle_violation($sender, $message_type, $media_data, $chat_id);
            log_message('info', "Violation de {$sender}: type={$message_type}");
            return;
        }
        
        if ($is_admin) {
            $target_type = 'both';
            $message_text = trim($media_data['message'] ?? $media_data['caption'] ?? '');
            
            if ($message_text === '#groupes') {
                $target_type = 'groups';
                $media_data['message'] = '';
            } elseif ($message_text === '#inbox') {
                $target_type = 'inbox';
                $media_data['message'] = '';
            }
            
            $this->broadcast_to_all($media_data, $sender, $sender_name, $target_type);
            log_message('info', "Broadcast admin depuis {$sender} vers {$target_type}");
        }
    }
    
    private function extract_media_content($message, $type) {
        $media_data = [
            'media_type' => $type,
            'message' => null,
            'media_url' => null,
            'caption' => null,
            'filename' => null,
            'local_media_path' => null
        ];
        
        switch($type) {
            case 'text':
                $media_data['message'] = $message['body'] ?? '';
                break;
            case 'image':
                $media_data['media_url'] = $message['mediaUrl'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                break;
            case 'video':
                $media_data['media_url'] = $message['mediaUrl'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                break;
            case 'audio':
                $media_data['media_url'] = $message['mediaUrl'] ?? null;
                break;
            case 'document':
                $media_data['media_url'] = $message['mediaUrl'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                $media_data['filename'] = $message['filename'] ?? 'document';
                break;
            case 'sticker':
                $media_data['media_url'] = $message['mediaUrl'] ?? null;
                break;
        }
        
        return $media_data;
    }
    
    private function validate_message($type, $media_data, $is_admin) {
        if ($is_admin) return true;
        if ($type !== 'text') return false;
        
        $text = $media_data['message'] ?? '';
        $blocked_patterns = $this->config->item('blocked_patterns');
        if (is_array($blocked_patterns)) {
            foreach ($blocked_patterns as $pattern) {
                if (preg_match($pattern, $text)) return false;
            }
        }
        return true;
    }
    
    private function handle_violation($phone, $type, $media_data, $groupe_id) {
        $violation_msg = $type == 'text' ? ($media_data['message'] ?? '') : 'Média: ' . $type;
        $this->Participant_model->log_violation($phone, $type, $violation_msg, $groupe_id);
        
        if ($this->Participant_model->increment_violation($phone)) {
            log_message('info', 'Utilisateur bloqué après 3 violations: ' . $phone);
        }
    }
    
    private function broadcast_to_all($media_data, $sender_number, $sender_name, $target_type) {
        $groups = ($target_type === 'inbox') ? [] : $this->Group_model->get_active_groups();
        $participants = ($target_type === 'groups') ? [] : $this->Participant_model->get_all_unique_participants();
        
        $queue_data = [
            'message' => $media_data['message'] ?? $media_data['caption'] ?? null,
            'sender_number' => $sender_number,
            'sender_name' => $sender_name,
            'is_admin' => 1,
            'target_type' => $target_type,
            'total_recipients' => count($groups) + count($participants),
            'media_type' => $media_data['media_type'],
            'media_url' => $media_data['media_url'] ?? null,
            'local_media_path' => $media_data['local_media_path'] ?? null,
            'media_caption' => $media_data['caption'] ?? null,
            'media_filename' => $media_data['filename'] ?? null
        ];
        
        $queue_id = $this->Queue_model->add_to_queue($queue_data);
        
        if (!empty($participants)) {
            $this->Inbox_model->add_to_inbox($queue_id, $participants, $media_data);
        }
        
        log_message('info', sprintf(
            'Broadcast %s ajouté: Queue=%d, Groupes=%d, Inbox=%d',
            $target_type,
            $queue_id,
            count($groups),
            count($participants)
        ));
    }
    
    private function process_statuses($statuses) {
        foreach ($statuses as $status) {
            if (isset($status['id']) && isset($status['status'])) {
                log_message('debug', 'Status update: ' . json_encode($status));
            }
        }
    }
}