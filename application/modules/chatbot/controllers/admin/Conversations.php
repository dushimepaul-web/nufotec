<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin - Conversation management & human handoff
 */
class Conversations extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_admin();

        $this->load->model('chatbot/Chatbot_model');
    }

    /**
     * List pending/active conversations
     */
    public function index()
    {
        $data['pending']  = $this->Chatbot_model->list_conversations('pending_human');
        $data['active']   = $this->Chatbot_model->list_conversations('human');
        $data['handoffs'] = $this->Chatbot_model->list_open_handoffs();
        $this->load->view('admin/chatbot/conversations_list', $data);
    }

    /**
     * View a conversation + reply form
     */
    public function view($conversation_id)
    {
        $conversation = $this->Chatbot_model->get_conversation($conversation_id);
        if (!$conversation) show_404();

        $data['conversation'] = $conversation;
        $data['messages'] = $this->Chatbot_model->get_messages($conversation_id);
        $this->load->view('admin/chatbot/conversations_view', $data);
    }

    /**
     * Agent takes ownership of conversation
     */
    public function assign($conversation_id)
    {
        $agent_id = $this->session->userdata('user_id') ?: 0;
        $this->Chatbot_model->assign_agent($conversation_id, $agent_id);
        $this->Chatbot_model->add_message($conversation_id, 'system', 'Un agent a rejoint la conversation.');
        redirect('admin/chatbot/conversations/view/' . $conversation_id);
    }

    /**
     * Agent sends a reply to visitor
     */
    public function reply($conversation_id)
    {
        $message = trim((string) $this->input->post('message'));
        if ($message !== '') {
            $this->Chatbot_model->add_message($conversation_id, 'agent', $message);
        }
        redirect('admin/chatbot/conversations/view/' . $conversation_id);
    }

    /**
     * Close a conversation
     */
    public function close($conversation_id)
    {
        $this->Chatbot_model->update_conversation_status($conversation_id, 'closed');
        redirect('admin/chatbot/conversations');
    }
}
