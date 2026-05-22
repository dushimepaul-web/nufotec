<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }

    public function index($token = null) {
        $raw_input = file_get_contents('php://input');
        $input     = json_decode($raw_input, true);

        // ==============================================
        // CORRECTION CRITIQUE : répondre 200 OK IMMÉDIATEMENT
        // avant tout traitement pour éviter le ETIMEDOUT.
        // CodeIgniter bufferise la sortie via set_output(),
        // donc Whapi ne reçoit le 200 qu'à la fin de l'exécution.
        // On bypass CI et on flush manuellement ici.
        // ==============================================
        http_response_code(200);
        header('Content-Type: application/json');
        header('Connection: close');
        header('Content-Encoding: none');

        $response_body = json_encode(['status' => 'received']);
        header('Content-Length: ' . strlen($response_body));
        echo $response_body;

        // Vider tous les buffers de sortie et fermer la connexion
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();

        // À partir d'ici, le client (Whapi) a reçu sa réponse 200.
        // On continue le traitement en arrière-plan.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request(); // Pour PHP-FPM : libère la connexion immédiatement
        }

        // Payload vide : rien à traiter
        if (!$input) {
            log_message('error', 'Webhook: payload vide');
            return;
        }

        log_message('info', 'Webhook reçu: ' . substr($raw_input, 0, 500));

        // Vérification du token (si configuré)
        $expected_token = $this->whapi_library->get_setting('webhook_token');
        if (!empty($expected_token)) {
            $url_token = $token ?? $this->input->get('token');
            if ($url_token !== $expected_token) {
                log_message('error', 'Webhook: token invalide');
                return;
            }
        }

        $this->process_message($input);
    }

    private function process_message($payload) {
        // ==============================================
        // 1. IGNORER LES MESSAGES DU BOT (fromMe)
        // ==============================================
        if (!empty($payload['fromMe']) || !empty($payload['isFromMe'])) {
            log_message('debug', 'Webhook: message du bot ignoré');
            return;
        }

        // ==============================================
        // 2. EXTRAIRE LES DONNÉES
        // ==============================================
        $message_type = $payload['type'] ?? 'unknown';
        $message_id   = $payload['id'] ?? null;

        // Expéditeur
        $sender = null;
        if (isset($payload['from']['phone'])) {
            $sender = $payload['from']['phone'];
        } elseif (isset($payload['from'])) {
            $sender = $payload['from'];
        } elseif (isset($payload['author'])) {
            $sender = $payload['author'];
        }

        // Chat / groupe
        $chat_id  = null;
        $is_group = false;
        if (isset($payload['chat']['id'])) {
            $chat_id  = $payload['chat']['id'];
            $is_group = strpos($chat_id, '@g.us') !== false;
        } elseif (isset($payload['chatId'])) {
            $chat_id  = $payload['chatId'];
            $is_group = strpos($chat_id, '@g.us') !== false;
        }

        // Texte
        $message_text = '';
        if ($message_type === 'text' && isset($payload['text']['body'])) {
            $message_text = $payload['text']['body'];
        } elseif ($message_type === 'text' && isset($payload['text'])) {
            $message_text = is_string($payload['text']) ? $payload['text'] : ($payload['text']['body'] ?? '');
        } elseif (isset($payload['text'])) {
            $message_text = is_string($payload['text']) ? $payload['text'] : '';
        }

        // Médias
        $media_types   = ['image', 'video', 'audio', 'document', 'sticker'];
        $has_media     = in_array($message_type, $media_types);
        $media_url     = null;
        $media_caption = null;

        if ($has_media && isset($payload[$message_type])) {
            $media_url     = $payload[$message_type]['url'] ?? $payload[$message_type]['link'] ?? null;
            $media_caption = $payload[$message_type]['caption'] ?? null;
            if ($media_caption) {
                $message_text = $media_caption;
            }
        }

        if (!$sender) {
            log_message('info', 'Webhook: sender manquant, ignoré');
            return;
        }

        $message_text = sanitize_message($message_text);

        // ==============================================
        // 3. VÉRIFIER BLACKLIST
        // ==============================================
        $is_blacklisted = $this->db
            ->where('phone_number', format_phone($sender))
            ->get('whatsapp_blacklist')
            ->num_rows() > 0;

        if ($is_blacklisted) {
            log_message('info', "Webhook: message ignoré - $sender est blacklisté");
            return;
        }

        // ==============================================
        // 4. DÉTECTER SI ADMIN
        // ==============================================
        $admin_numbers = json_decode($this->whapi_library->get_setting('admin_numbers') ?? '[]', true) ?: [];
        $sender_clean  = format_phone($sender);
        $is_admin      = in_array($sender_clean, $admin_numbers);

        if (!$is_admin && $is_group && $chat_id) {
            $is_admin = $this->db
                ->where('groupe_id', $chat_id)
                ->where('phone_formatted', $sender_clean)
                ->where('is_admin', 1)
                ->get('whatsapp_participants')
                ->num_rows() > 0;
        }

        // ==============================================
        // 5. SYNC GROUPE ET PARTICIPANT
        // ==============================================
        if ($is_group && $chat_id) {
            $this->_upsert_group($chat_id, $payload['chat']['name'] ?? $payload['chatName'] ?? null);
            $this->_upsert_participant($chat_id, $sender, $payload['pushName'] ?? $payload['notifyName'] ?? null);
        }

        // ==============================================
        // 6. RÈGLES DE SÉCURITÉ POUR NON-ADMINS
        // ==============================================
        $master_group_id = $this->whapi_library->get_setting('master_group_id');
        $is_master_group = ($chat_id === $master_group_id);

        if (!$is_admin) {
            $violation        = false;
            $violation_reason = '';

            if ($has_media) {
                $violation        = true;
                $violation_reason = 'media_non_autorise';
            } elseif (contains_link($message_text)) {
                $violation        = true;
                $violation_reason = 'lien_non_autorise';
            } elseif (contains_mention($message_text)) {
                $violation        = true;
                $violation_reason = 'mention_non_autorisee';
            } elseif (contains_phone($message_text)) {
                $violation        = true;
                $violation_reason = 'phone_number_non_autorise';
            }

            if ($violation) {
                if ($message_id) {
                    $this->whapi_library->delete_message($message_id);
                }

                $this->db->insert('whatsapp_security_logs', [
                    'group_id'    => $chat_id,
                    'sender'      => format_phone($sender),
                    'action_type' => $violation_reason,
                    'reason'      => 'Message supprimé automatiquement - non-admin',
                    'created_at'  => date('Y-m-d H:i:s')
                ]);

                $this->_increment_violation($sender);

                log_message('info', "Webhook: violation $violation_reason de $sender dans " . ($chat_id ?? 'prive'));
                return;
            }

            log_message('debug', "Webhook: message membre autorisé de $sender");
            return;
        }

        // ==============================================
        // 7. ADMIN - TRAITEMENT DU BROADCAST
        // ==============================================
        if (!$is_master_group) {
            log_message('debug', "Webhook: message admin dans groupe cible, pas de broadcast");
            return;
        }

        $target_type = 'both';

        if (strpos($message_text, '#groupe') === 0) {
            $target_type  = 'group';
            $message_text = trim(substr($message_text, 7));
        } elseif (strpos($message_text, '#inbox') === 0) {
            $target_type  = 'inbox';
            $message_text = trim(substr($message_text, 6));
        } elseif (strpos($message_text, '#template:') === 0) {
            preg_match('/#template:([a-zA-Z0-9_]+)/', $message_text, $matches);
            $template_name = $matches[1] ?? null;
            if ($template_name) {
                $template = $this->db->get_where('whatsapp_templates', ['name' => $template_name])->row();
                if ($template) {
                    $message_text = $template->content;
                }
            }
            $message_text = preg_replace('/#template:[a-zA-Z0-9_]+\s*/', '', $message_text);
        }

        $message_text = sanitize_message($message_text);

        log_whatsapp(null, null, $sender, $message_text, $message_type, 'received');

        $message_data = [
            'type'       => $message_type,
            'text'       => $message_text,
            'group_id'   => $chat_id,
            'sender'     => $sender,
            'message_id' => $message_id,
            'target_type'=> $target_type,
            'has_media'  => $has_media,
            'media_url'  => $media_url,
            'media_type' => $message_type
        ];

        $result = $this->whapi_library->distribute_message($message_data, $sender);

        log_message('info', "Webhook: broadcast admin $sender vers $target_type - " . json_encode($result));
    }

    private function process_statuses($statuses) {
        if (!is_array($statuses)) return;

        foreach ($statuses as $status) {
            $message_id  = $status['id'] ?? null;
            $status_type = $status['type'] ?? null;

            if ($message_id) {
                $this->db->where('message_id', $message_id);
                $this->db->update('whatsapp_queue', [
                    'delivery_status' => $status_type,
                    'updated_at'      => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    // ==============================================
    // MÉTHODES PRIVÉES
    // ==============================================

    private function _upsert_group($groupe_id, $nom = null) {
        $exists = $this->db->get_where('groupes_whatsapp', ['groupe_id' => $groupe_id])->row();

        $data = ['groupe_id' => $groupe_id, 'updated_at' => date('Y-m-d H:i:s')];
        if ($nom) $data['nom'] = $nom;

        if ($exists) {
            $this->db->where('id', $exists->id)->update('groupes_whatsapp', $data);
        } else {
            $data['actif']      = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('groupes_whatsapp', $data);
        }
    }

    private function _upsert_participant($groupe_id, $phone, $name = null) {
        $phone_formatted = format_phone($phone);

        $exists = $this->db
            ->where('groupe_id', $groupe_id)
            ->where('phone_formatted', $phone_formatted)
            ->get('whatsapp_participants')
            ->row();

        $data = [
            'groupe_id'       => $groupe_id,
            'phone'           => $phone,
            'phone_formatted' => $phone_formatted,
            'synced_at'       => date('Y-m-d H:i:s')
        ];
        if ($name) $data['profile_name'] = $name;

        if ($exists) {
            $this->db->where('id', $exists->id)->update('whatsapp_participants', $data);
        } else {
            $data['is_admin']   = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('whatsapp_participants', $data);
        }
    }

    private function _increment_violation($phone) {
        $phone_formatted = format_phone($phone);

        $participant = $this->db
            ->where('phone_formatted', $phone_formatted)
            ->get('whatsapp_participants')
            ->row();

        if (!$participant) return;

        $new_count = ($participant->violation_count ?? 0) + 1;

        $this->db->where('phone_formatted', $phone_formatted);
        $this->db->update('whatsapp_participants', ['violation_count' => $new_count]);

        // Blacklist automatique après 5 violations
        if ($new_count >= 5) {
            $this->db->insert('whatsapp_blacklist', [
                'phone_number' => $phone_formatted,
                'reason'       => 'Auto-blacklist: 5 violations',
                'created_at'   => date('Y-m-d H:i:s')
            ]);
            log_message('info', "Webhook: $phone auto-blacklisté après $new_count violations");
        }
    }
}