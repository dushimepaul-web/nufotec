<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media extends Public_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Model');
    }

    public function index()
    {
        // Récupérer tous les médias actifs
        $data['medias'] = $this->Model->read('galerie_medias', ['est_actif' => 1], 'created_at', 'DESC');
        $this->load->view('Media_View', $data);
    }
}