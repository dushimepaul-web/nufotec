<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }

    public function index($token = null)
{
    try {

        // =========================
        // 1. RÉPONSE IMMÉDIATE
        // =========================
        ignore_user_abort(true);
        set_time_limit(0);

        http_response_code(200);

        header('Content-Type: application/json');

        $response = json_encode([
            'success' => true
        ]);

        header('Content-Length: ' . strlen($response));

        echo $response;

        // Vider le buffer correctement
        if (ob_get_level() > 0) {
            ob_end_flush();
        }

        flush();

        // Pour Nginx + PHP-FPM
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // =========================
        // 2. VÉRIFICATION TOKEN
        // =========================
        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $header_token = $headers['X-Whapi-Token'] ?? '';
        $url_token    = $token ?? $this->input->get('token');

        $expected_token = $this->whapi_library
            ->get_setting('webhook_token');

        if (
            !empty($expected_token)
            && $header_token !== $expected_token
            && $url_token !== $expected_token
        ) {

            log_message(
                'error',
                'Webhook token invalide'
            );

            return;
        }

        // =========================
        // 3. PAYLOAD
        // =========================
        $raw = file_get_contents('php://input');

        if (empty($raw)) {
            log_message('info', 'Payload vide');
            return;
        }

        $payload = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            log_message(
                'error',
                'JSON invalide : ' . json_last_error_msg()
            );

            return;
        }

        // LOG DEBUG
        log_message('debug', 'Webhook RAW: ' . $raw);

        // =========================
        // 4. TRAITEMENT
        // =========================
        $this->process_webhook($payload);

    } catch (Throwable $e) {

        log_message(
            'error',
            'Webhook fatal error: ' . $e->getMessage()
        );
    }
}

    private function process_webhook($payload) {

        // --------------------------------------------------
        // STRUCTURE RÉELLE DU PAYLOAD WHAPI
        // {
        //   "id": "...",
        //   "type": "text",        ← type du message
        //   "from": "2571234@c.us", ← expéditeur (string, pas objet)
        //   "fromMe": false,        ← TRUE si c'est le bot lui-même
        //   "chatId": "12345@g.us", ← id du groupe
        //   "chatName": "Mon groupe",
        //   "pushName": "Jean",
        //   "text": {"body": "Bonjour"},
        //   "image": {"link": "...", "caption": "..."},
        //   "video": {...},
        //   "audio": {...},
        //   "document": {"link": "...", "filename": "..."},
        //   "sticker": {...}
        // }
        // --------------------------------------------------

        // CORRECTION BUG 1 — ignorer les messages du bot lui-même
        if (!empty($payload['fromMe'])) {
            log_message('debug', 'Webhook: message fromMe ignore');
            return;
        }

        // Extraire les champs selon la vraie structure Whapi
        $message_id   = $payload['id']       ?? null;
        $message_type = $payload['type']      ?? 'unknown';
        $sender       = $payload['from']      ?? null;   // string directement
        $sender_name  = $payload['pushName']  ?? $payload['chatName'] ?? 'Inconnu';
        $chat_id      = $payload['chatId']    ?? $sender; // id du groupe ou privé
        $is_group     = !empty($chat_id) && strpos($chat_id, '@g.us') !== false;

        if (!$sender) {
            log_message('info', 'Webhook: sender manquant ignore');
            return;
        }

        // Extraire le texte selon le type
        $message_text = '';
        switch ($message_type) {
            case 'text':
                $message_text = $payload['text']['body'] ?? '';
                break;
            case 'image':
            case 'video':
            case 'document':
                $message_text = $payload[$message_type]['caption'] ?? '';
                break;
        }

        // Détecter les médias
        $has_media = in_array($message_type, ['image', 'video', 'audio', 'document', 'sticker']);
        $media_url  = null;

        if ($has_media) {
            $media_url = $payload[$message_type]['link']
                      ?? $payload[$message_type]['url']
                      ?? null;
        }

        // Vérifier si l'expéditeur est bloqué
        $is_blocked = $this->db
            ->where('phone', $sender)
            ->where('is_blocked', 1)
            ->count_all_results('whatsapp_participants') > 0;

        if ($is_blocked) {
            log_message('info', 'Webhook: message ignore - utilisateur bloque: ' . $sender);
            return;
        }

        // Détecter si admin
        $admin_numbers = json_decode(
            $this->whapi_library->get_setting('admin_numbers') ?? '[]', true
        ) ?: [];
        $sender_clean = preg_replace('/[^0-9]/', '', $sender);
        $is_admin     = false;
        foreach ($admin_numbers as $admin) {
            if (preg_replace('/[^0-9]/', '', $admin) === $sender_clean) {
                $is_admin = true;
                break;
            }
        }

        // Enregistrer le groupe et participant si message de groupe
        if ($is_group && $chat_id) {
            $this->_upsert_group($chat_id, $payload['chatName'] ?? null);
            $this->_upsert_participant($chat_id, $sender, $sender_name);
        }

        // --------------------------------------------------
        // RÈGLES MEMBRES — texte uniquement, sans liens
        // CORRECTION BUG 2 — suppression effective du message
        // --------------------------------------------------
        if (!$is_admin) {
            $violation = false;
            $reason    = '';

            if ($has_media) {
                $violation = true;
                $reason    = 'media_non_autorise';
            } elseif (contains_link($message_text)) {
                $violation = true;
                $reason    = 'lien_non_autorise';
            }

            if ($violation) {
                // Supprimer le message du groupe via API Whapi
                if ($is_group && $message_id) {
                    $this->whapi_library->delete_message($message_id, $chat_id);
                }

                // Enregistrer la violation
                $this->db->insert('whatsapp_security_logs', [
                    'group_id'    => $chat_id,
                    'sender'      => $sender,
                    'action_type' => $reason,
                    'reason'      => 'Message supprime automatiquement',
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);

                // Incrémenter le compteur de violations
                $this->_increment_violation($sender);

                log_message('info', "Webhook: violation $reason de $sender dans $chat_id");
                return;
            }

            // Texte valide d'un membre — on le laisse passer
            log_message('debug', "Webhook: message membre autorise de $sender");
            return;
        }

        // --------------------------------------------------
        // ADMIN — diffusion selon commande
        // --------------------------------------------------
        $target_type = 'both';

        if (strpos($message_text, '#groupe') === 0) {
            $target_type  = 'group';
            $message_text = trim(substr($message_text, 7));
        } elseif (strpos($message_text, '#inbox') === 0) {
            $target_type  = 'inbox';
            $message_text = trim(substr($message_text, 6));
        } elseif (strpos($message_text, '#template:') === 0) {
            preg_match('/#template:([a-zA-Z0-9_]+)/', $message_text, $matches);
            $tpl_name = $matches[1] ?? null;
            if ($tpl_name) {
                $tpl = $this->db->get_where('whatsapp_templates', ['name' => $tpl_name])->row();
                if ($tpl) $message_text = $tpl->content;
            }
            $message_text = preg_replace('/#template:[a-zA-Z0-9_]+\s*/', '', $message_text);
        }

        $message_text = sanitize_message($message_text);

        $message_data = [
            'type'       => $message_type,
            'text'       => $message_text,
            'group_id'   => $chat_id,
            'sender'     => $sender,
            'message_id' => $message_id,
            'target_type'=> $target_type,
            'has_media'  => $has_media,
            'media_url'  => $media_url,
            'media_type' => $message_type,
        ];

        log_whatsapp(null, null, $sender, $message_text, $message_type, 'received');

        $this->whapi_library->distribute_message($message_data, $sender);

        log_message('info', "Webhook: broadcast admin $sender -> $target_type");
    }

    // --------------------------------------------------
    // MÉTHODES UTILITAIRES PRIVÉES
    // --------------------------------------------------

    private function _upsert_group($groupe_id, $nom = null) {
        $exists = $this->db->get_where('groupes_whatsapp', ['groupe_id' => $groupe_id])->row();
        if ($exists) {
            $data = ['updated_at' => date('Y-m-d H:i:s')];
            if ($nom) $data['nom'] = $nom;
            $this->db->where('id', $exists->id)->update('groupes_whatsapp', $data);
        } else {
            $this->db->insert('groupes_whatsapp', [
                'groupe_id'  => $groupe_id,
                'nom'        => $nom,
                'actif'      => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _upsert_participant($groupe_id, $phone, $name = null) {
        $exists = $this->db
            ->where('groupe_id', $groupe_id)
            ->where('phone', $phone)
            ->get('whatsapp_participants')->row();

        if ($exists) {
            $this->db->where('id', $exists->id)
                     ->update('whatsapp_participants', ['synced_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->db->insert('whatsapp_participants', [
                'groupe_id'       => $groupe_id,
                'phone'           => $phone,
                'phone_formatted' => $phone,
                'is_blocked'      => 0,
                'synced_at'       => date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _increment_violation($phone) {
        $row = $this->db
            ->where('phone', $phone)
            ->limit(1)
            ->get('whatsapp_participants')->row();

        if (!$row) return;

        $count  = (int)($row->violation_count ?? 0) + 1;
        $update = ['violation_count' => $count];
        if ($count >= 3) $update['is_blocked'] = 1;

        $this->db->where('phone', $phone)->update('whatsapp_participants', $update);

        if ($count >= 3) {
            log_message('info', 'Webhook: utilisateur bloque apres 3 violations: ' . $phone);
        }
    }
}