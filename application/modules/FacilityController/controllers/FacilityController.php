<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FacilityController extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        is_admin();
        
        
    }
    
    // ============================================
    // PAGE PRINCIPALE - ARBORESCENCE COMPLÈTE
    // ============================================
    public function index() {
        $data['title'] = 'Gestion du Plan d\'Aménagement';
        
        // Récupérer toute l'arborescence
        $query = $this->db->query("
            WITH RECURSIVE facility_cte AS (
                SELECT 
                    t.id,
                    t.parent_id,
                    t.node_level,
                    t.node_code,
                    t.node_name,
                    t.node_type,
                    t.storage_type,
                    t.length_m,
                    t.width_m,
                    t.area_m2,
                    t.height_m,
                    t.description,
                    t.notes,
                    t.sort_order,
                    CAST(t.node_name AS CHAR(1000)) AS path,
                    CAST(t.sort_order AS CHAR(100)) AS path_order,
                    1 AS level_num,
                    t.created_at,
                    t.updated_at
                FROM facility_tree t
                WHERE t.parent_id IS NULL
                
                UNION ALL
                
                SELECT 
                    t.id,
                    t.parent_id,
                    t.node_level,
                    t.node_code,
                    t.node_name,
                    t.node_type,
                    t.storage_type,
                    t.length_m,
                    t.width_m,
                    t.area_m2,
                    t.height_m,
                    t.description,
                    t.notes,
                    t.sort_order,
                    CONCAT(cte.path, ' > ', t.node_name),
                    CONCAT(cte.path_order, '.', t.sort_order),
                    cte.level_num + 1,
                    t.created_at,
                    t.updated_at
                FROM facility_tree t
                INNER JOIN facility_cte cte ON t.parent_id = cte.id
            )
            SELECT * FROM facility_cte
            ORDER BY path_order
        ");
        
        $data['facility_tree'] = $query->result();
        
        // Récupérer les parents pour les formulaires
        $data['parents'] = $this->get_parent_options();
        
        // Statistiques globales
        $data['total_industrial'] = $this->db->query("
            SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
            WHERE node_type = 'industrial_space' AND node_level = 3
        ")->row()->total;
        
        $data['total_residential'] = $this->db->query("
            SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
            WHERE node_type = 'residential_space'
        ")->row()->total;
        
        $data['total_garage'] = $this->db->query("
            SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
            WHERE node_type = 'garage_space'
        ")->row()->total;
        
        // Types d'espaces pour les filtres
        $data['node_types'] = $this->db->query("
            SELECT DISTINCT node_type FROM facility_tree WHERE node_type IS NOT NULL
        ")->result();
        
        $this->load->view('index.php', $data);
    }
    
    // ============================================
    // RÉCUPÉRER LES OPTIONS PARENTS
    // ============================================
    private function get_parent_options() {
        $query = $this->db->query("
            WITH RECURSIVE facility_cte AS (
                SELECT 
                    id,
                    parent_id,
                    node_level,
                    node_code,
                    node_name,
                    node_type,
                    CAST(node_name AS CHAR(1000)) AS path,
                    CAST(sort_order AS CHAR(100)) AS path_order,
                    1 AS level_num
                FROM facility_tree
                WHERE parent_id IS NULL
                
                UNION ALL
                
                SELECT 
                    t.id,
                    t.parent_id,
                    t.node_level,
                    t.node_code,
                    t.node_name,
                    t.node_type,
                    CONCAT(cte.path, ' > ', t.node_name),
                    CONCAT(cte.path_order, '.', t.sort_order),
                    cte.level_num + 1
                FROM facility_tree t
                INNER JOIN facility_cte cte ON t.parent_id = cte.id
            )
            SELECT * FROM facility_cte WHERE node_level <= 3
            ORDER BY path_order
        ");
        
        return $query->result();
    }
    
    // ============================================
    // AJOUTER UN ESPACE (AJAX)
    // ============================================
    public function ajax_add() {
        $this->form_validation->set_rules('node_name', 'Nom', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }
        
        $data = [
            'parent_id' => $this->input->post('parent_id') ?: NULL,
            'node_level' => $this->input->post('node_level') ?: 3,
            'node_code' => $this->input->post('node_code'),
            'node_name' => $this->input->post('node_name'),
            'node_type' => $this->input->post('node_type'),
            'storage_type' => $this->input->post('storage_type'),
            'length_m' => $this->input->post('length_m') ?: NULL,
            'width_m' => $this->input->post('width_m') ?: NULL,
            'area_m2' => $this->input->post('area_m2') ?: NULL,
            'height_m' => $this->input->post('height_m') ?: NULL,
            'description' => $this->input->post('description'),
            'notes' => $this->input->post('notes'),
            'sort_order' => $this->input->post('sort_order') ?: 0
        ];
        
        // Calcul automatique de la surface
        if($data['length_m'] && $data['width_m'] && !$data['area_m2']) {
            $data['area_m2'] = $data['length_m'] * $data['width_m'];
        }
        
        if($this->db->insert('facility_tree', $data)) {
            $insert_id = $this->db->insert_id();
            echo json_encode([
                'status' => 'success', 
                'message' => 'Espace ajouté avec succès',
                'id' => $insert_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout']);
        }
    }
    
    // ============================================
    // RÉCUPÉRER UN ESPACE (AJAX)
    // ============================================
    public function ajax_get($id) {
        $space = $this->db->get_where('facility_tree', ['id' => $id])->row();
        
        if($space) {
            echo json_encode(['status' => 'success', 'data' => $space]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Espace non trouvé']);
        }
    }
    
    // ============================================
    // METTRE À JOUR UN ESPACE (AJAX)
    // ============================================
    public function ajax_update() {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('node_name', 'Nom', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }
        
        $data = [
            'parent_id' => $this->input->post('parent_id') ?: NULL,
            'node_code' => $this->input->post('node_code'),
            'node_name' => $this->input->post('node_name'),
            'node_type' => $this->input->post('node_type'),
            'storage_type' => $this->input->post('storage_type'),
            'length_m' => $this->input->post('length_m') ?: NULL,
            'width_m' => $this->input->post('width_m') ?: NULL,
            'area_m2' => $this->input->post('area_m2') ?: NULL,
            'height_m' => $this->input->post('height_m') ?: NULL,
            'description' => $this->input->post('description'),
            'notes' => $this->input->post('notes'),
            'sort_order' => $this->input->post('sort_order') ?: 0
        ];
        
        // Recalculer la surface
        if($data['length_m'] && $data['width_m']) {
            $data['area_m2'] = $data['length_m'] * $data['width_m'];
        }
        
        $this->db->where('id', $id);
        if($this->db->update('facility_tree', $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Espace mis à jour avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    // ============================================
    // SUPPRIMER UN ESPACE (AJAX)
    // ============================================
    public function ajax_delete($id) {
        // Vérifier les enfants
        $children = $this->db->get_where('facility_tree', ['parent_id' => $id])->num_rows();
        
        if($children > 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Impossible de supprimer : cet espace contient des sous-espaces'
            ]);
            return;
        }
        
        if($this->db->delete('facility_tree', ['id' => $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Espace supprimé avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression']);
        }
    }
    
    // ============================================
    // RÉORGANISER L'ORDRE (AJAX)
    // ============================================
    public function ajax_reorder() {
        $items = $this->input->post('items');
        
        if($items) {
            foreach($items as $index => $id) {
                $this->db->where('id', $id);
                $this->db->update('facility_tree', ['sort_order' => $index + 1]);
            }
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    
    // ============================================
    // EXPORTER EN CSV
    // ============================================
    public function export_csv() {
        $this->load->dbutil();
        $this->load->helper('download');
        
        $query = $this->db->query("
            SELECT 
                node_code as Code,
                node_name as Nom,
                node_type as Type,
                storage_type as Stockage,
                length_m as Longueur_m,
                width_m as Largeur_m,
                area_m2 as Surface_m2,
                height_m as Hauteur_m,
                description as Description,
                notes as Notes,
                created_at as Créé_le
            FROM facility_tree
            WHERE node_level >= 2
            ORDER BY node_level, sort_order
        ");
        
        $csv = $this->dbutil->csv_from_result($query);
        force_download('facility_layout_' . date('Y-m-d') . '.csv', $csv);
    }
    
    // ============================================
    // RECHERCHE (AJAX)
    // ============================================
    public function ajax_search() {
        $keyword = $this->input->post('keyword');
        $type = $this->input->post('type');
        
        $this->db->select('*');
        $this->db->from('facility_tree');
        $this->db->where('node_level >=', 2);
        
        if($keyword) {
            $this->db->like('node_name', $keyword);
            $this->db->or_like('node_code', $keyword);
            $this->db->or_like('description', $keyword);
        }
        
        if($type && $type != 'all') {
            $this->db->where('node_type', $type);
        }
        
        $this->db->order_by('node_level, sort_order');
        $results = $this->db->get()->result();
        
        echo json_encode(['status' => 'success', 'data' => $results]);
    }
    
    // ============================================
    // STATISTIQUES (AJAX)
    // ============================================
    public function ajax_stats() {
        $stats = [
            'total_industrial' => $this->db->query("
                SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
                WHERE node_type = 'industrial_space'
            ")->row()->total,
            
            'total_residential' => $this->db->query("
                SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
                WHERE node_type = 'residential_space'
            ")->row()->total,
            
            'total_garage' => $this->db->query("
                SELECT COALESCE(SUM(area_m2), 0) as total FROM facility_tree 
                WHERE node_type = 'garage_space'
            ")->row()->total,
            
            'by_type' => $this->db->query("
                SELECT node_type, COUNT(*) as count, SUM(area_m2) as total_area
                FROM facility_tree
                WHERE node_level = 3
                GROUP BY node_type
            ")->result()
        ];
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
}