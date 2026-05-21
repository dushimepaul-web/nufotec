<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_whapi extends MY_Controller {
    
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
     * Traite la queue de diffusion vers les groupes
     */
    public function process_queue() {
        // Sécurité: CLI uniquement ou token secret
        if (php_sapi_name() !== 'cli' && $this->input->get('token') !== 'nufotecburundi2026') {
            show_404();
            return;
        }
        
        set_time_limit(0);
        
        $config = $this->config->item('antiban');
        $batch_size = $config['batch_size'] ?? 5;
        
        $messages = $this->Queue_model->get_pending_messages_locked($batch_size);
        
        foreach ($messages as $index => $message) {
            $this->process_single_message($message);
            $this->antiban->batch_delay($index + 1);
        }
        
        echo "Queue processed: " . count($messages) . " messages\n";
    }
    
    private function process_single_message($message) {
        if ($message->retries >= $message->max_retries) {
            $this->Queue_model->update_status($message->id, 'failed', 'Max retries exceeded');
            return;
        }
        
        if (!$this->antiban->can_send($message->sender_number)) {
            // Reporter le message
            $new_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $this->db->where('id', $message->id);
            $this->db->update('wa_messages_queue', ['scheduled_at' => $new_time]);
            return;
        }
        
        $this->Queue_model->update_status($message->id, 'processing');
        
        // Récupérer les destinataires (sans exclusion car déjà gérée dans le webhook)
        $recipients = $this->Queue_model->get_recipients_by_target_type($message);
        $all_success = true;
        
        // Envoi vers les groupes
        foreach ($recipients['groups'] as $group) {
            $is_media = ($message->media_type != 'text');
            $this->antiban->smart_delay($is_media);
            
            $result = $this->send_message_by_type($group->groupe_id, $message);
            
            if ($result['success']) {
                $this->Queue_model->increment_sent_count($message->id);
            } else {
                $all_success = false;
                log_message('error', "Échec envoi groupe {$group->groupe_id}: " . ($result['error'] ?? 'Unknown'));
            }
        }
        
        $status = $all_success ? 'sent' : 'pending';
        $error = $all_success ? null : 'Partial failure - will retry';
        $this->Queue_model->update_status($message->id, $status, $error);
    }
    
    private function send_message_by_type($to, $message) {
        $media_url = !empty($message->local_media_path) 
            ? base_url($message->local_media_path) 
            : $message->media_url;
        
        switch($message->media_type) {
            case 'text':
                return $this->whatsapp_whapi->send_text($to, $message->message);
            case 'image':
                return $this->whatsapp_whapi->send_image($to, $media_url, $message->media_caption);
            case 'video':
                return $this->whatsapp_whapi->send_video($to, $media_url, $message->media_caption);
            case 'audio':
                return $this->whatsapp_whapi->send_audio($to, $media_url);
            case 'document':
                return $this->whatsapp_whapi->send_document($to, $media_url, $message->media_filename, $message->media_caption);
            case 'sticker':
                return $this->whatsapp_whapi->send_sticker($to, $media_url);
            default:
                return $this->whatsapp_whapi->send_text($to, $message->message);
        }
    }
    
    /**
     * Traite la queue inbox (messages privés)
     */
    public function process_inbox() {
        if (php_sapi_name() !== 'cli' && $this->input->get('token') !== 'YOUR_SECRET_CRON_TOKEN') {
            show_404();
            return;
        }
        
        set_time_limit(0);
        
        $inbox_messages = $this->Inbox_model->get_pending_inbox_messages_locked(20);
        
        foreach ($inbox_messages as $inbox) {
            if ($inbox->retries >= 3) {
                $this->Inbox_model->update_status($inbox->id, 'failed', 'Max retries exceeded');
                continue;
            }
            
            $this->antiban->smart_delay(true);
            
            $media_url = !empty($inbox->local_media_path) 
                ? base_url($inbox->local_media_path) 
                : $inbox->media_url;
            
            $result = $this->send_inbox_message_by_type($inbox->participant_phone, $inbox, $media_url);
            
            if ($result['success']) {
                $this->Inbox_model->update_status($inbox->id, 'sent');
                $this->Queue_model->increment_sent_count($inbox->queue_id);
            } else {
                $this->Inbox_model->update_status($inbox->id, 'pending', $result['error'] ?? 'Unknown error');
            }
            
            sleep(rand(1, 3));
        }
        
        echo "Inbox processed: " . count($inbox_messages) . " messages\n";
    }
    
    private function send_inbox_message_by_type($to, $inbox, $media_url) {
        switch($inbox->media_type) {
            case 'text':
                return $this->whatsapp_whapi->send_text($to, $inbox->message_content);
            case 'image':
                return $this->whatsapp_whapi->send_image($to, $media_url, $inbox->message_content);
            case 'video':
                return $this->whatsapp_whapi->send_video($to, $media_url, $inbox->message_content);
            case 'audio':
                return $this->whatsapp_whapi->send_audio($to, $media_url);
            case 'document':
                return $this->whatsapp_whapi->send_document($to, $media_url, 'document', $inbox->message_content);
            case 'sticker':
                return $this->whatsapp_whapi->send_sticker($to, $media_url);
            default:
                return $this->whatsapp_whapi->send_text($to, $inbox->message_content);
        }
    }
}