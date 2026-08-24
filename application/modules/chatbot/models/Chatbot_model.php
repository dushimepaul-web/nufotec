<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chatbot_model - Conversations, messages, handoff requests
 */
class Chatbot_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ===================== CONVERSATIONS ===================== */

    public function get_or_create_conversation($session_id, $user_name = null, $user_email = null)
    {
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get_where('chatbot_conversations', array(
            'session_id' => $session_id,
            'status !=' => 'closed'
        ))->row_array();

        if ($row) {
            return $row;
        }

        $data = array(
            'session_id' => $session_id,
            'user_name'  => $user_name,
            'user_email' => $user_email,
            'status'     => 'bot',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $this->db->insert('chatbot_conversations', $data);
        $data['id'] = $this->db->insert_id();
        return $data;
    }

    public function get_conversation($id)
    {
        return $this->db->get_where('chatbot_conversations', array('id' => $id))->row_array();
    }

    public function update_conversation_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('chatbot_conversations', array(
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function assign_agent($conversation_id, $agent_id)
    {
        $this->db->where('id', $conversation_id);
        $this->db->update('chatbot_conversations', array(
            'agent_id'   => $agent_id,
            'status'     => 'human',
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function list_conversations($status = null, $limit = 50)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('chatbot_conversations')->result_array();
    }

    /* ======================= MESSAGES ======================= */

    public function add_message($conversation_id, $sender, $message, $meta = null)
    {
        $data = array(
            'conversation_id' => $conversation_id,
            'sender'          => $sender,
            'message'         => $message,
            'meta'            => $meta ? json_encode($meta) : null,
            'created_at'      => date('Y-m-d H:i:s')
        );
        $this->db->insert('chatbot_messages', $data);

        $this->db->where('id', $conversation_id);
        $this->db->update('chatbot_conversations', array('updated_at' => date('Y-m-d H:i:s')));

        return $this->db->insert_id();
    }

    public function get_messages($conversation_id, $limit = 50)
    {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->order_by('id', 'ASC');
        if ($limit) $this->db->limit($limit);
        return $this->db->get('chatbot_messages')->result_array();
    }

    /* =================== HANDOFF REQUESTS =================== */

    public function create_handoff_request($conversation_id, $reason = null)
    {
        $data = array(
            'conversation_id' => $conversation_id,
            'reason'          => $reason,
            'status'          => 'open',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        );
        $this->db->insert('chatbot_handoff_requests', $data);
        return $this->db->insert_id();
    }

    public function list_open_handoffs()
    {
        $this->db->select('chatbot_handoff_requests.*, chatbot_conversations.session_id, chatbot_conversations.user_name, chatbot_conversations.user_email');
        $this->db->join('chatbot_conversations', 'chatbot_conversations.id = chatbot_handoff_requests.conversation_id');
        $this->db->where('chatbot_handoff_requests.status', 'open');
        $this->db->order_by('chatbot_handoff_requests.created_at', 'ASC');
        return $this->db->get('chatbot_handoff_requests')->result_array();
    }

    public function close_handoff($id)
    {
        $this->db->where('id', $id);
        $this->db->update('chatbot_handoff_requests', array(
            'status' => 'closed',
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /* ======================== STATS ========================= */

    public function count_conversations($status = null)
    {
        if ($status) $this->db->where('status', $status);
        return $this->db->count_all_results('chatbot_conversations');
    }

    public function count_messages($conversation_id = null)
    {
        if ($conversation_id) $this->db->where('conversation_id', $conversation_id);
        return $this->db->count_all_results('chatbot_messages');
    }

    /* ======================== PRODUCTS ========================= */

    public function get_active_products($limit = 10)
    {
        $this->db->select('id, title, price, slug, description');
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL', NULL, FALSE);
        $this->db->order_by('in_vedette', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('advertise_product')->result_array();
    }

    /* ======================== DOCUMENTS ========================= */

    public function save_document($conversation_id, $document_type, $file_path, $file_name = null)
    {
        $data = array(
            'conversation_id' => $conversation_id,
            'document_type'   => $document_type,
            'file_path'       => $file_path,
            'file_name'       => $file_name,
            'created_at'      => date('Y-m-d H:i:s')
        );
        $this->db->insert('chatbot_patient_documents', $data);
        return $this->db->insert_id();
    }

    public function get_documents($conversation_id, $document_type = null)
    {
        $this->db->where('conversation_id', $conversation_id);
        if ($document_type) {
            $this->db->where('document_type', $document_type);
        }
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('chatbot_patient_documents')->result_array();
    }

    public function get_medical_documents($conversation_id)
    {
        return $this->get_documents($conversation_id, 'medical');
    }

    public function get_payment_proof($conversation_id)
    {
        $docs = $this->get_documents($conversation_id, 'payment_proof');
        return !empty($docs) ? $docs[0] : null;
    }

    public function has_medical_documents($conversation_id)
    {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->where('document_type', 'medical');
        return $this->db->count_all_results('chatbot_patient_documents') > 0;
    }

    public function has_payment_proof($conversation_id)
    {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->where('document_type', 'payment_proof');
        return $this->db->count_all_results('chatbot_patient_documents') > 0;
    }
}
