<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kb_model - Knowledge Base articles + embeddings
 */
class Kb_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all($only_active = false)
    {
        if ($only_active) $this->db->where('active', 1);
        $this->db->order_by('id', 'DESC');
        return $this->db->get('chatbot_kb')->result_array();
    }

    public function get_active_with_embeddings()
    {
        $this->db->where('active', 1);
        $this->db->where('embedding IS NOT NULL', null, false);
        return $this->db->get('chatbot_kb')->result_array();
    }

    public function get($id)
    {
        return $this->db->get_where('chatbot_kb', array('id' => $id))->row_array();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('chatbot_kb', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('chatbot_kb', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('chatbot_kb');
    }

    public function save_embedding($id, array $embedding)
    {
        $this->db->where('id', $id);
        return $this->db->update('chatbot_kb', array(
            'embedding'  => json_encode($embedding),
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Fallback full-text search when embedding API is unavailable.
     */
    public function fulltext_search($query, $limit = 3)
    {
        $query = $this->db->escape_like_str($query);
        $this->db->select('id, title, content, category, 1 as score');
        $this->db->where('active', 1);
        $this->db->group_start();
        $this->db->like('title', $query);
        $this->db->or_like('content', $query);
        $this->db->group_end();
        $this->db->limit($limit);
        return $this->db->get('chatbot_kb')->result_array();
    }
}
