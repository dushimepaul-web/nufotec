<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('WhatsApp_Whapi');
        $this->load->model(['Group_model', 'Participant_model', 'Queue_model', 'Inbox_model']);
        $this->load->helper('whatsapp');
    }
    
    /**
     * Point d'entrée du webhook
     */
    public function index() {
        // Vérification du token secret (en GET ou en HEADER)
        $expected_token = 'nufotecburundi2026'; // Directement ici pour éviter les problèmes de config
        
        // Récupérer le token depuis les différents endroits possibles
        $received_token = null;
        
        // 1. Depuis l'en-tête HTTP X-Whapi-Token
        $headers = getallheaders();
        if (isset($headers['X-Whapi-Token'])) {
            $received_token = $headers['X-Whapi-Token'];
        }
        
        // 2. Depuis l'en-tête Authorization
        if (!$received_token && isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
                $received_token = $matches[1];
            }
        }
        
        // 3. Depuis le paramètre GET 'token'
        if (!$received_token && $this->input->get('token')) {
            $received_token = $this->input->get('token');
        }
        
        // 4. Depuis le paramètre POST 'token'
        if (!$received_token && $this->input->post('token')) {
            $received_token = $this->input->post('token');
        }
        
        // 5. Depuis le corps JSON
        if (!$received_token) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['token'])) {
                $received_token = $input['token'];
            }
        }
        
        // Vérifier le token
        if (!$expected_token || $received_token !== $expected_token) {
            log_message('error', 'Webhook appelé avec token invalide. Reçu: ' . ($received_token ?: 'aucun') . ', Attendu: ' . $expected_token);
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => 'unauthorized', 'error' => 'Invalid token']));
            return;
        }
        
        // Lire le corps de la requête
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Si pas de données JSON, essayer de récupérer depuis POST
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }
        
        if (!$input) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'no_data', 'message' => 'No data received']));
            return;
        }
        
        log_message('info', 'Webhook Whapi reçu');
        
        // Traiter les messages
        if (isset($input['messages']) && is_array($input['messages'])) {
            foreach ($input['messages'] as $message) {
                $this->process_message($message);
            }
        }
        
        // Traiter les statuts
        if (isset($input['statuses'])) {
            $this->process_statuses($input['statuses']);
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'ok']));
    }
    
    /**
     * Traiter un message entrant
     */
    private function process_message($message) {
        $sender = $message['from'] ?? null;
        $sender_name = $message['pushName'] ?? $message['author'] ?? 'Unknown';
        $message_type = $message['type'] ?? 'unknown';
        $chat_id = $message['chatId'] ?? $sender;
        $is_group = strpos($chat_id, '@g.us') !== false;
        $message_id = $message['id'] ?? null;
        
        if (!$sender) return;
        
        log_message('info', "Message reçu de: {$sender}, type: {$message_type}, groupe: " . ($is_group ? $chat_id : 'non'));
        
        // Extraire le contenu du média
        $media_data = $this->extract_media_content($message, $message_type);
        
        // Télécharger le média localement si nécessaire
        if (!empty($media_data['media_url']) && in_array($message_type, ['image','video','audio','document','sticker'])) {
            $local_path = $this->download_media_locally($media_data['media_url'], $message_type);
            if ($local_path) {
                $media_data['local_media_path'] = $local_path;
                $media_data['media_url'] = base_url($local_path);
            }
        }
        
        // SYNCHRONISATION AUTOMATIQUE : Si c'est un groupe, l'ajouter à la BDD
        if ($is_group && $chat_id) {
            $this->Group_model->upsert_group($chat_id, $message['chatName'] ?? 'Groupe WhatsApp');
            
            // Ajouter le participant
            $this->Participant_model->upsert_participant($chat_id, $sender, $sender_name);
        }
        
        // Vérifier si l'utilisateur est bloqué
        if ($this->Participant_model->is_blocked($sender)) {
            log_message('info', 'Message ignoré - utilisateur bloqué: ' . $sender);
            return;
        }
        
        // Vérifier si c'est un admin
        $admin_numbers = ['25779666439', '25768863945']; // Directement ici
        $is_admin = in_array($sender, $admin_numbers);
        
        log_message('info', "Admin check: {$sender} -> " . ($is_admin ? 'ADMIN' : 'MEMBRE'));
        
        // VALIDATION DU MESSAGE
        $is_valid = $this->validate_message($message_type, $media_data, $is_admin);
        
        if (!$is_valid) {
            // Membre a envoyé un média non autorisé → RÉACTION 🚫 + Violation
            $this->WhatsApp_Whapi->react_to_message($message_id, '🚫');
            $this->handle_violation($sender, $message_type, $media_data, $chat_id);
            log_message('info', "VIOLATION de {$sender}: type={$message_type}");
            return;
        }
        
        // Si c'est un admin → DIFFUSION AUTOMATIQUE
        if ($is_admin && $is_group) {
            $this->handle_admin_message($media_data, $sender, $sender_name, $message_type);
        } elseif ($is_admin && !$is_group) {
            // Admin en message privé - on répond normalement
            log_message('info', "Message privé d'admin reçu");
        }
        // Sinon c'est un membre avec texte valide → reste dans le groupe (pas de diffusion)
    }
    
    /**
     * Gérer le message d'un admin (diffusion automatique)
     */
    private function handle_admin_message($media_data, $sender_number, $sender_name, $message_type) {
        // Détecter la cible avec les mots-clés
        $target_type = 'both'; // Par défaut: groupes + inbox
        $message_text = trim($media_data['message'] ?? $media_data['caption'] ?? '');
        
        // Vérifier les mots-clés au début du message
        if (preg_match('/^#groupe\s+/i', $message_text)) {
            $target_type = 'groups';
            // Retirer le mot-clé du message
            $clean_message = preg_replace('/^#groupe\s+/i', '', $message_text);
            $media_data['message'] = $clean_message;
            $media_data['caption'] = $clean_message;
        } elseif (preg_match('/^#inbox\s+/i', $message_text)) {
            $target_type = 'inbox';
            // Retirer le mot-clé du message
            $clean_message = preg_replace('/^#inbox\s+/i', '', $message_text);
            $media_data['message'] = $clean_message;
            $media_data['caption'] = $clean_message;
        }
        
        // Diffuser à tous les groupes actifs et/ou inbox
        $this->broadcast_to_all($media_data, $sender_number, $sender_name, $target_type, $message_type);
        
        log_message('info', "BROADCAST ADMIN depuis {$sender_number} vers {$target_type}");
    }
    
    /**
     * Télécharger le média localement
     */
    private function download_media_locally($url, $type) {
        $storage_path = FCPATH . 'uploads/whatsapp_media/';
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0755, true);
        }
        
        $extensions = [
            'image' => 'jpg',
            'video' => 'mp4', 
            'audio' => 'mp3',
            'document' => 'pdf',
            'sticker' => 'webp'
        ];
        $ext = $extensions[$type] ?? 'bin';
        $filename = uniqid('wa_') . '.' . $ext;
        $local_file = $storage_path . $filename;
        
        if ($this->WhatsApp_Whapi->download_media($url, $local_file)) {
            return 'uploads/whatsapp_media/' . $filename;
        }
        return null;
    }
    
    /**
     * Extraire le contenu du média du message
     */
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
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['url'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                break;
            case 'video':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['url'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                break;
            case 'audio':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['url'] ?? null;
                break;
            case 'document':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['url'] ?? null;
                $media_data['caption'] = $message['caption'] ?? null;
                $media_data['filename'] = $message['filename'] ?? 'document';
                break;
            case 'sticker':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['url'] ?? null;
                break;
        }
        
        return $media_data;
    }
    
    /**
     * Valider si le message est autorisé
     */
    private function validate_message($type, $media_data, $is_admin) {
        // Admin peut tout envoyer
        if ($is_admin) return true;
        
        // Membre: SEULEMENT du texte sans liens
        if ($type !== 'text') return false;
        
        $text = $media_data['message'] ?? '';
        $blocked_patterns = [
            '/https?:\/\//i',
            '/www\.[a-z0-9\.\-]+/i',
            '/\.(com|org|net|fr|cm|info|biz|io|ai)/i',
            '/wa\.me\//i',
            '/chat\.whatsapp\.com\//i'
        ];
        
        foreach ($blocked_patterns as $pattern) {
            if (preg_match($pattern, $text)) return false;
        }
        
        return true;
    }
    
    /**
     * Gérer une violation
     */
    private function handle_violation($phone, $type, $media_data, $groupe_id) {
        $violation_msg = ($type == 'text') ? ($media_data['message'] ?? '') : 'Média non autorisé: ' . $type;
        
        $this->Participant_model->log_violation($phone, $type, $violation_msg, $groupe_id);
        
        $blocked = $this->Participant_model->increment_violation($phone);
        if ($blocked) {
            log_message('info', 'UTILISATEUR BLOQUÉ après 3 violations: ' . $phone);
        }
    }
    
    /**
     * Diffuser le message à tous les groupes et/ou inbox
     */
    private function broadcast_to_all($media_data, $sender_number, $sender_name, $target_type, $message_type) {
        $groups = [];
        $participants = [];
        
        if ($target_type === 'groups' || $target_type === 'both') {
            $groups = $this->Group_model->get_active_groups();
        }
        
        if ($target_type === 'inbox' || $target_type === 'both') {
            $participants = $this->Participant_model->get_all_unique_participants();
        }
        
        // Ajouter à la queue
        $queue_data = [
            'message' => $media_data['message'] ?? $media_data['caption'] ?? null,
            'sender_number' => $sender_number,
            'sender_name' => $sender_name,
            'is_admin' => 1,
            'target_type' => $target_type,
            'total_recipients' => count($groups) + count($participants),
            'media_type' => $message_type,
            'media_url' => $media_data['media_url'] ?? null,
            'local_media_path' => $media_data['local_media_path'] ?? null,
            'media_caption' => $media_data['caption'] ?? null,
            'media_filename' => $media_data['filename'] ?? null
        ];
        
        $queue_id = $this->Queue_model->add_to_queue($queue_data);
        
        // Ajouter les messages inbox dans la table messages_inbox
        if (!empty($participants)) {
            $this->Inbox_model->add_to_inbox($queue_id, $participants, $media_data);
        }
        
        log_message('info', sprintf(
            'BROADCAST ajouté: Queue=%d, Groupes=%d, Inbox=%d, Cible=%s',
            $queue_id,
            count($groups),
            count($participants),
            $target_type
        ));
    }
    
    /**
     * Traiter les statuts de livraison
     */
    private function process_statuses($statuses) {
        foreach ($statuses as $status) {
            if (isset($status['id']) && isset($status['status'])) {
                $this->db->where('message_id', $status['id']);
                $this->db->update('messages_inbox', ['status' => $status['status']]);
            }
        }
    }
}