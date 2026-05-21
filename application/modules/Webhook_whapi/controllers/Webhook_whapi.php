<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WEBHOOK WHAPI - VERSION CORRIGÉE
 * 
 * RÈGLES:
 * 1. ADMIN: Peut envoyer TOUS types de médias → Diffusion selon commande (#groupes, #inbox, ou both par défaut)
 * 2. MEMBRE: Seulement TEXT (pas de médias, pas de liens, pas de localisation, pas de contacts)
 * 3. Si membre envoie média/lien/localisation/contact → 🚫 Violation + compteur + blocage après 3
 * 4. Le message original de l'admin reste dans le groupe source (normal) mais est diffusé ailleurs
 */

class Webhook_whapi extends MY_Controller {
    
    private $whatsapp_whapi;
    
    public function __construct() {
        parent::__construct();
        $this->load->library('WhatsApp_Whapi');
        $this->load->model(['Group_model', 'Participant_model', 'Queue_model', 'Inbox_model']);
        $this->load->helper('whatsapp');
        $this->whatsapp_whapi = new WhatsApp_Whapi();
    }
    
    public function index() {
        // Vérification du token secret
        $headers = getallheaders();
        $received_token = $headers['X-Whapi-Token'] ?? $this->input->get_request_header('X-Whapi-Token');
        $expected_token = $this->config->item('whapi')['webhook_secret'] ?? '';
        
        if (!$expected_token || $received_token !== $expected_token) {
            log_message('error', 'Webhook appelé avec token invalide');
            $this->output->set_status_header(401);
            echo json_encode(['status' => 'unauthorized']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['status' => 'no_data']);
            return;
        }
        
        log_message('info', 'Webhook Whapi reçu: ' . json_encode($input));
        
        if (isset($input['messages']) && is_array($input['messages'])) {
            foreach ($input['messages'] as $message) {
                $this->process_message($message);
            }
        }
        
        if (isset($input['statuses'])) {
            $this->process_statuses($input['statuses']);
        }
        
        echo json_encode(['status' => 'ok']);
    }
    
    /**
     * TRAITEMENT PRINCIPAL D'UN MESSAGE
     */
    private function process_message($message) {
        $sender = $message['from'] ?? null;
        $sender_name = $message['pushName'] ?? $message['author'] ?? 'Unknown';
        $message_type = $message['type'] ?? 'unknown';
        $chat_id = $message['chatId'] ?? $sender;
        $message_id = $message['id'] ?? null;
        $is_group = (strpos($chat_id, '@g.us') !== false);
        
        if (!$sender || !$message_id) {
            log_message('debug', 'Message ignoré: pas de sender ou pas de message_id');
            return;
        }
        
        // Ignorer les messages envoyés par le bot lui-même
        if (isset($message['fromMe']) && $message['fromMe'] === true) {
            log_message('debug', 'Message ignoré: envoyé par le bot');
            return;
        }
        
        // Extraire le contenu média
        $media_data = $this->extract_media_content($message, $message_type);
        
        // Téléchargement local du média si nécessaire (pour les admins)
        if (!empty($media_data['media_url']) && in_array($message_type, ['image','video','audio','document','sticker'])) {
            $local_path = $this->download_media_locally($media_data['media_url'], $message_type);
            if ($local_path) {
                $media_data['local_media_path'] = $local_path;
                $media_data['media_url'] = base_url($local_path);
            }
        }
        
        // Enregistrer/mettre à jour le groupe et le participant
        if ($is_group && $chat_id) {
            $this->Group_model->upsert_group($chat_id, $message['chatName'] ?? 'Groupe WhatsApp');
            $this->Participant_model->upsert_participant($chat_id, $sender, $sender_name);
        }
        
        // Vérifier si l'utilisateur est bloqué
        if ($this->Participant_model->is_blocked($sender)) {
            log_message('info', 'Message ignoré - utilisateur bloqué: ' . $sender);
            // Optionnel: envoyer un message privé pour dire qu'il est bloqué
            return;
        }
        
        // Déterminer si c'est un admin
        $admin_numbers = $this->config->item('admin_numbers');
        // Normaliser les numéros pour la comparaison
        $normalized_sender = $this->normalize_phone($sender);
        $is_admin = false;
        foreach ($admin_numbers as $admin_num) {
            if ($this->normalize_phone($admin_num) === $normalized_sender) {
                $is_admin = true;
                break;
            }
        }
        
        log_message('info', "Message de {$sender} (admin=" . ($is_admin ? 'OUI' : 'NON') . ") type={$message_type} dans " . ($is_group ? 'groupe' : 'privé'));
        
        // ================================
        // VALIDATION DU MESSAGE
        // ================================
        $is_valid = $this->validate_message($message_type, $media_data, $is_admin);
        
        if (!$is_valid) {
            // MEMBRE A ENVOYÉ UN TYPE INTERDIT (média, lien, localisation, contact, etc.)
            $this->whatsapp_whapi->react_to_message($message_id, '🚫');
            $this->handle_violation($sender, $message_type, $media_data, $chat_id);
            log_message('info', "🚫 VIOLATION de {$sender}: type={$message_type} dans {$chat_id}");
            
            // Envoyer un avertissement privé au membre (une seule fois par type de violation)
            $this->send_warning_to_member($sender, $message_type);
            return;
        }
        
        // ================================
        // TRAITEMENT ADMIN
        // ================================
        if ($is_admin) {
            // Détecter la commande de diffusion dans le texte
            $target_type = 'both'; // Par défaut: groupes + inbox
            $message_text = trim($media_data['message'] ?? $media_data['caption'] ?? '');
            
            // Vérifier si le message commence par une commande
            if (stripos($message_text, '#groupes') === 0) {
                $target_type = 'groups';
                // Retirer la commande du message diffusé
                $media_data['message'] = trim(substr($message_text, strlen('#groupes')));
                $media_data['caption'] = trim(substr($message_text, strlen('#groupes')));
            } elseif (stripos($message_text, '#inbox') === 0) {
                $target_type = 'inbox';
                $media_data['message'] = trim(substr($message_text, strlen('#inbox')));
                $media_data['caption'] = trim(substr($message_text, strlen('#inbox')));
            }
            
            // Réagir avec ✅ pour confirmer la réception du broadcast
            $this->whatsapp_whapi->react_to_message($message_id, '✅');
            
            // Lancer la diffusion
            $this->broadcast_to_all($media_data, $sender, $sender_name, $target_type, $chat_id);
            
            log_message('info', "✅ BROADCAST admin {$sender} vers {$target_type} (source: {$chat_id})");
            return;
        }
        
        // ================================
        // TRAITEMENT MEMBRE (TEXTE VALIDE)
        // ================================
        // Le message texte valide du membre reste dans le groupe (comportement normal WhatsApp)
        // On ne fait rien de spécial, WhatsApp gère déjà l'affichage
        log_message('info', "Message membre accepté: {$sender} dans {$chat_id}");
    }
    
    /**
     * Télécharge un média localement pour persistance
     */
    private function download_media_locally($url, $type) {
        $storage_path = $this->config->item('whapi')['media_storage_path'];
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0755, true);
        }
        
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $extensions = [
            'image' => 'jpg',
            'video' => 'mp4',
            'audio' => 'ogg',
            'document' => 'pdf',
            'sticker' => 'webp'
        ];
        $filename = uniqid('wa_') . '.' . ($ext ?: ($extensions[$type] ?? 'bin'));
        $local_file = $storage_path . $filename;
        
        if ($this->whatsapp_whapi->download_media($url, $local_file)) {
            return 'uploads/whatsapp_media/' . $filename;
        }
        return null;
    }
    
    /**
     * Extrait le contenu média du message selon son type
     */
    private function extract_media_content($message, $type) {
        $media_data = [
            'media_type' => $type,
            'message' => null,
            'media_url' => null,
            'caption' => null,
            'filename' => null,
            'local_media_path' => null,
            'latitude' => null,
            'longitude' => null,
            'location_name' => null
        ];
        
        switch($type) {
            case 'text':
                $media_data['message'] = $message['body'] ?? '';
                break;
                
            case 'image':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['image']['link'] ?? null;
                $media_data['caption'] = $message['caption'] ?? $message['image']['caption'] ?? null;
                break;
                
            case 'video':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['video']['link'] ?? null;
                $media_data['caption'] = $message['caption'] ?? $message['video']['caption'] ?? null;
                break;
                
            case 'audio':
            case 'voice':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['audio']['link'] ?? null;
                $media_data['media_type'] = 'audio';
                break;
                
            case 'document':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['document']['link'] ?? null;
                $media_data['caption'] = $message['caption'] ?? $message['document']['caption'] ?? null;
                $media_data['filename'] = $message['filename'] ?? $message['document']['filename'] ?? 'document';
                break;
                
            case 'sticker':
                $media_data['media_url'] = $message['mediaUrl'] ?? $message['sticker']['link'] ?? null;
                break;
                
            case 'location':
                $media_data['latitude'] = $message['location']['latitude'] ?? null;
                $media_data['longitude'] = $message['location']['longitude'] ?? null;
                $media_data['location_name'] = $message['location']['name'] ?? null;
                break;
                
            case 'contact':
            case 'contacts':
            case 'vcard':
                $media_data['message'] = '[Contact partagé]';
                break;
                
            case 'poll':
                $media_data['message'] = '[Sondage]';
                break;
                
            default:
                $media_data['message'] = '[Type non supporté: ' . $type . ']';
                break;
        }
        
        return $media_data;
    }
    
    /**
     * VALIDE UN MESSAGE SELON LES RÈGLES
     * 
     * ADMIN: Tout est autorisé
     * MEMBRE: UNIQUEMENT texte (pas de média, pas de lien, pas de localisation, pas de contact)
     */
    private function validate_message($type, $media_data, $is_admin) {
        // ADMIN: Tout est permis
        if ($is_admin) {
            return true;
        }
        
        // MEMBRE: Vérifier que c'est du texte PUR
        $allowed_types = $this->config->item('allowed_for_members');
        
        // Vérifier le type de média
        if (!in_array($type, $allowed_types)) {
            log_message('info', "Validation échouée: type '{$type}' non autorisé pour membre");
            return false;
        }
        
        // Vérifier les liens dans le texte
        $text = $media_data['message'] ?? '';
        $blocked_patterns = $this->config->item('blocked_patterns');
        
        foreach ($blocked_patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                log_message('info', "Validation échouée: lien détecté dans le texte");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Gère une violation (média interdit, lien, etc.)
     */
    private function handle_violation($phone, $type, $media_data, $groupe_id) {
        $violation_msg = ($type === 'text') 
            ? ($media_data['message'] ?? '') 
            : 'Type interdit: ' . $type;
            
        $this->Participant_model->log_violation($phone, $type, $violation_msg, $groupe_id);
        
        $is_now_blocked = $this->Participant_model->increment_violation($phone);
        
        if ($is_now_blocked) {
            log_message('info', '🔒 Utilisateur BLOQUÉ après 3 violations: ' . $phone);
        }
    }
    
    /**
     * Envoie un avertissement privé au membre violateur
     */
    private function send_warning_to_member($phone, $violation_type) {
        $warnings = [
            'image' => "🚫 Les images ne sont pas autorisées dans ce groupe. Seul le texte est permis.",
            'video' => "🚫 Les vidéos ne sont pas autorisées. Seul le texte est permis.",
            'audio' => "🚫 Les messages audio ne sont pas autorisés. Seul le texte est permis.",
            'document' => "🚫 Les documents ne sont pas autorisés. Seul le texte est permis.",
            'sticker' => "🚫 Les stickers ne sont pas autorisés. Seul le texte est permis.",
            'location' => "🚫 Le partage de localisation n'est pas autorisé. Seul le texte est permis.",
            'contact' => "🚫 Le partage de contacts n'est pas autorisé. Seul le texte est permis.",
            'vcard' => "🚫 Le partage de contacts n'est pas autorisé. Seul le texte est permis.",
            'text' => "🚫 Les liens ne sont pas autorisés dans ce groupe. Seul le texte sans lien est permis.",
            'poll' => "🚫 Les sondages ne sont pas autorisés. Seul le texte est permis.",
            'unknown' => "🚫 Ce type de message n'est pas autorisé. Seul le texte est permis."
        ];
        
        $warning = $warnings[$violation_type] ?? $warnings['unknown'];
        $warning .= "\n\n⚠️ Après 3 violations, vous serez bloqué automatiquement.";
        
        // Envoyer en privé (pas dans le groupe)
        $this->whatsapp_whapi->send_text($phone, $warning);
    }
    
    /**
     * DIFFUSION DU MESSAGE ADMIN VERS LES CIBLES
     * 
     * @param array $media_data Données du média
     * @param string $sender_number Numéro de l'admin
     * @param string $sender_name Nom de l'admin
     * @param string $target_type 'groups', 'inbox', ou 'both'
     * @param string $source_group_id Groupe source (pour l'exclure de la diffusion groupes)
     */
    private function broadcast_to_all($media_data, $sender_number, $sender_name, $target_type, $source_group_id = null) {
        // Récupérer les destinataires
        $groups = [];
        $participants = [];
        
        if ($target_type === 'groups' || $target_type === 'both') {
            // Si source est un groupe, exclure le groupe source pour éviter doublon
            if ($source_group_id && strpos($source_group_id, '@g.us') !== false) {
                $groups = $this->Group_model->get_active_groups_except($source_group_id);
            } else {
                $groups = $this->Group_model->get_active_groups();
            }
        }
        
        if ($target_type === 'inbox' || $target_type === 'both') {
            $participants = $this->Participant_model->get_all_unique_participants();
        }
        
        // Préparer les données de la queue
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
        
        // Ajouter les messages inbox en batch
        if (!empty($participants)) {
            $this->Inbox_model->add_to_inbox($queue_id, $participants, $media_data);
        }
        
        // Logger dans whatsapp_logs
        $this->db->insert('whatsapp_logs', [
            'order_request_id' => null,
            'product_id' => null,
            'phone_number' => $sender_number,
            'message_content' => '[BROADCAST] ' . ($media_data['message'] ?? $media_data['caption'] ?? 'Média'),
            'message_type' => 'order_confirmation',
            'status' => 'pending',
            'sent_at' => null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        log_message('info', sprintf(
            '📢 BROADCAST ajouté: Queue=%d, Groupes=%d, Inbox=%d, Type=%s',
            $queue_id,
            count($groups),
            count($participants),
            $target_type
        ));
    }
    
    /**
     * Traite les statuts de livraison
     */
    private function process_statuses($statuses) {
        foreach ($statuses as $status) {
            if (isset($status['id']) && isset($status['status'])) {
                $this->db->where('message_id', $status['id']);
                $this->db->update('messages_inbox', ['status' => $status['status']]);
            }
        }
    }
    
    /**
     * Normalise un numéro de téléphone pour comparaison
     */
    private function normalize_phone($number) {
        $number = preg_replace('/[^0-9]/', '', $number);
        // Enlever le préfixe 237 si présent pour comparaison
        if (strpos($number, '237') === 0) {
            $number = substr($number, 3);
        }
        return $number;
    }
}