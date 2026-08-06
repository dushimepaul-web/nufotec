<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Esg_Sustainability extends Public_Controller{

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page ESG et durabilité (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "ESG et durabilité | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Gouvernance, conformité, transparence financière et positionnement ESG de NUFOTEC-PHYTOMED INDUSTRIES : BPF, certifications ISO et autorisations réglementaires.";

        $this->load->view('Frontend/Esg_Sustainability_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'esg-sustainability') {
        $this->index($slug);
    }
}
