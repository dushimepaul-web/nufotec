<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('search_model');
    }

    /**
     * Page de recherche complète
     */
    public function index() {
    $term = $this->input->get('q', TRUE);
    $data['title'] = 'Recherche';
    $data['results'] = [];

    if (!empty($term)) {
        $this->load->model('search_model');
        $data['results'] = $this->search_model->search_all($term);
    }
    $data['term'] = $term;

    $this->load->view('search/index', $data);
}

   public function ajax_search() {
    $term = $this->input->get('q', TRUE);
    if (empty($term)) {
        $this->output->set_content_type('application/json')->set_output(json_encode([]));
        return;
    }
    $results = $this->search_model->search_all($term);
    $this->output->set_content_type('application/json')->set_output(json_encode($results));
}
}