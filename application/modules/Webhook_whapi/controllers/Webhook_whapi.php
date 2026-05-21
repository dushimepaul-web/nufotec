<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }

    public function index($token = null) {

        // ⚡ 1. Réponse immédiate (CRITIQUE POUR WHAPI)
        http_response_code(200);
        header('Content-Type: application/json');

        // ⚡ capture rapide payload
        $rawPayload = file_get_contents('php://input');

        if (!$rawPayload) {
            echo json_encode(['status' => 'empty']);
            return;
        }

        // ⚡ 2. décodage safe
        $payload = json_decode($rawPayload, true);

        if (!$payload) {
            echo json_encode(['status' => 'invalid_json']);
            return;
        }

        // ⚡ 3. validation token ultra rapide
        $url_token = $token ?? $this->input->get('token');
        $expected_token = $this->whapi_library->get_setting('webhook_token');

        if ($url_token !== $expected_token) {
            log_message('error', 'Webhook token invalid');

            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        // ⚡ 4. PUSH IMMÉDIAT EN QUEUE (IMPORTANT)
        $this->load->library('redis');

        $this->redis->lpush('whapi:webhook:queue', json_encode($payload));

        // ⚡ 5. IMPORTANT : répondre AVANT traitement
        echo json_encode([
            'status' => 'accepted'
        ]);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // ⚠️ 6. traitement ASYNC (après réponse)
        $this->process_async($payload);
    }

    private function process_async($payload)
    {
        try {

            $message_type = $payload['type'] ?? 'unknown';
            $group_id = $payload['chat']['id'] ?? null;
            $sender = $payload['from']['phone'] ?? null;
            $message_text = $payload['text'] ?? '';
            $message_id = $payload['id'] ?? null;

            // ⚡ MEDIA detection
            $has_media = isset($payload['media']);

            // ⚡ MASTER GROUP CHECK
            $master_group_id = $this->whapi_library->get_setting('master_group_id');

            // =========================
            // 1. TARGET GROUP LOGIC
            // =========================
            if ($group_id !== $master_group_id) {

                $is_admin = $this->whapi_library->is_group_admin($group_id, $sender);

                if (!$is_admin) {

                    if ($has_media) {
                        // ⚠️ IMPORTANT: ne PAS bloquer webhook si delete fail
                        @$this->whapi_library->delete_message($message_id);
                    }

                    if (function_exists('contains_link') && contains_link($message_text)) {
                        @$this->whapi_library->delete_message($message_id);
                    }
                }

                // log rapide
                $this->db->insert('whatsapp_logs', [
                    'group_id' => $group_id,
                    'sender' => $sender,
                    'message' => $message_text,
                    'type' => 'received',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return;
            }

            // =========================
            // 2. MASTER GROUP = BROADCAST
            // =========================

            $target_type = 'both';

            if (strpos($message_text, '#groupe') === 0) {
                $target_type = 'group';
                $message_text = trim(substr($message_text, 7));
            }

            if (strpos($message_text, '#inbox') === 0) {
                $target_type = 'inbox';
                $message_text = trim(substr($message_text, 6));
            }

            // ⚡ CLEAN MESSAGE
            if (function_exists('sanitize_message')) {
                $message_text = sanitize_message($message_text);
            }

            // ⚡ PUSH BROADCAST QUEUE (IMPORTANT)
            $this->db->insert('whatsapp_queue', [
                'message' => $message_text,
                'sender' => $sender,
                'target_type' => $target_type,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            log_message('error', $e->getMessage());
        }
    }
}