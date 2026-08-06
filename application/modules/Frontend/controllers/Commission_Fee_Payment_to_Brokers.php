<?php
defined('BASEPATH') OR exit('No direct access allowed');

class Commission_Fee_Payment_to_Brokers extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page Paiement des commissions aux courtiers (statique)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $data['current_lang'] = $lang;
        $data['site_title']   = "Paiement des commissions aux courtiers | NUFOTEC-PHYTOMED INDUSTRIES";
        $data['site_description'] = "Politique de paiement des commissions aux courtiers de NUFOTEC-PHYTOMED INDUSTRIES : accord notarié respecté, commission versée uniquement après décaissement, aucune commission initiale.";

        $this->load->view('Frontend/Commission_Fee_Payment_to_Brokers_View', $data);
    }

    /**
     * Alias de route pour compatibilité
     * @param string $slug Slug de la page
     */
    public function page($slug = 'broker-commission') {
        $this->index($slug);
    }
}
