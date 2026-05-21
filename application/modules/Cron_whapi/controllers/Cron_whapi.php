<?php
class Cron_whapi extends MY_Controller {

    public function process_queue() {
        if (php_sapi_name() !== 'cli' && $this->input->get('token') !== 'SECRET_CRON_TOKEN') show_404();

        $this->db->trans_start();
        // Récupère les messages pending avec lock pour éviter les doublons
        $query = $this->db->query("
            SELECT * FROM wa_messages_queue 
            WHERE status = 'pending' AND scheduled_at <= NOW() 
            ORDER BY id ASC 
            LIMIT 5 
            FOR UPDATE SKIP LOCKED
        ");
        $messages = $query->result();
        foreach ($messages as $msg) {
            $this->db->where('id', $msg->id)->update('wa_messages_queue', ['status' => 'processing']);
        }
        $this->db->trans_complete();

        foreach ($messages as $message) {
            $this->process_single_message($message);
            sleep(rand(2,4)); // délai entre les messages
        }
        echo "Queue done: " . count($messages) . "\n";
    }

    private function process_single_message($message) {
        if ($message->retries >= $message->max_retries) {
            $this->Queue_model->update_status($message->id, 'failed', 'Max retries');
            return;
        }
        // Récupérer les cibles selon target_type
        if ($message->target_type == 'groups' || $message->target_type == 'both') {
            $groups = $this->Group_model->get_active_groups();
            foreach ($groups as $group) {
                $this->antiban->smart_delay($message->media_type != 'text');
                $result = $this->send_message_by_type($group->groupe_id, $message);
                // log...
            }
        }
        if ($message->target_type == 'inbox' || $message->target_type == 'both') {
            // traiter inbox séparément (dans process_inbox)
        }
        $this->Queue_model->update_status($message->id, 'sent');
    }

    public function process_inbox() { /* similaire avec FOR UPDATE */ }
}