<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vision_Mission extends Public_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->helper('text');   // ← SOLUTION
    }

    public function index() {
         $data['hero_section'] = $this->get_hero_section();
         $data['appels_action'] = $this->Model->read('appels_action', NULL, 'ordre','ASC'
                );
        $data['statements'] = $this->Model->read('company_statements', null, 'order', 'ASC');
        $this->load->view('Vision_Mission', $data);
    }

     private function get_hero_section()
    {
        $page = $this->Model->readOne('pages', ['slug' => 'vision-mission', 'est_publiee' => 1]);

        if (empty($page)) {
            log_message('debug', 'Page product-categories non trouvée');
            return null;
        }

        $hero = $this->Model->readOne('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1
        ]);

        if (empty($hero)) {
            log_message('debug', 'Section hero non trouvée pour la page ' . $page['id_page']);
            return null;
        }

        if (!empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        return $hero;
    }
}