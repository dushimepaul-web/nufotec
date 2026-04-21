<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FrontendController extends MY_Controller
{
    public $CI;
    public $data = array(); // Changé de protected à public (comme dans MY_Controller)

    public function __construct()
    {
        parent::__construct();

        // Désactiver le profiler en production
        // $this->output->enable_profiler(false);

        // Données supplémentaires pour le frontend
        $this->data['sitename'] = 'nufotec.com';
        $this->data['site_title'] = ucfirst('Frontend');
    }
}