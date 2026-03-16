<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Twilio\Rest\Client;

/**
 * Envoie un SMS via Twilio
 *
 * @param string $to Numéro du destinataire (format international, ex: +33612345678)
 * @param string $message Corps du message
 * @param array $options Options supplémentaires (media_url pour MMS)
 * @return array ['success' => bool, 'message' => string, 'sid' => string|null]
 */
function send_twilio_sms($to, $message, $options = [])
{
    $CI =& get_instance();
    
    // Récupérer la configuration
    $account_sid = $CI->config->item('twilio_account_sid');
    $auth_token = $CI->config->item('twilio_auth_token');
    $twilio_number = $CI->config->item('twilio_phone_number');
    
    // Vérifier que la configuration est présente
    if (empty($account_sid) || empty($auth_token) || empty($twilio_number)) {
        log_message('error', 'Twilio SMS: Configuration manquante (account_sid, auth_token ou phone_number)');
        return [
            'success' => false,
            'message' => 'Configuration Twilio incomplète'
        ];
    }
    
    try {
        $client = new Client($account_sid, $auth_token);
        
        $params = [
            'from' => $twilio_number,
            'body' => $message
        ];
        
        // Ajouter un média si fourni (pour MMS)
        if (!empty($options['media_url'])) {
            $params['mediaUrl'] = [$options['media_url']];
        }
        
        $twilio_message = $client->messages->create($to, $params);
        
        log_message('debug', "Twilio SMS envoyé avec succès. SID: " . $twilio_message->sid);
        
        return [
            'success' => true,
            'message' => 'SMS envoyé',
            'sid' => $twilio_message->sid,
            'status' => $twilio_message->status
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Twilio SMS Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erreur Twilio: ' . $e->getMessage()
        ];
    }
}

/**
 * Envoie un message WhatsApp via Twilio
 *
 * @param string $to Numéro du destinataire (format international, ex: +33612345678)
 * @param string $message Corps du message
 * @param array $options Options supplémentaires (media_url)
 * @return array ['success' => bool, 'message' => string, 'sid' => string|null]
 */
function send_twilio_whatsapp($to, $message, $options = [])
{
    $CI =& get_instance();
    
    $account_sid = $CI->config->item('twilio_account_sid');
    $auth_token = $CI->config->item('twilio_auth_token');
    $twilio_whatsapp_number = $CI->config->item('twilio_whatsapp_number');
    
    if (empty($account_sid) || empty($auth_token) || empty($twilio_whatsapp_number)) {
        log_message('error', 'Twilio WhatsApp: Configuration manquante');
        return [
            'success' => false,
            'message' => 'Configuration Twilio WhatsApp incomplète'
        ];
    }
    
    try {
        $client = new Client($account_sid, $auth_token);
        
        // Ajouter le préfixe whatsapp:
        $from = 'whatsapp:' . $twilio_whatsapp_number;
        $to_whatsapp = 'whatsapp:' . $to;
        
        $params = [
            'from' => $from,
            'body' => $message
        ];
        
        if (!empty($options['media_url'])) {
            $params['mediaUrl'] = [$options['media_url']];
        }
        
        $twilio_message = $client->messages->create($to_whatsapp, $params);
        
        log_message('debug', "Twilio WhatsApp envoyé avec succès. SID: " . $twilio_message->sid);
        
        return [
            'success' => true,
            'message' => 'Message WhatsApp envoyé',
            'sid' => $twilio_message->sid,
            'status' => $twilio_message->status
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Twilio WhatsApp Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erreur Twilio WhatsApp: ' . $e->getMessage()
        ];
    }
}

/**
 * Envoie un message avec média (image/vidéo) sur SMS (MMS) ou WhatsApp
 *
 * @param string $to
 * @param string $message
 * @param string $media_url URL publique du média
 * @param string $channel 'sms' ou 'whatsapp'
 * @return array
 */
function send_twilio_media($to, $message, $media_url, $channel = 'sms')
{
    $options = ['media_url' => $media_url];
    if ($channel === 'whatsapp') {
        return send_twilio_whatsapp($to, $message, $options);
    } else {
        return send_twilio_sms($to, $message, $options);
    }
}