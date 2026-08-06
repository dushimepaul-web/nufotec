<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NUFOTEC_PHYTOMED_INDUSTRIES_Facility extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Installation NUFOTEC-PHYTOMED INDUSTRIES (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Installation NUFOTEC-PHYTOMED INDUSTRIES | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Plan d'aménagement des installations de NUFOTEC-PHYTOMED INDUSTRIES : bâtiment industriel de 13 640 m², laboratoires, unités résidentielles et garage.";

        $this->load->view('Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'nufotec-phytomed-industries-facility') {
        $this->index($slug);
    }
}
