<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Market_Expansion_Platform extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Plateforme de croissance digitale et d'expansion commerciale (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Plateforme de croissance digitale et d'expansion commerciale | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Plateforme de croissance digitale de NUFOTEC-PHYTOMED INDUSTRIES : HubSpot + ChatGPT IA, 235+ groupes WhatsApp, 211 500+ participants, engagement communautaire et expansion multicanal.";

        $this->load->view('Frontend/Market_Expansion_Platform_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'digital-growth') {
        $this->index($slug);
    }
}
