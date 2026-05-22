<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class Whapi_library {

    private $CI;
    private $client;
    private $api_url      = 'https://gate.whapi.cloud/';
    private $api_token    = null;
    private $webhook_token;
    private $master_group_id;
    private $rate_limit   = 30;
    private $delay_min    = 5;
    private $delay_max    = 15;
    private $max_retries  = 3;
    private $cooldown     = 300;
    private $settings_cache = [];

    // ==============================================
    // CONSTRUCTEUR & INITIALISATION
    // ==============================================

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->helper('whapi');
        $this->load_settings();
        $this->init_client();
    }

    private function load_settings() {
        try {
            $settings = $this->CI->db->get('whatsapp_settings')->result_array();
            foreach ($settings as $row) {
                $key   = $row['setting_key'];
                $value = $row['setting_value'];
                $this->settings_cache[$key] = $value;

                // Le token API est stocké en plain text (pas JSON) dans la BDD
                if ($key === 'api_token') {
                    // Essayer d'abord JSON, sinon plain text
                    $decoded = json_decode($value, true);
                    $this->api_token = isset($decoded['token']) ? $decoded['token'] : $value;
                } elseif (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Whapi load_settings() failed: ' . $e->getMessage());
        }
    }

    private function init_client() {
        if (empty($this->api_token)) {
            log_message('error', 'Whapi: api_token manquant, client non initialisé');
            return;
        }
        $this->client = new Client([
            'base_uri'        => $this->api_url,
            'timeout'         => 30,
            'connect_timeout' => 10,
            'verify'          => false,
            'headers'         => [
                'Authorization' => 'Bearer ' . $this->api_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    // ==============================================
    // SETTINGS
    // ==============================================

    public function get_setting($key) {
        // Lire depuis le cache d'abord
        if (isset($this->settings_cache[$key])) {
            return $this->settings_cache[$key];
        }
        try {
            $this->CI->db->where('setting_key', $key);
            $result = $this->CI->db->get('whatsapp_settings')->row();
            if ($result) {
                $this->settings_cache[$key] = $result->setting_value;
                return $result->setting_value;
            }
        } catch (Exception $e) {
            log_message('error', "Whapi get_setting($key) failed: " . $e->getMessage());
        }
        return null;
    }

    public function update_setting($key, $value) {
        try {
            $this->CI->db->where('setting_key', $key);
            $exists = $this->CI->db->get('whatsapp_settings')->row();
            if ($exists) {
                $this->CI->db->where('setting_key', $key);
                $ok = $this->CI->db->update('whatsapp_settings', ['setting_value' => $value]);
            } else {
                $ok = $this->CI->db->insert('whatsapp_settings', [
                    'setting_key'   => $key,
                    'setting_value' => $value,
                ]);
            }
            // Mettre à jour le cache
            $this->settings_cache[$key] = $value;
            return $ok;
        } catch (Exception $e) {
            log_message('error', "Whapi update_setting($key) failed: " . $e->getMessage());
            return false;
        }
    }

    // ==============================================
    // REQUÊTES API (Guzzle)
    // ==============================================

    private function api_request($endpoint, $method = 'GET', $data = null) {
        if (empty($this->api_token)) {
            throw new Exception("API token non configuré");
        }
        if (!$this->client) {
            $this->init_client();
            if (!$this->client) {
                throw new Exception("Client Guzzle non initialisé");
            }
        }

        $options = ['http_errors' => false];

        if ($method === 'POST' && $data !== null) {
            $options['json'] = $data;
        }

        try {
            $response  = $this->client->request($method, $endpoint, $options);
            $status    = $response->getStatusCode();
            $body      = $response->getBody()->getContents();
            $decoded   = json_decode($body, true);

            if ($status >= 400) {
                $errorMsg = isset($decoded['message']) ? $decoded['message']
                    : (isset($decoded['error']) ? $decoded['error'] : "HTTP $status");
                throw new Exception($errorMsg);
            }

            return $decoded;

        } catch (ConnectException $e) {
            throw new Exception("Erreur réseau: " . $e->getMessage());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body     = $e->getResponse()->getBody()->getContents();
                $error    = json_decode($body, true);
                $errorMsg = $error['message'] ?? $error['error'] ?? $e->getMessage();
                throw new Exception($errorMsg);
            }
            throw new Exception($e->getMessage());
        }
    }

    // ==============================================
    // ENVOI DE MESSAGES
    // ==============================================

    public function send_text($to, $text) {
        if (empty($to) || empty($text)) {
            return ['success' => false, 'error' => 'Destinataire ou texte vide'];
        }
        $data = [
            'to'   => format_phone($to),
            'body' => sanitize_message($text),
        ];
        try {
            $result = $this->api_request('messages/text', 'POST', $data);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            log_message('error', "send_text($to) failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function send_media($to, $type, $media_url, $caption = null) {
        if (empty($to) || empty($media_url)) {
            return ['success' => false, 'error' => 'Destinataire ou URL média vide'];
        }
        $allowed_types = ['image', 'video', 'audio', 'document', 'sticker'];
        if (!in_array($type, $allowed_types, true)) {
            return ['success' => false, 'error' => "Type de média invalide: $type"];
        }
        $data = [
            'to'    => format_phone($to),
            'media' => $media_url,
        ];
        if ($caption) {
            $data['caption'] = sanitize_message($caption);
        }
        try {
            $result = $this->api_request("messages/$type", 'POST', $data);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            log_message('error', "send_media($to, $type) failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete_message($message_id) {
        if (empty($message_id)) {
            return ['success' => false, 'error' => 'message_id vide'];
        }
        try {
            $result = $this->api_request("messages/$message_id", 'DELETE');
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            log_message('error', "delete_message($message_id) failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function get_groups() {
        try {
            $result = $this->api_request('groups');
            // Normaliser: retourner toujours un tableau
            $groups = $result['groups'] ?? $result ?? [];
            return ['success' => true, 'data' => $groups];
        } catch (Exception $e) {
            log_message('error', 'get_groups() failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function get_group_participants($group_id) {
        if (empty($group_id)) {
            return ['success' => false, 'error' => 'group_id vide'];
        }
        try {
            $result = $this->api_request("groups/$group_id/participants");
            $participants = $result['participants'] ?? $result ?? [];
            return ['success' => true, 'data' => $participants];
        } catch (Exception $e) {
            log_message('error', "get_group_participants($group_id) failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==============================================
    // GESTION DE LA FILE D'ATTENTE
    // ==============================================

    public function enqueue($data) {
        try {
            $queue_data = [
                'target_type'  => $data['target_type']  ?? 'both',
                'target_id'    => $data['target_id']    ?? null,
                'phone_number' => isset($data['phone_number']) ? format_phone($data['phone_number']) : null,
                'message_type' => $data['message_type'],
                'message_data' => $data['message_data']  ?? null,
                'media_url'    => $data['media_url']     ?? null,
                'priority'     => $data['priority']      ?? 1,
                'scheduled_at' => $data['scheduled_at']  ?? date('Y-m-d H:i:s'),
                'status'       => 'pending',
                'created_at'   => date('Y-m-d H:i:s'),
            ];
            $this->CI->db->insert('whatsapp_queue', $queue_data);
            return $this->CI->db->insert_id();
        } catch (Exception $e) {
            log_message('error', 'enqueue() failed: ' . $e->getMessage());
            return false;
        }
    }

    public function dequeue() {
        if ($this->get_setting('queue_paused') == '1') return null;

        try {
            $this->CI->db->where('status', 'pending');
            $this->CI->db->where('scheduled_at <=', date('Y-m-d H:i:s'));
            $this->CI->db->order_by('priority DESC, created_at ASC');
            $this->CI->db->limit(1);
            $queue = $this->CI->db->get('whatsapp_queue')->row();

            if ($queue) {
                $this->CI->db->where('id', $queue->id);
                $this->CI->db->update('whatsapp_queue', ['status' => 'processing']);
                return $queue;
            }
        } catch (Exception $e) {
            log_message('error', 'dequeue() failed: ' . $e->getMessage());
        }
        return null;
    }

    public function dequeue_batch($batch_size = 10) {
        if ($this->get_setting('queue_paused') == '1') return [];

        try {
            $this->CI->db->where('status', 'pending');
            $this->CI->db->where('scheduled_at <=', date('Y-m-d H:i:s'));
            $this->CI->db->order_by('priority DESC, created_at ASC');
            $this->CI->db->limit((int) $batch_size);
            $items = $this->CI->db->get('whatsapp_queue')->result();

            if (!empty($items)) {
                $ids = array_column($items, 'id');
                $this->CI->db->where_in('id', $ids);
                $this->CI->db->update('whatsapp_queue', ['status' => 'processing']);
            }
            return $items ?: [];
        } catch (Exception $e) {
            log_message('error', 'dequeue_batch() failed: ' . $e->getMessage());
            return [];
        }
    }

    public function process_queue_item($queue_item) {
        if (empty($queue_item)) return false;

        try {
            $identifier = $queue_item->phone_number ?? $queue_item->target_id ?? null;
            if (!$this->check_rate_limit($identifier, 'send')) {
                throw new Exception("Rate limit dépassé pour: $identifier");
            }

            $result = false;

            switch ($queue_item->message_type) {
                case 'text':
                    $result = $this->send_text($queue_item->phone_number, $queue_item->message_data);
                    break;

                case 'image':
                case 'video':
                case 'document':
                case 'audio':
                case 'sticker':
                    if (!empty($queue_item->media_url)) {
                        $this->validate_media($queue_item->media_url, $queue_item->message_type);
                    }
                    $result = $this->send_media(
                        $queue_item->phone_number,
                        $queue_item->message_type,
                        $queue_item->media_url,
                        $queue_item->message_data
                    );
                    break;

                default:
                    throw new Exception("Type de message inconnu: {$queue_item->message_type}");
            }

            if ($result && !empty($result['success'])) {
                $this->mark_queue_completed($queue_item->id);
                log_whatsapp(null, null, $queue_item->phone_number, $queue_item->message_data, $queue_item->message_type, 'sent');
                return true;
            }

            throw new Exception($result['error'] ?? 'Erreur inconnue');

        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->mark_queue_failed($queue_item->id, $error);
            log_whatsapp(null, null, $queue_item->phone_number ?? '', $queue_item->message_data ?? '', $queue_item->message_type ?? '', 'failed', $error);
            return false;
        }
    }

    private function check_rate_limit($identifier, $action, $limit = null, $window = 3600) {
        if (empty($identifier)) return true;
        $limit = $limit ?? (int) $this->rate_limit;

        try {
            $this->CI->db->where('sender', $identifier);
            $this->CI->db->where('action', $action);
            $this->CI->db->where('last_attempt >', date('Y-m-d H:i:s', time() - $window));
            $record = $this->CI->db->get('whatsapp_rate_limits')->row();

            if ($record && $record->attempts >= $limit) {
                return false;
            }

            if ($record) {
                $this->CI->db->where('id', $record->id);
                $this->CI->db->set('attempts', 'attempts+1', false);
                $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
                $this->CI->db->update('whatsapp_rate_limits');
            } else {
                $this->CI->db->insert('whatsapp_rate_limits', [
                    'sender'       => $identifier,
                    'action'       => $action,
                    'attempts'     => 1,
                    'last_attempt' => date('Y-m-d H:i:s'),
                ]);
            }
            return true;
        } catch (Exception $e) {
            log_message('error', 'check_rate_limit() failed: ' . $e->getMessage());
            return true; // Laisser passer en cas d'erreur DB
        }
    }

    private function validate_media($media_url, $type) {
        $headers = @get_headers($media_url, 1);
        if (!$headers) {
            throw new Exception("URL média inaccessible: $media_url");
        }

        $max_sizes = [
            'image'    => 16 * 1024 * 1024,
            'video'    => 64 * 1024 * 1024,
            'audio'    => 16 * 1024 * 1024,
            'document' => 100 * 1024 * 1024,
            'sticker'  => 512 * 1024,
        ];

        $size = isset($headers['Content-Length']) ? (int) $headers['Content-Length'] : 0;
        $max  = $max_sizes[$type] ?? 16 * 1024 * 1024;

        if ($size > 0 && $size > $max) {
            throw new Exception("Média trop lourd pour $type (max " . round($max / 1024 / 1024) . " MB)");
        }

        return true;
    }

    private function mark_queue_completed($id) {
        try {
            $this->CI->db->where('id', $id);
            $this->CI->db->update('whatsapp_queue', [
                'status'       => 'completed',
                'processed_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            log_message('error', "mark_queue_completed($id) failed: " . $e->getMessage());
        }
    }

    private function mark_queue_failed($id, $error) {
        try {
            $this->CI->db->where('id', $id);
            $queue = $this->CI->db->get('whatsapp_queue')->row();

            if (!$queue) return;

            $retry_count = (int) ($queue->retry_count ?? 0);
            $max_retries = (int) $this->max_retries;

            if ($retry_count < $max_retries) {
                $backoff = (int) pow(2, $retry_count); // 1, 2, 4 minutes
                $this->CI->db->where('id', $id);
                $this->CI->db->update('whatsapp_queue', [
                    'status'       => 'retry',
                    'retry_count'  => $retry_count + 1,
                    'error_message'=> substr($error, 0, 500),
                    'scheduled_at' => date('Y-m-d H:i:s', strtotime("+{$backoff} minutes")),
                ]);
            } else {
                $this->CI->db->where('id', $id);
                $this->CI->db->update('whatsapp_queue', [
                    'status'        => 'failed',
                    'error_message' => substr($error, 0, 500),
                    'processed_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
            log_message('error', "mark_queue_failed($id) failed: " . $e->getMessage());
        }
    }

    // ==============================================
    // DISTRIBUTION DE MESSAGES
    // ==============================================

    public function distribute_message($message_data, $sender_phone) {
        $target_type    = $message_data['target_type'] ?? 'both';
        $distributed_to = [];

        $is_master_group = ($message_data['group_id'] === $this->master_group_id);
        $is_admin        = $this->is_group_admin($message_data['group_id'], $sender_phone);

        // Contrôles pour les non-admins
        if (!$is_admin) {
            if ($message_data['has_media']) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_media', 'Non-admin a tenté d\'envoyer un média');
                if (!empty($message_data['message_id'])) $this->delete_message($message_data['message_id']);
                return ['error' => 'Les non-admins ne peuvent pas envoyer de médias'];
            }
            if (contains_link($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_link', 'Non-admin a tenté d\'envoyer un lien');
                if (!empty($message_data['message_id'])) $this->delete_message($message_data['message_id']);
                return ['error' => 'Les non-admins ne peuvent pas envoyer de liens'];
            }
            if (contains_mention($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_mention', 'Non-admin a tenté de mentionner quelqu\'un');
                if (!empty($message_data['message_id'])) $this->delete_message($message_data['message_id']);
                return ['error' => 'Les non-admins ne peuvent pas utiliser les mentions'];
            }
            if (contains_phone($message_data['text'])) {
                $this->log_security_event($message_data['group_id'], $sender_phone, 'unauthorized_phone', 'Non-admin a tenté de partager un numéro');
                if (!empty($message_data['message_id'])) $this->delete_message($message_data['message_id']);
                return ['error' => 'Les non-admins ne peuvent pas partager de numéros'];
            }
        }

        // Seul le groupe maître peut déclencher un broadcast
        if (!$is_master_group) {
            return ['success' => true, 'type' => 'group_message', 'action' => 'allowed'];
        }

        // Broadcast vers les groupes
        if (in_array($target_type, ['group', 'both'], true)) {
            try {
                $groups = $this->CI->db->get_where('groupes_whatsapp', ['actif' => 1])->result();
                foreach ($groups as $group) {
                    $this->enqueue([
                        'target_type'  => 'group',
                        'target_id'    => $group->groupe_id,
                        'message_type' => $message_data['type'],
                        'message_data' => $message_data['text'],
                        'media_url'    => $message_data['media_url'] ?? null,
                        'priority'     => 1,
                    ]);
                    $distributed_to[] = "group:{$group->groupe_id}";
                }
            } catch (Exception $e) {
                log_message('error', 'distribute_message (groups) failed: ' . $e->getMessage());
            }
        }

        // Broadcast vers les inboxes
        if (in_array($target_type, ['inbox', 'both'], true)) {
            try {
                $inboxes = $this->CI->db->get_where('whatsapp_inbox', ['is_blacklisted' => 0])->result();
                shuffle($inboxes);
                foreach ($inboxes as $inbox) {
                    $this->enqueue([
                        'target_type'  => 'inbox',
                        'phone_number' => $inbox->phone_number,
                        'message_type' => $message_data['type'],
                        'message_data' => $message_data['text'],
                        'media_url'    => $message_data['media_url'] ?? null,
                        'priority'     => 1,
                    ]);
                    $distributed_to[] = "inbox:{$inbox->phone_number}";
                }
            } catch (Exception $e) {
                log_message('error', 'distribute_message (inboxes) failed: ' . $e->getMessage());
            }
        }

        return ['success' => true, 'distributed_to' => $distributed_to, 'count' => count($distributed_to)];
    }

    private function is_group_admin($group_id, $phone) {
        if (empty($group_id) || empty($phone)) return false;
        try {
            return $this->CI->db
                ->where('groupe_id', $group_id)
                ->where('phone_formatted', format_phone($phone))
                ->where('is_admin', 1)
                ->get('whatsapp_participants')
                ->num_rows() > 0;
        } catch (Exception $e) {
            log_message('error', 'is_group_admin() failed: ' . $e->getMessage());
            return false;
        }
    }

    private function log_security_event($group_id, $sender, $action_type, $reason) {
        try {
            $this->CI->db->insert('whatsapp_security_logs', [
                'group_id'    => $group_id,
                'sender'      => format_phone($sender),
                'action_type' => $action_type,
                'reason'      => $reason,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            log_message('error', 'log_security_event() failed: ' . $e->getMessage());
        }
    }

    // ==============================================
    // SYNCHRONISATION
    // ==============================================

    public function sync_all_groups() {
        $result = $this->get_groups();
        if (!$result['success']) {
            log_message('error', 'sync_all_groups() failed: ' . ($result['error'] ?? 'unknown'));
            return false;
        }

        $groups        = $result['data'];
        $synced_count  = 0;

        foreach ($groups as $group) {
            if (empty($group['id'])) continue;
            try {
                $exists = $this->CI->db->get_where('groupes_whatsapp', ['groupe_id' => $group['id']])->row();
                $group_data = [
                    'groupe_id'  => $group['id'],
                    'nom'        => $group['name'] ?? null,
                    'actif'      => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($exists) {
                    $this->CI->db->where('groupe_id', $group['id'])->update('groupes_whatsapp', $group_data);
                } else {
                    $group_data['created_at'] = date('Y-m-d H:i:s');
                    $this->CI->db->insert('groupes_whatsapp', $group_data);
                }
                $synced_count++;
                $this->sync_group_participants($group['id']);
                usleep(500000);
            } catch (Exception $e) {
                log_message('error', "sync group {$group['id']} failed: " . $e->getMessage());
            }
        }

        $this->update_setting('last_sync', date('Y-m-d H:i:s'));
        return $synced_count;
    }

    public function sync_group_participants($group_id) {
        $result = $this->get_group_participants($group_id);
        if (!$result['success']) return false;

        $participants = $result['data'];
        $synced_count = 0;

        foreach ($participants as $participant) {
            if (empty($participant['phone'])) continue;
            try {
                $p_data = [
                    'groupe_id'      => $group_id,
                    'phone'          => $participant['phone'],
                    'phone_formatted'=> format_phone($participant['phone']),
                    'is_admin'       => in_array('admin', $participant['roles'] ?? [], true) ? 1 : 0,
                    'is_creator'     => in_array('creator', $participant['roles'] ?? [], true) ? 1 : 0,
                    'profile_name'   => $participant['name'] ?? null,
                    'synced_at'      => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ];

                $exists = $this->CI->db->get_where('whatsapp_participants', [
                    'groupe_id'       => $group_id,
                    'phone_formatted' => $p_data['phone_formatted'],
                ])->row();

                if ($exists) {
                    $this->CI->db->where('id', $exists->id)->update('whatsapp_participants', $p_data);
                } else {
                    $p_data['created_at']      = date('Y-m-d H:i:s');
                    $p_data['violation_count'] = 0;
                    $this->CI->db->insert('whatsapp_participants', $p_data);
                }
                $synced_count++;

                // Ajouter à l'inbox si non blacklisté
                $blacklisted = $this->CI->db->get_where('whatsapp_blacklist', ['phone_number' => $p_data['phone_formatted']])->row();
                $in_inbox    = $this->CI->db->get_where('whatsapp_inbox', ['phone_number' => $p_data['phone_formatted']])->row();

                if (!$in_inbox && !$blacklisted) {
                    $this->CI->db->insert('whatsapp_inbox', [
                        'phone_number' => $p_data['phone_formatted'],
                        'full_name'    => $p_data['profile_name'],
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            } catch (Exception $e) {
                log_message('error', "sync participant {$participant['phone']} failed: " . $e->getMessage());
            }
        }

        return $synced_count;
    }

    public function cleanup_deleted_groups() {
        $remote_result = $this->get_groups();
        if (!$remote_result['success']) return false;

        $remote_ids    = array_column($remote_result['data'], 'id');
        $local_groups  = $this->CI->db->get('groupes_whatsapp')->result();
        $deleted_count = 0;

        foreach ($local_groups as $local) {
            if (!in_array($local->groupe_id, $remote_ids, true)) {
                try {
                    $this->CI->db->where('groupe_id', $local->groupe_id)->delete('groupes_whatsapp');
                    $this->CI->db->where('groupe_id', $local->groupe_id)->delete('whatsapp_participants');
                    $deleted_count++;
                } catch (Exception $e) {
                    log_message('error', "cleanup group {$local->groupe_id} failed: " . $e->getMessage());
                }
            }
        }

        return $deleted_count;
    }

    // ==============================================
    // ANTI-BAN
    // ==============================================

    public function send_with_antiban($messages) {
        $sent_count  = 0;
        $batch_count = 0;

        if (empty($messages)) return true;
        shuffle($messages);

        foreach ($messages as $message) {
            smart_delay();

            $result = $this->send_message_item($message);
            if ($result) {
                $sent_count++;
                $batch_count++;
            }

            // Pause longue tous les 25 messages
            if ($batch_count >= 25) {
                sleep(rand(30, 60));
                $batch_count = 0;
            }

            // Cooldown global
            if ($sent_count >= (int) $this->rate_limit) {
                $cooldown = rand((int) $this->cooldown, (int) $this->cooldown * 2);
                sleep($cooldown);
                $sent_count = 0;
            }
        }

        return true;
    }

    private function send_message_item($message_data) {
        try {
            if (($message_data['type'] ?? '') === 'text') {
                $r = $this->send_text($message_data['to'], $message_data['content']);
            } else {
                $r = $this->send_media($message_data['to'], $message_data['type'], $message_data['media_url'], $message_data['caption'] ?? null);
            }
            return !empty($r['success']);
        } catch (Exception $e) {
            log_message('error', 'send_message_item() failed: ' . $e->getMessage());
            return false;
        }
    }
}