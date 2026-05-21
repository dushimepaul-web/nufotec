<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_whapi extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
        
        if (PHP_SAPI !== 'cli') {
            die('Access denied');
        }
    }
    
    public function process_queue() {
        $processed = 0;
        $failed = 0;
        
        // Traiter par lots de 10 messages maximum
        $batch = $this->whapi_library->dequeue_batch(10);
        
        foreach ($batch as $queue_item) {
            $result = $this->whapi_library->process_queue_item($queue_item);
            
            if ($result) {
                $processed++;
            } else {
                $failed++;
            }
            
            // Délai intelligent entre les messages
            smart_delay();
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Processed: $processed, Failed: $failed\n";
    }
    
    public function retry_failed() {
        $this->db->where('status', 'failed');
        $this->db->where('retry_count <', 3);
        $updated = $this->db->update('whatsapp_queue', ['status' => 'pending']);
        
        echo "[" . date('Y-m-d H:i:s') . "] Retried failed messages: " . ($updated ? $this->db->affected_rows() : 0) . "\n";
    }
    
    public function cleanup_logs() {
        // Supprimer les logs de plus de 30 jours
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-30 days')));
        $deleted_logs = $this->db->delete('whatsapp_logs');
        
        // Supprimer les logs de sécurité de plus de 90 jours
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-90 days')));
        $deleted_security = $this->db->delete('whatsapp_security_logs');
        
        // Supprimer les messages traités de plus de 7 jours
        $this->db->where('status', 'completed');
        $this->db->where('processed_at <', date('Y-m-d H:i:s', strtotime('-7 days')));
        $deleted_queue = $this->db->delete('whatsapp_queue');
        
        echo "[" . date('Y-m-d H:i:s') . "] Cleaned up: logs=$deleted_logs, security=$deleted_security, queue=$deleted_queue\n";
    }
    
    public function monitor_whatsapp() {
        // Vérifier la taille de la queue
        $queue_size = $this->db->where('status', 'pending')->count_all_results('whatsapp_queue');
        
        if ($queue_size > 1000) {
            log_message('error', "Queue size is high: $queue_size");
            // Envoyer une alerte par email
            $this->_send_alert("Queue Alert", "Queue size is $queue_size");
        }
        
        // Vérifier les messages en échec
        $failed_messages = $this->db->where('status', 'failed')
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->count_all_results('whatsapp_queue');
        
        if ($failed_messages > 50) {
            log_message('error', "High failure rate: $failed_messages failed in last hour");
            $this->_send_alert("Failure Alert", "$failed_messages messages failed in last hour");
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Monitoring completed - Queue: $queue_size, Failed (1h): $failed_messages\n";
    }
    
    public function anti_spam_control() {
        $spam_threshold = $this->whapi_library->get_setting('spam_threshold') ?: 100;
        $last_minute = date('Y-m-d H:i:s', strtotime('-1 minute'));
        
        $recent_messages = $this->db->where('sent_at >', $last_minute)
            ->count_all_results('whatsapp_logs');
        
        if ($recent_messages > $spam_threshold) {
            $this->whapi_library->update_setting('queue_paused', '1');
            log_message('error', "Spam detected ($recent_messages msg/min), queue paused");
            
            $this->_send_alert("SPAM DETECTED", "$recent_messages messages per minute - Queue paused for 5 minutes");
            
            // Reprendre après 5 minutes
            sleep(300);
            $this->whapi_library->update_setting('queue_paused', '0');
            log_message('info', "Queue resumed after spam cooldown");
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Anti-spam check completed\n";
    }
    
    public function optimize_database() {
        $tables = ['whatsapp_queue', 'whatsapp_logs', 'whatsapp_security_logs', 'whatsapp_participants'];
        
        foreach ($tables as $table) {
            $this->db->query("OPTIMIZE TABLE $table");
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Database optimization completed\n";
    }
    
    private function _send_alert($subject, $message) {
        $admin_email = $this->whapi_library->get_setting('admin_email');
        if ($admin_email) {
            mail($admin_email, "[WhatsApp System] $subject", $message, "From: system@" . $_SERVER['HTTP_HOST']);
        }
    }
}