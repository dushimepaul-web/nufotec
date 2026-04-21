<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BackendController extends MY_Controller
{
    public $CI;
    public $data = array(); // Changé de protected à public

    public function __construct()
    {
        parent::__construct();

        // Désactiver le profiler en production
        // $this->output->enable_profiler(false);

        // Données supplémentaires pour le backend
        $this->data['sitename'] = 'CodeIgniter-HMVC';
        $this->data['site_title'] = ucfirst('Admin Dashboard');
    }

    /**
     * Rendu d'une page avec le template admin
     */
    protected function render_page($view, $data = array())
    {
        // Fusionner les données si nécessaire
        $this->data = array_merge($this->data, $data);
        
        $this->load->view('templates/header', $this->data);
        $this->load->view('templates/main_header', $this->data);
        $this->load->view('templates/main_sidebar', $this->data);
        $this->load->view($view, $this->data);
        $this->load->view('templates/footer', $this->data);
        $this->load->view('templates/control_sidebar', $this->data);
    }
}