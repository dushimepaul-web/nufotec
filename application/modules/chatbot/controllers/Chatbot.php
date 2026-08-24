<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chatbot Controller - Public API for the chat widget
 * Reads configuration from the `configurations` table (admin panel).
 */
class Chatbot extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model');
        $this->load->model('chatbot/Chatbot_model');
        $this->load->library('chatbot/Ai_service');
        $this->load->helper(array('url', 'text'));
    }

    /**
     * Widget view (for standalone testing)
     */
    public function index()
    {
        $this->load->view('chatbot/chatbot/Widget_View');
    }

    /**
     * Start or resume a conversation for the current session
     * POST /chatbot/start
     */
    public function start()
    {
        $session_id = $this->_get_or_create_session_id();
        $name  = $this->input->post('name');
        $email = $this->input->post('email');

        $conversation = $this->Chatbot_model->get_or_create_conversation($session_id, $name, $email);
        $messages = $this->Chatbot_model->get_messages($conversation['id']);

        $this->_json(array(
            'success' => true,
            'conversation_id' => $conversation['id'],
            'status' => $conversation['status'],
            'messages' => $messages
        ));
    }

    /**
     * Receive user message, run RAG + AI, return response
     * POST /chatbot/send  { conversation_id, message }
     */
    public function send()
    {
        $conversation_id = (int) $this->input->post('conversation_id');
        $message = trim((string) $this->input->post('message'));

        if (!$conversation_id || $message === '') {
            return $this->_json(array('success' => false, 'error' => 'Requete invalide.'), 400);
        }

        $conversation = $this->Chatbot_model->get_conversation($conversation_id);
        if (!$conversation) {
            return $this->_json(array('success' => false, 'error' => 'Conversation introuvable.'), 404);
        }

        $this->Chatbot_model->add_message($conversation_id, 'user', $message);

        if (in_array($conversation['status'], array('human', 'pending_human'))) {
            return $this->_json(array(
                'success' => true,
                'mode' => $conversation['status'],
                'reply' => null
            ));
        }

        $history = $this->Chatbot_model->get_messages($conversation_id, 20);

        // Check if we are in consultation collection mode
        $consultation_state = $this->session->userdata('consultation_state');
        if ($consultation_state && isset($consultation_state['conversation_id']) && $consultation_state['conversation_id'] == $conversation_id) {
            $result = $this->ai_service->generate_reply($message, $history, $consultation_state);
        } else {
            $result = $this->ai_service->generate_reply($message, $history);
        }

        // Handle consultation state updates
        if (isset($result['start_consultation']) && $result['start_consultation']) {
            $this->session->set_userdata('consultation_state', array(
                'conversation_id' => $conversation_id,
                'step' => $result['consultation_step'],
                'data' => array()
            ));
        } elseif (isset($result['consultation_step']) && $result['consultation_step'] > 0) {
            $state = $this->session->userdata('consultation_state');
            if ($state) {
                if (isset($result['consultation_data'])) {
                    $state['data'] = array_merge($state['data'], $result['consultation_data']);
                }
                $state['step'] = $result['consultation_step'];
                $this->session->set_userdata('consultation_state', $state);
            }
        }

        // Clear consultation state if completed
        if (isset($result['completed']) && $result['completed']) {
            $this->session->unset_userdata('consultation_state');
        }

        $this->Chatbot_model->add_message($conversation_id, 'bot', $result['reply'], array(
            'sources'   => $result['sources'],
            'top_score' => $result['top_score']
        ));

        $response = array(
            'success' => true,
            'mode'    => 'bot',
            'reply'   => $result['reply'],
            'sources' => $result['sources']
        );

        if (isset($result['whatsapp_link'])) {
            $response['whatsapp_link'] = $result['whatsapp_link'];
        }

        if (!empty($result['should_escalate'])) {
            $this->Chatbot_model->update_conversation_status($conversation_id, 'pending_human');
            $this->Chatbot_model->create_handoff_request($conversation_id, 'Escalade automatique (reponse IA insuffisante)');
            $this->_notify_agents($conversation_id);
            $response['escalation_suggested'] = true;
        }

        $this->_json($response);
    }

    /**
     * Explicit human transfer request (button in widget)
     * POST /chatbot/request_human { conversation_id, reason }
     */
    public function request_human()
    {
        $conversation_id = (int) $this->input->post('conversation_id');
        $reason = $this->input->post('reason') ?: 'Demande explicite de l\'utilisateur';

        $conversation = $this->Chatbot_model->get_conversation($conversation_id);
        if (!$conversation) {
            return $this->_json(array('success' => false, 'error' => 'Conversation introuvable.'), 404);
        }

        $this->Chatbot_model->update_conversation_status($conversation_id, 'pending_human');
        $this->Chatbot_model->create_handoff_request($conversation_id, $reason);
        $this->Chatbot_model->add_message($conversation_id, 'system', 'L\'utilisateur a demande a parler a un agent humain.');

        $this->_notify_agents($conversation_id);

        $this->_json(array(
            'success' => true,
            'message' => 'Un agent va bientot prendre en charge votre demande.'
        ));
    }

    /**
     * Get conversation history (polling for agent responses)
     * GET /chatbot/history/{conversation_id}?after_id=123
     */
    public function history($conversation_id)
    {
        $after_id = (int) $this->input->get('after_id');
        $messages = $this->Chatbot_model->get_messages((int) $conversation_id);

        if ($after_id) {
            $messages = array_values(array_filter($messages, function ($m) use ($after_id) {
                return $m['id'] > $after_id;
            }));
        }

        $conversation = $this->Chatbot_model->get_conversation((int) $conversation_id);

        $this->_json(array(
            'success' => true,
            'status' => $conversation ? $conversation['status'] : null,
            'messages' => $messages
        ));
    }

    /* -------------------------------------------------------- */

    /**
     * Upload a document (medical or payment proof)
     * POST /chatbot/upload { conversation_id, document_type }
     */
    public function upload()
    {
        $conversation_id = (int) $this->input->post('conversation_id');
        $document_type = $this->input->post('document_type');

        if (!$conversation_id || !in_array($document_type, array('medical', 'payment_proof'))) {
            return $this->_json(array('success' => false, 'error' => 'Parametres invalides.'), 400);
        }

        $conversation = $this->Chatbot_model->get_conversation($conversation_id);
        if (!$conversation) {
            return $this->_json(array('success' => false, 'error' => 'Conversation introuvable.'), 404);
        }

        // Check if file was uploaded
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            return $this->_json(array('success' => false, 'error' => 'Aucun fichier envoye.'), 400);
        }

        $file = $_FILES['document'];
        
        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf');
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            return $this->_json(array('success' => false, 'error' => 'Type de fichier non autorise.'), 400);
        }

        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return $this->_json(array('success' => false, 'error' => 'Fichier trop volumineux (max 10MB).'), 400);
        }

        // Create upload directory if not exists
        $upload_dir = FCPATH . 'attachments' . DIRECTORY_SEPARATOR . 'Chatbot' . DIRECTORY_SEPARATOR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'doc_' . $conversation_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return $this->_json(array('success' => false, 'error' => 'Erreur lors de l\'enregistrement.'), 500);
        }

        // Save to database
        $relative_path = 'attachments/Chatbot/' . $filename;
        $doc_id = $this->Chatbot_model->save_document($conversation_id, $document_type, $relative_path, $file['name']);

        // Get consultation state and update it
        $consultation_state = $this->session->userdata('consultation_state');
        if ($consultation_state && isset($consultation_state['conversation_id']) && $consultation_state['conversation_id'] == $conversation_id) {
            // Store file path in consultation data
            $field = ($document_type === 'medical') ? 'medical_documents' : 'payment_proof';
            if (!isset($consultation_state['data'][$field])) {
                $consultation_state['data'][$field] = array();
            }
            $consultation_state['data'][$field][] = site_url($relative_path);
            $this->session->set_userdata('consultation_state', $consultation_state);
        }

        $this->_json(array(
            'success' => true,
            'document_id' => $doc_id,
            'file_url' => site_url($relative_path),
            'message' => 'Document enregistre avec succes.'
        ));
    }

    protected function _get_or_create_session_id()
    {
        $sid = $this->session->userdata('chatbot_session_id');
        if (!$sid) {
            $sid = bin2hex(random_bytes(16));
            $this->session->set_userdata('chatbot_session_id', $sid);
        }
        return $sid;
    }

    protected function _notify_agents($conversation_id)
    {
        $to = $this->Model->get_setting('chatbot_handoff_email', '');
        if (!$to) return;

        $site_name = $this->Model->get_setting('chatbot_site_name', 'Chatbot');
        $this->load->library('email');
        $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $site_name);
        $this->email->to($to);
        $this->email->subject('Nouvelle demande de transfert humain - Conversation #' . $conversation_id);
        $this->email->message('Une conversation necessite l\'intervention d\'un agent : conversation #' . $conversation_id . "\n" .
            site_url('admin/chatbot/conversations/view/' . $conversation_id));
        @$this->email->send();
    }

    protected function _json($data, $http_code = 200)
    {
        $this->output
            ->set_status_header($http_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
