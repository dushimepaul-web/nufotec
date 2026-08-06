<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Corporate_Structure_Governance extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page statique
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Structure et gouvernance d'entreprise | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Structure administrative, comités de gouvernance, départements et licences réglementaires de NUFOTEC-PHYTOMED INDUSTRIES.";

        $this->load->view('Frontend/Corporate_Structure_Governance_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'corporate-structure-governance') {
        $this->index($slug);
    }
}
