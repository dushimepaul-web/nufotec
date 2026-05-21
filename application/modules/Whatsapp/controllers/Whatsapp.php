<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Vérifier si l'utilisateur est connecté (session admin)
        //if (!$this->session->userdata('logged_in')) {
     //       redirect('Admin');
     //   }
        
        $this->load->model(['Whatsapp_model', 'Chat_model','Queue_model']);
        $this->load->helper(['form', 'url', 'whatsapp']);
    }
    
    public function index() {
        $data['title'] = 'WhatsApp Bot Whatsapp';
        $data['stats'] = $this->Whatsapp_model->get_stats();
        $data['groups'] = $this->Whatsapp_model->get_all_groups_with_stats();
        $data['recent_messages'] = $this->Chat_model->get_recent_messages(50);
        $data['blocked_users'] = $this->Chat_model->get_blocked_users();
        
        $this->load->view('Whatsapp_view', $data);
    }
    
    public function login() {
        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            
            // Vérifier les identifiants (à personnaliser)
            if ($username == 'admin' && $password == 'votre_mot_de_passe') {
                $this->session->set_userdata('logged_in', true);
                redirect('Whatsapp');
            } else {
                $data['error'] = 'Identifiants incorrects';
            }
        }
        $this->load->view('Whatsapp/login');
    }
    
    public function logout() {
        $this->session->sess_destroy();
        redirect('Whatsapp/login');
    }
    
    // ============================================
    // GESTION DES GROUPES
    // ============================================
    
    public function groups() {
        $data['title'] = 'Gestion des Groupes';
        $data['groups'] = $this->Whatsapp_model->get_all_groups_with_stats();
        $this->load->view('Whatsapp/groups', $data);
    }
    
    public function toggle_group($groupe_id) {
        $actif = $this->input->post('actif');
        $this->Chat_model->toggle_status($groupe_id, $actif);
        echo json_encode(['success' => true]);
    }
    
    public function delete_group($groupe_id) {
        $this->Chat_model->delete_group($groupe_id);
        redirect('Whatsapp/groups');
    }
    
    public function sync_groups() {
        // Synchroniser les groupes depuis Whapi
        $this->load->library('WhatsApp_Whapi');
        $groups = $this->whatsapp_whapi->get_groups();
        
        foreach ($groups as $group) {
            $this->Chat_model->upsert_group($group['id'], $group['name']);
        }
        
        redirect('Whatsapp/groups');
    }
    
    // ============================================
    // GESTION DES PARTICIPANTS
    // ============================================
    
    public function participants($groupe_id = null) {
        $data['title'] = 'Gestion des Participants';
        $data['groups'] = $this->Chat_model->get_active_groups();
        $data['current_group'] = $groupe_id;
        
        if ($groupe_id) {
            $data['participants'] = $this->Chat_model->get_group_participants_with_status($groupe_id);
        } else {
            $data['participants'] = $this->Chat_model->get_all_participants();
        }
        
        $this->load->view('Whatsapp/participants', $data);
    }
    
    public function block_participant($participant_id) {
        $this->Chat_model->block_participant($participant_id);
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function unblock_participant($participant_id) {
        $this->Chat_model->unblock_participant($participant_id);
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function reset_violations($participant_id) {
        $this->Chat_model->reset_violations($participant_id);
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    // ============================================
    // CHAT / MESSAGES
    // ============================================
    
    public function chat($chat_id = null, $type = 'group') {
        $data['title'] = 'Conversations';
        $data['chats'] = $this->Chat_model->get_all_chats();
        
        if ($chat_id) {
            $data['current_chat'] = $chat_id;
            $data['current_type'] = $type;
            $data['messages'] = $this->Chat_model->get_conversation($chat_id, $type);
            
            // Marquer les messages comme lus
            $this->Chat_model->mark_as_read($chat_id, $type);
        }
        
        $data['stats'] = $this->Whatsapp_model->get_stats();
        
        $this->load->view('Whatsapp/chat', $data);
    }
    
    public function send_message() {
        $chat_id = $this->input->post('chat_id');
        $message = $this->input->post('message');
        $type = $this->input->post('type');
        
        $this->load->library('WhatsApp_Whapi');
        
        if ($type == 'group') {
            $result = $this->whatsapp_whapi->send_to_group($chat_id, [
                'type' => 'text',
                'content' => ['body' => $message]
            ]);
        } else {
            $result = $this->whatsapp_whapi->send_text($chat_id, $message);
        }
        
        if ($result['success']) {
            // Sauvegarder dans la base de données
            $this->Chat_model->save_message([
                'chat_id' => $chat_id,
                'chat_type' => $type,
                'message' => $message,
                'direction' => 'outgoing',
                'sender' => 'Admin',
                'status' => 'sent'
            ]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    }
    
    // ============================================
    // STATISTIQUES ET LOGS
    // ============================================
    
    public function stats() {
        $data['title'] = 'Statistiques';
        $data['global_stats'] = $this->Whatsapp_model->get_detailed_stats();
        $data['messages_per_day'] = $this->Whatsapp_model->get_messages_per_day(30);
        $data['violations_per_day'] = $this->Whatsapp_model->get_violations_per_day(30);
        $data['top_groups'] = $this->Whatsapp_model->get_top_groups(10);
        $data['top_violators'] = $this->Whatsapp_model->get_top_violators(10);
        
        $this->load->view('Whatsapp/stats', $data);
    }
    
    public function logs() {
        $data['title'] = 'Logs Système';
        $data['logs'] = $this->Whatsapp_model->get_logs(100);
        $this->load->view('Whatsapp/logs', $data);
    }
    
    public function clear_logs() {
        $this->Whatsapp_model->clear_logs();
        redirect('Whatsapp/logs');
    }
    
    // ============================================
    // QUEUE MANAGEMENT
    // ============================================
    
    public function queue() {
        $data['title'] = 'File d\'attente';
        $data['pending_messages'] = $this->Queue_model->get_pending_messages(100);
        $data['processing_messages'] = $this->Queue_model->get_processing_messages();
        $data['failed_messages'] = $this->Queue_model->get_failed_messages(50);
        
        $this->load->view('Whatsapp/queue', $data);
    }
    
    public function retry_message($queue_id) {
        $this->Queue_model->retry_message($queue_id);
        redirect('Whatsapp/queue');
    }
    
    public function cancel_message($queue_id) {
        $this->Queue_model->cancel_message($queue_id);
        redirect('Whatsapp/queue');
    }
    
    // ============================================
    // BROADCAST
    // ============================================
    
    public function broadcast() {
        $data['title'] = 'Diffusion';
        $data['groups'] = $this->Chat_model->get_active_groups();
        $data['stats'] = $this->Whatsapp_model->get_stats();
        
        $this->load->view('Whatsapp/broadcast', $data);
    }
    
    public function send_broadcast() {
        $target_type = $this->input->post('target_type'); // groups, inbox, both
        $message = $this->input->post('message');
        $media_type = $this->input->post('media_type');
        
        // Gérer l'upload de média
        $media_url = null;
        if ($_FILES && isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
            $upload_path = './uploads/whatsapp_media/';
            $filename = uniqid() . '_' . $_FILES['media']['name'];
            move_uploaded_file($_FILES['media']['tmp_name'], $upload_path . $filename);
            $media_url = base_url('uploads/whatsapp_media/' . $filename);
        }
        
        $queue_data = [
            'message' => $message,
            'sender_number' => $this->session->userdata('admin_number'),
            'sender_name' => 'Admin Whatsapp',
            'is_admin' => 1,
            'target_type' => $target_type,
            'media_type' => $media_type,
            'media_url' => $media_url,
            'total_recipients' => $this->Whatsapp_model->count_recipients($target_type)
        ];
        
        $queue_id = $this->Queue_model->add_to_queue($queue_data);
        
        $this->session->set_flashdata('success', 'Diffusion ajoutée à la file d\'attente (ID: ' . $queue_id . ')');
        redirect('Whatsapp/broadcast');
    }
    
    // ============================================
    // SETTINGS
    // ============================================
    
    public function settings() {
        $data['title'] = 'Paramètres';
        $data['antiban_settings'] = $this->Whatsapp_model->get_antiban_settings();
        $data['admin_numbers'] = $this->config->item('admin_numbers');
        
        $this->load->view('Whatsapp/settings', $data);
    }
    
    public function update_settings() {
        $settings = $this->input->post('settings');
        foreach ($settings as $key => $value) {
            $this->Whatsapp_model->update_setting($key, $value);
        }
        
        $this->session->set_flashdata('success', 'Paramètres mis à jour');
        redirect('Whatsapp/settings');
    }
}