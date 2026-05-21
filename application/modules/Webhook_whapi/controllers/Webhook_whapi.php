<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // CHARGER LA CONFIGURATION D'ABORD
        $this->config->load('whapi');
        
        $this->load->library('WhatsApp_Whapi');
        $this->load->model(['Group_model', 'Participant_model', 'Queue_model', 'Inbox_model']);
        $this->load->helper('whatsapp');
    }

    public function index() {
        // Vérification du token secret (GET, HEADER, ou POST)
        $received_token = $this->input->get('token') ?? 
                         $this->input->get_request_header('X-Whapi-Token') ?? 
                         $this->input->post('token');
        
        $whapi_config = $this->config->item('whapi');
        $expected_token = $whapi_config['webhook_secret'] ?? 'nufotecburundi2026';
        
        // POUR LE TEST (enlever en production)
        if ($this->input->get('test') == '1') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'test_mode',
                    'token_received' => $received_token,
                    'token_expected' => $expected_token,
                    'token_match' => ($received_token === $expected_token),
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
            return;
        }
        
        // Vérification normale du token
        if ($received_token !== $expected_token) {
            log_message('error', 'Webhook appelé avec token invalide. Reçu: ' . $received_token . ', Attendu: ' . $expected_token);
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => 'unauthorized', 'error' => 'Invalid token']));
            return;
        }

        // Traitement des données POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'no_data']));
                return;
            }

            log_message('info', 'Webhook reçu: ' . json_encode($input));

            if (isset($input['messages'])) {
                foreach ($input['messages'] as $message) {
                    $this->process_message($message);
                }
            }
            
            if (isset($input['statuses'])) {
                $this->process_statuses($input['statuses']);
            }
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'ok', 'message' => 'Webhook fonctionne']));
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
            case 'voice':
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
        if ($blocked_patterns) {
            foreach ($blocked_patterns as $pattern) {
                if (preg_match($pattern, $text)) return false;
            }
        }
        return true;
    }

    private function process_message($message) {
        $sender = $message['from'] ?? null;
        $sender_name = $message['pushName'] ?? $message['author'] ?? 'Unknown';
        $message_type = $message['type'] ?? 'unknown';
        $chat_id = $message['chatId'] ?? $sender;
        $is_group = strpos($chat_id, '@g.us') !== false;

        if (!$sender) return;

        $media_data = $this->extract_media_content($message, $message_type);

        // Téléchargement local du média si nécessaire
        if (!empty($media_data['media_url']) && in_array($message_type, ['image','video','audio','voice','document','sticker'])) {
            $local_path = $this->download_media_locally($media_data['media_url'], $message_type);
            if ($local_path) {
                $media_data['local_media_path'] = $local_path;
                $media_data['media_url'] = base_url($local_path);
            }
        }

        if ($is_group && $chat_id) {
            $this->Group_model->upsert_group($chat_id, $message['chatName'] ?? 'Groupe');
            $this->Participant_model->upsert_participant($chat_id, $sender, $sender_name);
        }

        if ($this->Participant_model->is_blocked($sender)) return;

        $admin_numbers = $this->config->item('admin_numbers');
        $is_admin = in_array($sender, $admin_numbers);

        $is_valid = $this->validate_message($message_type, $media_data, $is_admin);
        
        if (!$is_valid) {
            $this->whatsapp_whapi->react_to_message($message['id'], '🚫');
            $this->Participant_model->log_violation($sender, $message_type, $media_data['message'] ?? '', $chat_id);
            if ($this->Participant_model->increment_violation($sender)) {
                log_message('info', "Utilisateur bloqué: $sender");
            }
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

    private function download_media_locally($url, $type) {
        $whapi_config = $this->config->item('whapi');
        $storage_path = isset($whapi_config['media_storage_path']) ? $whapi_config['media_storage_path'] : FCPATH . 'uploads/whatsapp_media/';
        
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0755, true);
        }
        
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . ($ext ?: 'bin');
        $local_file = $storage_path . $filename;
        
        if ($this->whatsapp_whapi->download_media($url, $local_file)) {
            return 'uploads/whatsapp_media/' . $filename;
        }
        return null;
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
        
        log_message('info', "Broadcast $target_type ajouté, ID: $queue_id");
    }

    private function process_statuses($statuses) {
        foreach ($statuses as $status) {
            if (isset($status['id']) && isset($status['status'])) {
                log_message('debug', 'Statut message: ' . $status['id'] . ' = ' . $status['status']);
            }
        }
    }
}