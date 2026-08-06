<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Strategic_Partnerships extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Partenariats stratégiques (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Partenariats stratégiques | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Réseau de partenariats stratégiques de NUFOTEC-PHYTOMED INDUSTRIES : recherche scientifique, essais cliniques, réglementation, fabrication BPF, tests précliniques et financement.";

        $this->load->view('Frontend/Strategic_Partnerships_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'strategic-partnerships') {
        $this->index($slug);
    }
}
