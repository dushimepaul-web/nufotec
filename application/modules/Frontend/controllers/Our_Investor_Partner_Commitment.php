<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Our_Investor_Partner_Commitment extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Notre engagement envers les investisseurs partenaires (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Notre engagement envers les investisseurs partenaires | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Engagement de NUFOTEC-PHYTOMED INDUSTRIES envers les investisseurs, donateurs et courtiers : transparence, non-détournement des fonds, gouvernance financière et rapports en temps réel.";

        $this->load->view('Frontend/Our_Investor_Partner_Commitment_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'investor-commitment') {
        $this->index($slug);
    }
}
