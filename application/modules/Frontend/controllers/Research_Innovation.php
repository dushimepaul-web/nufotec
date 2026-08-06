<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Research_Innovation extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Recherche et innovation (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Recherche et Innovation | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Architecture de recherche intégrée de NUFOTEC-PHYTOMED INDUSTRIES : intelligence ethnobotanique, standardisation phytochimique, bioprocédés, recherche préclinique et pipeline de la recherche au marché.";

        $this->load->view('Frontend/Research_Innovation_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'research-innovation') {
        $this->index($slug);
    }
}
