<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends MY_Controller {
    
    private $antiban;
    private $whatsapp_whapi;
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Queue_model', 'Group_model', 'Inbox_model']);
        $this->load->library(['WhatsApp_Whapi', 'AntiBan']);
        $this->antiban = new AntiBan();
        $this->whatsapp_whapi = new WhatsApp_Whapi();
    }
    
    /**
     * Traite les messages de la queue principale (groupes)
     */
    public function process_queue() {
        // Sécurité: exécution seulement en CLI ou avec token
        if (php_sapi_name() !== 'cli' && $this->input->get('token') !== 'YOUR_SECRET_TOKEN') {
            show_404();
            return;
        }
        
        set_time_limit(0);
        
        $config = $this->config->item('antiban');
        $batch_size = $config['batch_size'] ?? 5;
        
        $messages = $this->Queue_model->get_pending_messages($batch_size);
        
        foreach ($messages as $index => $message) {
            $this->process_single_message($message);
            $this->antiban->batch_delay($index + 1);
        }
        
        echo "Queue processed: " . count($messages) . " messages\n";
    }
    
    /**
     * Traite un message individuel
     */
    private function process_single_message($message) {
        // Vérifier les tentatives
        if ($message->retries >= $message->max_retries) {
            $this->Queue_model->update_status($message->id, 'failed', 'Max retries exceeded');
            return;
        }
        
        // Vérifier rate limiting
        if (!$this->antiban->can_send($message->sender_number)) {
            $this->Queue_model->update_status($message->id, 'pending', 'Rate limit exceeded');
            return;
        }
        
        $this->Queue_model->update_status($message->id, 'processing');
        
        // Récupérer tous les groupes
        $groups = $this->Group_model->get_active_groups();
        $all_success = true;
        
        foreach ($groups as $group) {
            // Simuler comportement humain
            $is_media = ($message->media_type != 'text');
            $this->antiban->smart_delay($is_media);
            
            // Envoyer le message selon le type
            $result = $this->send_message_by_type($group->groupe_id, $message);
            
            if ($result['success']) {
                $this->Queue_model->log_broadcast($message->id, 'group', $group->groupe_id, 'sent');
                $this->Queue_model->increment_sent_count($message->id);
            } else {
                $all_success = false;
                $this->Queue_model->log_broadcast($message->id, 'group', $group->groupe_id, 'failed', $result['error'] ?? 'Unknown error');
            }
        }
        
        // Mettre à jour le statut final
        $status = $all_success ? 'sent' : 'pending';
        $error = $all_success ? null : 'Partial failure - will retry';
        $this->Queue_model->update_status($message->id, $status, $error);
    }
    
    /**
     * Envoie un message selon son type
     */
    private function send_message_by_type($to, $message) {
        switch($message->media_type) {
            case 'text':
                return $this->whatsapp_whapi->send_text($to, $message->message);
            case 'image':
                return $this->whatsapp_whapi->send_image($to, $message->media_url, $message->media_caption);
            case 'video':
                return $this->whatsapp_whapi->send_video($to, $message->media_url, $message->media_caption);
            case 'audio':
                return $this->whatsapp_whapi->send_audio($to, $message->media_url);
            case 'document':
                return $this->whatsapp_whapi->send_document($to, $message->media_url, $message->media_filename, $message->media_caption);
            case 'sticker':
                return $this->whatsapp_whapi->send_sticker($to, $message->media_url);
            default:
                return $this->whatsapp_whapi->send_text($to, $message->message);
        }
    }
    
    /**
     * Traite les messages inbox (messages privés)
     */
    public function process_inbox() {
        // Sécurité: exécution seulement en CLI ou avec token
        if (php_sapi_name() !== 'cli' && $this->input->get('token') !== 'YOUR_SECRET_TOKEN') {
            show_404();
            return;
        }
        
        set_time_limit(0);
        
        $inbox_messages = $this->Inbox_model->get_pending_inbox_messages(20);
        
        foreach ($inbox_messages as $inbox) {
            if ($inbox->retries >= 3) {
                $this->Inbox_model->update_status($inbox->id, 'failed', 'Max retries exceeded');
                continue;
            }
            
            // Pause anti-ban (plus longue pour inbox)
            $this->antiban->smart_delay(true);
            
            // Envoyer le message privé
            $result = $this->send_message_by_type($inbox->participant_phone, $inbox);
            
            if ($result['success']) {
                $this->Inbox_model->update_status($inbox->id, 'sent');
                $this->Queue_model->log_broadcast($inbox->queue_id, 'inbox', $inbox->participant_phone, 'sent');
                $this->Queue_model->increment_sent_count($inbox->queue_id);
            } else {
                $this->Inbox_model->update_status($inbox->id, 'pending', $result['error'] ?? 'Unknown error');
            }
            
            // Pause entre chaque message
            sleep(rand(1, 3));
        }
        
        echo "Inbox processed: " . count($inbox_messages) . " messages\n";
    }
}
