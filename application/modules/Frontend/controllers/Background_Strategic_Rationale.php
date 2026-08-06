<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Background_Strategic_Rationale extends Public_Controller{

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
        $data['site_title']   = "Contexte et justification stratégique | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Contexte et justification stratégique de NUFOTEC-PHYTOMED INDUSTRIES : plateforme agro-biotechnologique et phytopharmaceutique intégrée verticalement, alignée sur les BPF et aux normes ISO.";

        $this->load->view('Frontend/Background_Strategic_Rationale_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     */
    public function page($slug = 'background-strategic-rationale') {
        $this->index($slug);
    }
}
