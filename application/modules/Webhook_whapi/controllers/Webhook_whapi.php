<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }

    // ==============================================
    // POINT D'ENTRÉE PRINCIPAL
    // ==============================================

    public function index($token = null) {
        // Lire le payload AVANT de répondre
        $raw_input = file_get_contents('php://input');

        // ✅ RÉPONDRE IMMÉDIATEMENT à Whapi pour éviter le timeout ETIMEDOUT
        $this->_send_immediate_response(['status' => 'received']);

        // Valider le payload JSON
        $input = json_decode($raw_input, true);
        if (empty($input) || !is_array($input)) {
            log_message('error', 'Webhook: payload vide ou invalide - ' . substr($raw_input, 0, 200));
            return;
        }

        // Log debug (commenter en production)
        log_message('info', 'Webhook reçu: ' . substr($raw_input, 0, 500));

        // Vérification du token webhook (optionnel)
        $expected_token = $this->whapi_library->get_setting('webhook_token');
        if (!empty($expected_token)) {
            $url_token = $token ?? $this->input->get('token');
            if ($url_token !== $expected_token) {
                log_message('error', 'Webhook: token invalide reçu: ' . $url_token);
                return;
            }
        }

        // Traiter le message
        $this->process_message($input);
    }

    /**
     * Envoie la réponse HTTP immédiatement et ferme la connexion
     * Le traitement continue en arrière-plan (si PHP-FPM disponible)
     */
    private function _send_immediate_response($data) {
        if (headers_sent()) return;

        $json   = json_encode($data);
        $length = strlen($json);

        header('Content-Type: application/json');
        header('Content-Length: ' . $length);
        header('Connection: close');
        header('Cache-Control: no-cache');

        echo $json;

        // Vider les buffers de sortie
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();

        // PHP-FPM : ferme la connexion HTTP, le script continue
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    // ==============================================
    // TRAITEMENT DU MESSAGE
    // ==============================================

    private function process_message($payload) {

        // 1. IGNORER LES MESSAGES DU BOT
        if (!empty($payload['fromMe']) || !empty($payload['isFromMe'])) {
            log_message('debug', 'Webhook: message du bot ignoré');
            return;
        }

        // 2. EXTRAIRE LES DONNÉES
        $message_type = $payload['type'] ?? 'unknown';
        $message_id   = $payload['id']   ?? null;

        // Expéditeur
        $sender = null;
        if (!empty($payload['from']['phone'])) {
            $sender = $payload['from']['phone'];
        } elseif (!empty($payload['from']) && is_string($payload['from'])) {
            $sender = $payload['from'];
        } elseif (!empty($payload['author'])) {
            $sender = $payload['author'];
        }

        if (empty($sender)) {
            log_message('info', 'Webhook: sender manquant, ignoré');
            return;
        }

        // Chat / Groupe
        $chat_id  = null;
        $is_group = false;

        if (!empty($payload['chat']['id'])) {
            $chat_id  = $payload['chat']['id'];
        } elseif (!empty($payload['chatId'])) {
            $chat_id  = $payload['chatId'];
        }
        $is_group = !empty($chat_id) && strpos($chat_id, '@g.us') !== false;

        // Texte du message
        $message_text = '';
        if ($message_type === 'text') {
            if (!empty($payload['text']['body'])) {
                $message_text = $payload['text']['body'];
            } elseif (!empty($payload['text']) && is_string($payload['text'])) {
                $message_text = $payload['text'];
            }
        } elseif (!empty($payload['text'])) {
            $message_text = is_string($payload['text']) ? $payload['text'] : '';
        }

        // Médias
        $media_types   = ['image', 'video', 'audio', 'document', 'sticker'];
        $has_media     = in_array($message_type, $media_types, true);
        $media_url     = null;
        $media_caption = null;

        if ($has_media && !empty($payload[$message_type])) {
            $media_url     = $payload[$message_type]['url']     ?? $payload[$message_type]['link'] ?? null;
            $media_caption = $payload[$message_type]['caption'] ?? null;
            if ($media_caption) {
                $message_text = $media_caption;
            }
        }

        $message_text   = sanitize_message($message_text);
        $sender_clean   = format_phone($sender);

        // 3. VÉRIFIER BLACKLIST
        $is_blacklisted = $this->_is_blacklisted($sender_clean);
        if ($is_blacklisted) {
            log_message('info', "Webhook: message ignoré - $sender_clean est blacklisté");
            return;
        }

        // 4. DÉTECTER SI ADMIN
        $admin_numbers = json_decode($this->whapi_library->get_setting('admin_numbers') ?? '[]', true) ?: [];
        $is_admin      = in_array($sender_clean, $admin_numbers, true);

        // Vérifier admin dans le groupe
        if (!$is_admin && $is_group && $chat_id) {
            $is_admin = $this->db
                ->where('groupe_id', $chat_id)
                ->where('phone_formatted', $sender_clean)
                ->where('is_admin', 1)
                ->get('whatsapp_participants')
                ->num_rows() > 0;
        }

        // 5. SYNC GROUPE ET PARTICIPANT (non-bloquant)
        if ($is_group && $chat_id) {
            $this->_upsert_group($chat_id, $payload['chat']['name'] ?? $payload['chatName'] ?? null);
            $this->_upsert_participant($chat_id, $sender, $payload['pushName'] ?? $payload['notifyName'] ?? null);
        }

        // 6. RÈGLES DE SÉCURITÉ POUR NON-ADMINS
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
                // Supprimer le message
                if ($message_id) {
                    $this->whapi_library->delete_message($message_id);
                }

                // Log de sécurité
                $this->_log_security($chat_id, $sender_clean, $violation_reason, 'Message supprimé automatiquement - non-admin');

                // Incrémenter violations (auto-blacklist à 5)
                $this->_increment_violation($sender_clean);

                log_message('info', "Webhook: violation $violation_reason de $sender_clean dans " . ($chat_id ?? 'prive'));
                return;
            }

            // Membre simple avec texte OK
            log_message('debug', "Webhook: message membre autorisé de $sender_clean");
            return;
        }

        // 7. ADMIN - TRAITEMENT DU BROADCAST
        $master_group_id = $this->whapi_library->get_setting('master_group_id');
        $is_master_group = ($chat_id === $master_group_id);

        if (!$is_master_group) {
            log_message('debug', "Webhook: admin dans groupe cible, pas de broadcast");
            return;
        }

        // Interpréter les commandes #groupe / #inbox / #template
        $target_type  = 'both';
        $clean_text   = $message_text;

        if (strpos($clean_text, '#groupe') === 0) {
            $target_type = 'group';
            $clean_text  = trim(substr($clean_text, 7));
        } elseif (strpos($clean_text, '#inbox') === 0) {
            $target_type = 'inbox';
            $clean_text  = trim(substr($clean_text, 6));
        } elseif (strpos($clean_text, '#template:') === 0) {
            preg_match('/#template:([a-zA-Z0-9_]+)/', $clean_text, $matches);
            $template_name = $matches[1] ?? null;
            if ($template_name) {
                $template = $this->db->get_where('whatsapp_templates', ['name' => $template_name])->row();
                if ($template) {
                    $clean_text = $template->content;
                } else {
                    log_message('warning', "Webhook: template '$template_name' introuvable");
                    $clean_text = preg_replace('/#template:[a-zA-Z0-9_]+\s*/', '', $clean_text);
                }
            }
        }

        $clean_text = sanitize_message($clean_text);

        // Logger la réception
        log_whatsapp(null, null, $sender, $clean_text, $message_type, 'received');

        // Préparer les données pour distribution
        $message_data = [
            'type'        => $message_type,
            'text'        => $clean_text,
            'group_id'    => $chat_id,
            'sender'      => $sender,
            'message_id'  => $message_id,
            'target_type' => $target_type,
            'has_media'   => $has_media,
            'media_url'   => $media_url,
            'media_type'  => $message_type,
        ];

        $result = $this->whapi_library->distribute_message($message_data, $sender);
        log_message('info', "Webhook: broadcast admin $sender_clean vers $target_type - " . json_encode($result));
    }

    // ==============================================
    // MÉTHODES PRIVÉES
    // ==============================================

    private function _is_blacklisted($phone_formatted) {
        try {
            return $this->db
                ->where('phone_number', $phone_formatted)
                ->get('whatsapp_blacklist')
                ->num_rows() > 0;
        } catch (Exception $e) {
            log_message('error', '_is_blacklisted() failed: ' . $e->getMessage());
            return false;
        }
    }

    private function _log_security($group_id, $sender, $action_type, $reason) {
        try {
            $this->db->insert('whatsapp_security_logs', [
                'group_id'    => $group_id,
                'sender'      => $sender,
                'action_type' => $action_type,
                'reason'      => $reason,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            log_message('error', '_log_security() failed: ' . $e->getMessage());
        }
    }

    private function _upsert_group($groupe_id, $nom = null) {
        try {
            $exists = $this->db->get_where('groupes_whatsapp', ['groupe_id' => $groupe_id])->row();
            $data   = ['groupe_id' => $groupe_id, 'updated_at' => date('Y-m-d H:i:s')];
            if ($nom) $data['nom'] = $nom;

            if ($exists) {
                $this->db->where('id', $exists->id)->update('groupes_whatsapp', $data);
            } else {
                $data['actif']      = 1;
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('groupes_whatsapp', $data);
            }
        } catch (Exception $e) {
            log_message('error', "_upsert_group($groupe_id) failed: " . $e->getMessage());
        }
    }

    private function _upsert_participant($groupe_id, $phone, $name = null) {
        try {
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
                'synced_at'       => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
            if ($name) $data['profile_name'] = $name;

            if ($exists) {
                $this->db->where('id', $exists->id)->update('whatsapp_participants', $data);
            } else {
                $data['is_admin']        = 0;
                $data['violation_count'] = 0;
                $data['created_at']      = date('Y-m-d H:i:s');
                $this->db->insert('whatsapp_participants', $data);
            }
        } catch (Exception $e) {
            log_message('error', "_upsert_participant($phone) failed: " . $e->getMessage());
        }
    }

    private function _increment_violation($phone_formatted) {
        try {
            $participant = $this->db
                ->where('phone_formatted', $phone_formatted)
                ->get('whatsapp_participants')
                ->row();

            if (!$participant) return;

            $new_count = (int) ($participant->violation_count ?? 0) + 1;

            $this->db->where('phone_formatted', $phone_formatted);
            $this->db->update('whatsapp_participants', ['violation_count' => $new_count]);

            // Auto-blacklist après 5 violations
            if ($new_count >= 5) {
                $already = $this->db->get_where('whatsapp_blacklist', ['phone_number' => $phone_formatted])->row();
                if (!$already) {
                    $this->db->insert('whatsapp_blacklist', [
                        'phone_number' => $phone_formatted,
                        'reason'       => "Auto-blacklist: $new_count violations",
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                    log_message('info', "Webhook: $phone_formatted auto-blacklisté après $new_count violations");
                }
            }
        } catch (Exception $e) {
            log_message('error', "_increment_violation($phone_formatted) failed: " . $e->getMessage());
        }
    }
}