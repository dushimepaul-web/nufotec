<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }
    
   public function index($token = null)
{
    // ⚡ Réponse rapide HTTP (important pour Whapi)
    header('Content-Type: application/json');

    try {

        // ==============================
        // 1. TOKEN CHECK (SAFE + CLEAN)
        // ==============================
        $url_token = $token ?? $this->input->get('token', true);
        $header_token = $this->input->get_request_header('X-Webhook-Token', true);
        $expected_token = $this->whapi_library->get_setting('webhook_token');

        if ($url_token !== $expected_token && $header_token !== $expected_token) {
            log_message('error', 'Webhook token invalid');

            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        // ==============================
        // 2. READ RAW INPUT (SAFE)
        // ==============================
        $raw = file_get_contents('php://input');

        if (empty($raw)) {
            // Whapi peut envoyer ping vide → ne pas considérer comme erreur critique
            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'message' => 'empty payload ignored'
            ]);
            return;
        }

        // ==============================
        // 3. TRY JSON DECODE SAFE
        // ==============================
        $payload = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            log_message('error', 'Invalid JSON: ' . $raw);

            // fallback form-data
            $payload = !empty($_POST) ? $_POST : null;
        }

        // ==============================
        // 4. FINAL FALLBACK
        // ==============================
        if (!$payload) {
            http_response_code(200);
            echo json_encode([
                'status' => 'ignored',
                'reason' => 'no valid payload',
                'raw' => $raw
            ]);
            return;
        }

        // ==============================
        // 5. QUICK ACK (IMPORTANT WHAPI)
        // ==============================
        http_response_code(200);
        echo json_encode([
            'status' => 'accepted'
        ]);

        // Libère la réponse immédiatement
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // ==============================
        // 6. ASYNC PROCESSING
        // ==============================
        $this->process_webhook($payload);

    } catch (Exception $e) {

        log_message('error', $e->getMessage());

        http_response_code(200);
        echo json_encode([
            'status' => 'error',
            'message' => 'internal handler error'
        ]);
    }
}
    
  

    
    private function process_webhook($payload) {
        // Extraire les données
        $message_type = $payload['type'] ?? 'unknown';
        $group_id = $payload['chat']['id'] ?? null;
        $sender = $payload['from']['phone'] ?? null;
        $message_text = $payload['text'] ?? '';
        $message_id = $payload['id'] ?? null;
        
        // Détecter les médias
        $has_media = false;
        $media_url = null;
        $media_type = null;
        
        if (isset($payload['media'])) {
            $has_media = true;
            $media_url = $payload['media']['url'] ?? null;
            $media_type = $payload['media']['type'] ?? null;
        }
        
        // Vérifier si c'est un message entrant dans un groupe cible (pas le maître)
        $master_group_id = $this->whapi_library->get_setting('master_group_id');
        
        if ($group_id !== $master_group_id) {
            // C'est un message dans un groupe cible - Appliquer les règles de sécurité
            $is_admin = $this->whapi_library->is_group_admin($group_id, $sender);
            
            if (!$is_admin) {
                // Supprimer les médias et liens des non-admins dans TOUS les groupes
                if ($has_media) {
                    $this->whapi_library->delete_message($message_id);
                    $this->db->insert('whatsapp_security_logs', [
                        'group_id' => $group_id,
                        'sender' => $sender,
                        'action_type' => 'auto_deleted_media',
                        'reason' => 'Non-admin sent media in target group',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    return ['status' => 'deleted', 'reason' => 'media_not_allowed'];
                }
                
                if (contains_link($message_text)) {
                    $this->whapi_library->delete_message($message_id);
                    $this->db->insert('whatsapp_security_logs', [
                        'group_id' => $group_id,
                        'sender' => $sender,
                        'action_type' => 'auto_deleted_link',
                        'reason' => 'Non-admin sent link in target group',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    return ['status' => 'deleted', 'reason' => 'link_not_allowed'];
                }
            }
            
            // Log simple du message (texte autorisé)
            log_whatsapp(null, null, $sender, $message_text, $message_type, 'received');
            return ['status' => 'logged', 'type' => 'target_group_message'];
        }
        
        // Ici, c'est le GROUPE MAÎTRE - Traiter la diffusion
        // Déterminer la cible
        $target_type = 'both';
        if (strpos($message_text, '#groupe') === 0) {
            $target_type = 'group';
            $message_text = trim(substr($message_text, 7));
        } elseif (strpos($message_text, '#inbox') === 0) {
            $target_type = 'inbox';
            $message_text = trim(substr($message_text, 6));
        } elseif (strpos($message_text, '#template:') === 0) {
            // Support des templates
            preg_match('/#template:([a-zA-Z0-9_]+)/', $message_text, $matches);
            $template_name = $matches[1] ?? null;
            if ($template_name) {
                $template = $this->db->get_where('whatsapp_templates', ['name' => $template_name])->row();
                if ($template) {
                    $message_text = $template->content;
                    // Extraire les variables si présentes
                    if (preg_match('/\|\|(.*)/', $message_text, $var_matches)) {
                        $vars = explode(',', $var_matches[1]);
                        // Traitement des variables...
                    }
                }
            }
            $message_text = preg_replace('/#template:[a-zA-Z0-9_]+\s*/', '', $message_text);
        }
        
        // Nettoyer le message
        $message_text = sanitize_message($message_text);
        
        // Préparer les données
        $message_data = [
            'type' => $message_type,
            'text' => $message_text,
            'group_id' => $group_id,
            'sender' => $sender,
            'message_id' => $message_id,
            'target_type' => $target_type,
            'has_media' => $has_media,
            'media_url' => $media_url,
            'media_type' => $media_type
        ];
        
        // Log
        log_whatsapp(null, null, $sender, $message_text, $message_type, 'received');
        
        // Distribuer
        $distribution_result = $this->whapi_library->distribute_message($message_data, $sender);
        
        return [
            'status' => 'processed',
            'target_type' => $target_type,
            'distribution' => $distribution_result
        ];
    }
}