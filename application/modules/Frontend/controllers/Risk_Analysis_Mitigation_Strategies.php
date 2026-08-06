<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Risk_Analysis_Mitigation_Strategies extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Analyse des risques et stratégies d'atténuation (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Analyse des risques et stratégies d'atténuation | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Cadre complet de gestion des risques d'entreprise (ERM) de NUFOTEC-PHYTOMED INDUSTRIES : 14 risques identifiés et leurs stratégies d'atténuation.";

        $this->load->view('Frontend/Risk_Analysis_Mitigation_Strategies_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'risk-analysis') {
        $this->index($slug);
    }
}
