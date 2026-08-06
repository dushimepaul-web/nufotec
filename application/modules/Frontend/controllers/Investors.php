<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investors extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Investors_model');
        $this->load->model('Model');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    /**
     * Affiche le formulaire d'investissement
     */
    public function create() {
        $data['countries'] = $this->Investors_model->get_all_pays();
        $sections = $this->obtenir_sections('formulaire-investisseurs'); 
        $this->load->view('investor_form', $data);
    }
    
    /**
     * Enregistre un nouvel investisseur (AJAX)
     */
    public function store() {
        $this->output->set_content_type('application/json');
        
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|max_length[150]');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required|integer');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[150]');
        $this->form_validation->set_rules('agree_contact', 'Acceptation contact', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'errors' => $this->form_validation->error_array()]);
            return;
        }
        
        // Vérifier si l'email existe déjà
        if ($this->Investors_model->email_exists($this->input->post('email'))) {
            echo json_encode(['success' => false, 'errors' => ['email' => 'Cet email est déjà enregistré.']]);
            return;
        }
        
        $data = [
            'full_name' => $this->input->post('full_name'),
            'organization' => $this->input->post('organization'),
            'position_title' => $this->input->post('position_title'),
            'id_pays' => $this->input->post('id_pays'),
            'email' => $this->input->post('email'),
            'phone' => $this->input->post('phone'),
            'interest_equity' => $this->input->post('interest_equity') ? 1 : 0,
            'interest_debt' => $this->input->post('interest_debt') ? 1 : 0,
            'interest_blended_finance' => $this->input->post('interest_blended_finance') ? 1 : 0,
            'interest_grant' => $this->input->post('interest_grant') ? 1 : 0,
            'interest_strategic_partnership' => $this->input->post('interest_strategic_partnership') ? 1 : 0,
            'interest_technical_collaboration' => $this->input->post('interest_technical_collaboration') ? 1 : 0,
            'interest_offtake_distribution' => $this->input->post('interest_offtake_distribution') ? 1 : 0,
            'interest_other' => $this->input->post('interest_other'),
            'commitment_range' => $this->input->post('commitment_range'),
            'focus_research_lab' => $this->input->post('focus_research_lab') ? 1 : 0,
            'focus_gmp_facility' => $this->input->post('focus_gmp_facility') ? 1 : 0,
            'focus_medicinal_plant' => $this->input->post('focus_medicinal_plant') ? 1 : 0,
            'focus_commercialization' => $this->input->post('focus_commercialization') ? 1 : 0,
            'focus_full_platform' => $this->input->post('focus_full_platform') ? 1 : 0,
            'timeline' => $this->input->post('timeline'),
            'strategic_message' => $this->input->post('strategic_message'),
            'agree_contact' => 1,
            'non_binding_confirmation' => $this->input->post('non_binding_confirmation') ? 1 : 0
        ];
        
        $insert_id = $this->Investors_model->insert_investor($data);
        
        if ($insert_id) {
            // Envoyer email de confirmation (optionnel)
            // $this->_send_confirmation_email($data['email'], $data['full_name']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Votre intérêt a été enregistré avec succès ! Notre équipe vous contactera sous 48h.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
        }
    }
    
     /**
   /**
 * Récupération des sections du CMS
 * @param string $slug Le slug de la page (ex: 'formulaire-investisseurs')
 * @return array|null Les données des sections ou null si page non trouvée
 */
private function obtenir_sections($slug = 'formulaire-investisseurs') {
    
    // Récupérer la page par son slug
    $page = static_pages_one([
        'slug' => $slug,
        'est_publiee' => 1
    ]);

    if (empty($page)) {
        log_message('debug', 'Page "' . $slug . '" non trouvée');
        return null;
    }

    // Récupérer la section hero
    $hero = static_sections_one([
        'id_page'      => $page['id_page'],
        'type_section' => 'hero',
        'est_active'   => 1,
        'deleted_at'   => null
    ]);

    if (!empty($hero) && !empty($hero['options_json'])) {
        $hero['options'] = json_decode($hero['options_json'], true);
    } else {
        $hero = null;
    }

    // Récupérer les sections texte
    $textes = static_sections_where([
        'id_page'      => $page['id_page'],
        'type_section' => 'texte',
        'est_active'   => 1,
        'deleted_at'   => null
    ], 'ordre', 'ASC');

    // S'assurer que $textes est toujours un tableau
    if (empty($textes)) {
        $textes = [];
    }

    // Analyser les options JSON
    foreach ($textes as &$texte) {
        if (!empty($texte['options_json'])) {
            $texte['options'] = json_decode($texte['options_json'], true);
        } else {
            $texte['options'] = [];
        }
    }

    return [
        'page'   => $page,
        'hero'   => $hero,
        'textes' => $textes
    ];
}
}
?>