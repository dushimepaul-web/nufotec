<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class About extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper(['text', 'url']);
    }

    public function presentation($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title'] = "Présentation | NUFOTEC-PHYTOMED INDUSTRIES";

        $this->load->view('includes/frontend/Header', $data);
        $this->load->view('Frontend/Presentation_Detail_View', $data);
        $this->load->view('includes/frontend/Footer', $data);
    }
}
