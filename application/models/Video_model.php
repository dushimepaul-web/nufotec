<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video_model extends CI_Model {
    
    private $table = 'galerie_medias';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get active videos
     */
    public function getActiveVideos($limit = 50, $offset = 0)
    {
        $this->db->where('type', 'video');
        $this->db->where('est_actif', 1);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Get video by ID
     */
    public function get($id)
    {
        return $this->db->get_where($this->table, ['id_media' => $id])->row_array();
    }
    
    /**
     * Get video by queue ID
     */
    public function getByQueueId($queue_id)
    {
        return $this->db->get_where($this->table, ['queue_id' => $queue_id])->row_array();
    }
    
    /**
     * Create video
     */
    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update video
     */
    public function update($id, $data)
    {
        $this->db->where('id_media', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Delete video (soft)
     */
    public function delete($id)
    {
        return $this->update($id, [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total' => $this->db->where('type', 'video')->count_all_results($this->table),
            'active' => $this->db->where('type', 'video')->where('est_actif', 1)->count_all_results($this->table),
            'total_duration' => 0,
            'total_size' => 0
        ];
        
        // Total duration
        $this->db->select_sum('duree', 'total');
        $this->db->where('type', 'video');
        $result = $this->db->get($this->table)->row();
        $stats['total_duration'] = $result->total ?? 0;
        
        // Total size
        $this->db->select_sum('taille', 'total');
        $this->db->where('type', 'video');
        $result = $this->db->get($this->table)->row();
        $stats['total_size'] = $result->total ?? 0;
        
        return $stats;
    }
    
    /**
     * Get categories
     */
    public function getCategories()
    {
        $this->db->distinct();
        $this->db->select('categorie');
        $this->db->where('type', 'video');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $this->db->order_by('categorie', 'ASC');
        
        $result = $this->db->get($this->table)->result_array();
        return array_column($result, 'categorie');
    }
}