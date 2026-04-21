<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends Public_Controller {
    public function index($slug) {
        // Convertir le slug en nom de contrôleur
        // Ex: 'background-strategic-rationale' → 'Background_Strategic_Rationale'
        $controller_name = str_replace(' ', '_', ucwords(str_replace('-', ' ', $slug)));
        
        $controller_path = 'Frontend/' . $controller_name;
        
        if (module_exists($controller_path)) {
            $this->load->module($controller_path);
            $this->{$controller_name}->index($this->current_lang);
        } else {
            show_404();
        }
    }
}