<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MX_Controller {
    
    private $is_admin = false;
    
    public function __construct() {
        parent::__construct();
        
        // Vérification admin
        $this->load->library('session');
        $this->load->model('chatbot_model');
        $this->load->config('chatbot/config', TRUE);
        
        // Vérifier la session
        if (!$this->session->userdata('admin_logged')) {
            redirect('chatbot/admin/login');
        }
        
        $this->is_admin = true;
    }
    
    public function index() {
        $data['stats'] = $this->chatbot_model->getStats();
        $data['recent_users'] = $this->chatbot_model->getRecentUsers(10);
        
        $this->load->view('admin/header');
        $this->load->view('admin/dashboard', $data);
        $this->load->view('admin/footer');
    }
    
    public function commands() {
        if ($this->input->post()) {
            $data = [
                'command' => $this->input->post('command'),
                'response' => $this->input->post('response'),
                'type' => $this->input->post('type'),
                'media_url' => $this->input->post('media_url'),
                'is_active' => $this->input->post('is_active') ? 1 : 0
            ];
            
            if ($this->input->post('id')) {
                $data['id'] = $this->input->post('id');
            }
            
            $this->chatbot_model->saveCommand($data);
            $this->session->set_flashdata('success', 'Commande sauvegardée');
            redirect('chatbot/admin/commands');
        }
        
        $data['commands'] = $this->chatbot_model->getAllCommands();
        
        $this->load->view('admin/header');
        $this->load->view('admin/commands', $data);
        $this->load->view('admin/footer');
    }
    
    public function delete_command($id) {
        $this->chatbot_model->deleteCommand($id);
        $this->session->set_flashdata('success', 'Commande supprimée');
        redirect('chatbot/admin/commands');
    }
    
    public function users() {
        $data['users'] = $this->chatbot_model->getRecentUsers(50);
        
        $this->load->view('admin/header');
        $this->load->view('admin/users', $data);
        $this->load->view('admin/footer');
    }
    
    public function send() {
        if ($this->input->post()) {
            $this->load->library('whapi_client');
            $whapi = new Whapi_client();
            
            $to = $this->input->post('to') . '@s.whatsapp.net';
            $message = $this->input->post('message');
            $type = $this->input->post('type');
            
            switch ($type) {
                case 'image':
                    $result = $whapi->sendImage($to, $this->input->post('media'), $message);
                    break;
                case 'video':
                    $result = $whapi->sendVideo($to, $this->input->post('media'), $message);
                    break;
                case 'document':
                    $result = $whapi->sendDocument($to, $this->input->post('media'), $message);
                    break;
                default:
                    $result = $whapi->sendText($to, $message);
            }
            
            if ($result) {
                $this->session->set_flashdata('success', 'Message envoyé avec succès');
            } else {
                $this->session->set_flashdata('error', 'Erreur lors de l\'envoi');
            }
            redirect('chatbot/admin/send');
        }
        
        $this->load->view('admin/header');
        $this->load->view('admin/send');
        $this->load->view('admin/footer');
    }
    
    public function login() {
        if ($this->input->post()) {
            $password = $this->input->post('password');
            $config = $this->config->item('chatbot/config');
            
            if ($password === 'admin123') {
                $this->session->set_userdata('admin_logged', true);
                redirect('chatbot/admin');
            } else {
                $data['error'] = 'Mot de passe incorrect';
            }
        }
        
        $this->load->view('admin/login');
    }
    
    public function logout() {
        $this->session->unset_userdata('admin_logged');
        redirect('chatbot/admin/login');
    }
}