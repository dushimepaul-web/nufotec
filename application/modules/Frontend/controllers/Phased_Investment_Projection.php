<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phased_Investment_Projection extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Projection d'investissement par phase (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Projection d'investissement par phase | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Projection d'investissement par phase de NUFOTEC-PHYTOMED INDUSTRIES : capital d'amorçage de plus de 40 millions USD (2026-2029) pour l'installation BPF de phytomédicaments et nutraceutiques.";

        $this->load->view('Frontend/Phased_Investment_Projection_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'investment-projection') {
        $this->index($slug);
    }
}
