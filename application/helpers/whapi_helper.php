<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ==============================================
// FORMAT & VALIDATION
// ==============================================

if (!function_exists('format_phone')) {
    /**
     * Normalise un numéro de téléphone au format international (ex: 62XXXXXXXXX)
     */
    function format_phone($phone) {
        if (empty($phone)) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) return '';
        // Supprimer le préfixe 0 local
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        // Ajouter l'indicatif pays si absent
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}

if (!function_exists('contains_link')) {
    /**
     * Détecte si un message contient un lien URL
     */
    function contains_link($message) {
        if (empty($message)) return false;
        $pattern = '/(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?)/i';
        return (bool) preg_match($pattern, $message);
    }
}

if (!function_exists('contains_mention')) {
    /**
     * Détecte les mentions WhatsApp (@nom ou @+2547...)
     */
    function contains_mention($message) {
        if (empty($message)) return false;
        return (bool) preg_match('/@[a-zA-Z0-9_+]+/', $message);
    }
}

if (!function_exists('contains_phone')) {
    /**
     * Détecte si un message contient un numéro de téléphone (10+ chiffres consécutifs)
     */
    function contains_phone($message) {
        if (empty($message)) return false;
        return (bool) preg_match('/[0-9]{10,}/', $message);
    }
}

if (!function_exists('sanitize_message')) {
    /**
     * Nettoie un message en supprimant les balises HTML et caractères dangereux
     */
    function sanitize_message($message) {
        if (empty($message)) return '';
        $message = strip_tags($message);
        // Garder uniquement les caractères imprimables + saut de ligne
        $message = preg_replace('/[^\x20-\x7E\x0A\x0D\xC0-\xFF]/u', '', $message);
        return trim($message);
    }
}

// ==============================================
// DÉLAIS ANTI-BAN
// ==============================================

if (!function_exists('random_delay')) {
    /**
     * Pause aléatoire entre $min et $max secondes
     */
    function random_delay($min = 5, $max = 15) {
        $delay = rand($min, $max);
        usleep($delay * 1000000);
        return $delay;
    }
}

if (!function_exists('smart_delay')) {
    /**
     * Pause intelligente adaptée à l'heure de la journée pour éviter le ban WhatsApp
     */
    function smart_delay() {
        $hour = (int) date('H');

        if ($hour >= 22 || $hour < 6) {
            // Nuit : plus lent (15–30 secondes)
            $delay = rand(15, 30);
        } elseif ($hour >= 12 && $hour <= 14) {
            // Heure de pointe : moyen (8–20 secondes)
            $delay = rand(8, 20);
        } else {
            // Journée normale : standard (5–15 secondes)
            $delay = rand(5, 15);
        }

        // Variation aléatoire ±20%
        $variation = $delay * (rand(-20, 20) / 100);
        $final_delay = max(3, (int) round($delay + $variation));

        usleep($final_delay * 1000000);
        return $final_delay;
    }
}

// ==============================================
// DÉTECTION DE TYPE MEDIA
// ==============================================

if (!function_exists('media_type_detect')) {
    /**
     * Détecte le type de média à partir du chemin de fichier
     */
    function media_type_detect($file_path) {
        if (!file_exists($file_path)) return 'document';

        $mime_map = [
            'image'    => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'video'    => ['video/mp4', 'video/webm', 'video/quicktime'],
            'audio'    => ['audio/mpeg', 'audio/ogg', 'audio/wav'],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
            'sticker'  => ['image/webp'],
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        foreach ($mime_map as $type => $mimes) {
            if (in_array($mime, $mimes, true)) {
                return $type;
            }
        }
        return 'document';
    }
}

// ==============================================
// TEMPLATES
// ==============================================

if (!function_exists('parse_template')) {
    /**
     * Remplace les variables {{key}} dans un template par leurs valeurs
     */
    function parse_template($content, $variables = []) {
        if (empty($content)) return '';
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}

// ==============================================
// LOGGING
// ==============================================

if (!function_exists('log_whatsapp')) {
    /**
     * Enregistre un message WhatsApp dans la table whatsapp_logs
     */
    function log_whatsapp($order_request_id, $product_id, $phone_number, $message_content, $message_type, $status, $error_message = null) {
        try {
            $CI = &get_instance();
            $data = [
                'order_request_id' => $order_request_id,
                'product_id'       => $product_id,
                'phone_number'     => format_phone($phone_number),
                'message_content'  => $message_content,
                'message_type'     => $message_type,
                'status'           => $status,
                'error_message'    => $error_message,
                'sent_at'          => date('Y-m-d H:i:s'),
            ];
            return $CI->db->insert('whatsapp_logs', $data);
        } catch (Exception $e) {
            log_message('error', 'log_whatsapp() failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('log_whatsapp_security')) {
    /**
     * Enregistre un événement de sécurité dans whatsapp_security_logs
     */
    function log_whatsapp_security($group_id, $sender, $action_type, $reason) {
        try {
            $CI = &get_instance();
            return $CI->db->insert('whatsapp_security_logs', [
                'group_id'    => $group_id,
                'sender'      => format_phone($sender),
                'action_type' => $action_type,
                'reason'      => $reason,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            log_message('error', 'log_whatsapp_security() failed: ' . $e->getMessage());
            return false;
        }
    }
}

// ==============================================
// UTILITAIRES
// ==============================================

if (!function_exists('is_admin_number')) {
    /**
     * Vérifie si un numéro de téléphone est un admin global
     */
    function is_admin_number($phone) {
        try {
            $CI = &get_instance();
            $CI->load->library('whapi_library');
            $admins         = $CI->whapi_library->get_setting('admin_numbers');
            $admin_numbers  = json_decode($admins, true) ?: [];
            return in_array(format_phone($phone), $admin_numbers, true);
        } catch (Exception $e) {
            log_message('error', 'is_admin_number() failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('generate_queue_id')) {
    /**
     * Génère un identifiant unique pour la file d'attente
     */
    function generate_queue_id() {
        return uniqid('queue_', true);
    }
}