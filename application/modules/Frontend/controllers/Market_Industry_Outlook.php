<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Market_Industry_Outlook extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Aperçu du marché et du secteur (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Aperçu du marché et du secteur | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Aperçu du marché et du secteur de NUFOTEC-PHYTOMED INDUSTRIES : viabilité du marché domestique, opportunités régionales (COMESA, EAC, SADC) et potentiel d'exportation international.";

        $this->load->view('Frontend/Market_Industry_Outlook_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'market-outlook') {
        $this->index($slug);
    }
}
