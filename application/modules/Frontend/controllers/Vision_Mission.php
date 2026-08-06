<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vision_Mission extends Public_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('text');
    }

    /**
     * Page Vision, Mission et objectifs (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Vision, mission et objectifs | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Vision, mission, positionnement stratégique et appels à l'action de NUFOTEC-PHYTOMED INDUSTRIES : agriculture biologique, MTCA, nutraceutiques et phytomédicaments standardisés.";

        $this->load->view('Frontend/Vision_Mission_View', $data);
    }
}
