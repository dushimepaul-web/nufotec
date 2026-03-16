<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adresses extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        // Récupérer les filtres
        $filters = $this->input->get();
        
        // Construction de la requête avec jointures
        $this->db->select('adresses.*, users.nom as user_nom, users.prenom as user_prenom, users.email as user_email, pays.nom as pays_nom, pays.code_iso as pays_code');
        $this->db->from('adresses');
        $this->db->join('users', 'users.id = adresses.user_id', 'left');
        $this->db->join('pays', 'pays.id = adresses.pays_id', 'left');
        
        // Appliquer les filtres
        $this->applyFilters($filters);
        
        $data['adresses'] = $this->db->order_by('adresses.est_principale', 'DESC')
                                      ->order_by('adresses.created_at', 'DESC')
                                      ->get()->result_array();
        
        $data['users'] = $this->Model->read('users', ['deleted_at' => NULL, 'is_active' => 1], 'nom', 'ASC');
        $data['pays'] = $this->Model->read('pays', null, 'pays');
        $data['filters'] = $filters;
        
        $this->load->view('Adresses_View', $data);
    }

    // Méthode de filtrage avancée
    private function applyFilters($filters)
    {
        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $this->db->where('adresses.user_id', $filters['user_id']);
        }
        
        // Filtre par type d'adresse
        if (!empty($filters['type'])) {
            $this->db->where('adresses.type', $filters['type']);
        }
        
        // Filtre par pays
        if (!empty($filters['pays_id'])) {
            $this->db->where('adresses.pays_id', $filters['pays_id']);
        }
        
        // Filtre par adresse principale
        if (isset($filters['est_principale']) && $filters['est_principale'] !== '') {
            $this->db->where('adresses.est_principale', $filters['est_principale']);
        }
        
        // Filtre par recherche texte (nom, adresse, ville, code postal)
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('adresses.nom_complet', $filters['search']);
            $this->db->or_like('adresses.adresse_ligne1', $filters['search']);
            $this->db->or_like('adresses.adresse_ligne2', $filters['search']);
            $this->db->or_like('adresses.ville', $filters['search']);
            $this->db->or_like('adresses.code_postal', $filters['search']);
            $this->db->or_like('adresses.entreprise', $filters['search']);
            $this->db->or_like('users.nom', $filters['search']);
            $this->db->or_like('users.prenom', $filters['search']);
            $this->db->or_like('users.email', $filters['search']);
            $this->db->group_end();
        }
        
        // Filtre par ville
        if (!empty($filters['ville'])) {
            $this->db->like('adresses.ville', $filters['ville']);
        }
        
        // Filtre par code postal
        if (!empty($filters['code_postal'])) {
            $this->db->like('adresses.code_postal', $filters['code_postal']);
        }
    }

    // Export CSV des adresses filtrées
    public function Export()
    {
        $filters = $this->input->get();
        
        $this->db->select('adresses.*, users.nom as user_nom, users.prenom as user_prenom, users.email as user_email, pays.nom as pays_nom');
        $this->db->from('adresses');
        $this->db->join('users', 'users.id = adresses.user_id', 'left');
        $this->db->join('pays', 'pays.id = adresses.pays_id', 'left');
        $this->applyFilters($filters);
        
        $adresses = $this->db->order_by('adresses.created_at', 'DESC')->get()->result_array();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=adresses_export_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Utilisateur', 'Email', 'Nom complet', 'Entreprise', 'Adresse', 'Complément', 'Code Postal', 'Ville', 'Pays', 'Téléphone', 'Type', 'Principale', 'Créé le']);
        
        foreach ($adresses as $a) {
            fputcsv($output, [
                $a['id'],
                ($a['user_nom'] ?? '') . ' ' . ($a['user_prenom'] ?? ''),
                $a['user_email'] ?? '',
                $a['nom_complet'],
                $a['entreprise'] ?? '',
                $a['adresse_ligne1'],
                $a['adresse_ligne2'] ?? '',
                $a['code_postal'],
                $a['ville'],
                $a['pays_nom'] ?? '',
                $a['telephone'],
                $a['type'],
                $a['est_principale'] ? 'Oui' : 'Non',
                $a['created_at']
            ]);
        }
        fclose($output);
        exit;
    }

    function Create(){
        // Validation
        $this->form_validation->set_rules('user_id', 'Utilisateur', 'required|numeric');
        $this->form_validation->set_rules('nom_complet', 'Nom complet', 'required');
        $this->form_validation->set_rules('adresse_ligne1', 'Adresse', 'required');
        $this->form_validation->set_rules('code_postal', 'Code postal', 'required');
        $this->form_validation->set_rules('ville', 'Ville', 'required');
        $this->form_validation->set_rules('pays_id', 'Pays', 'required|numeric');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'required');
        $this->form_validation->set_rules('type', 'Type d\'adresse', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Adresses'));
            return;
        }

        $user_id = $this->input->post('user_id');
        $est_principale = $this->input->post('est_principale') ? 1 : 0;

        // Si cette adresse est définie comme principale, retirer le statut des autres
        if ($est_principale) {
            $this->db->where('user_id', $user_id);
            $this->db->update('adresses', ['est_principale' => 0]);
        }

        $data = array(
            'user_id' => $user_id,
            'nom_complet' => $this->input->post('nom_complet'),
            'entreprise' => $this->input->post('entreprise') ?: NULL,
            'adresse_ligne1' => $this->input->post('adresse_ligne1'),
            'adresse_ligne2' => $this->input->post('adresse_ligne2') ?: NULL,
            'code_postal' => $this->input->post('code_postal'),
            'ville' => $this->input->post('ville'),
            'pays_id' => $this->input->post('pays_id'),
            'telephone' => $this->input->post('telephone'),
            'est_principale' => $est_principale,
            'type' => $this->input->post('type'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->create('adresses', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Adresse créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de l\'adresse.');
        }
        redirect(base_url('Adresses'));
    }

    function Update(){
        $id = $this->input->post('id');
        $adresse = $this->Model->readOne('adresses', ['id' => $id]);
        
        // Validation
        $this->form_validation->set_rules('nom_complet', 'Nom complet', 'required');
        $this->form_validation->set_rules('adresse_ligne1', 'Adresse', 'required');
        $this->form_validation->set_rules('code_postal', 'Code postal', 'required');
        $this->form_validation->set_rules('ville', 'Ville', 'required');
        $this->form_validation->set_rules('pays_id', 'Pays', 'required|numeric');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'required');
        $this->form_validation->set_rules('type', 'Type d\'adresse', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Adresses'));
            return;
        }

        $user_id = $this->input->post('user_id') ?: $adresse['user_id'];
        $est_principale = $this->input->post('est_principale') ? 1 : 0;

        // Si cette adresse est définie comme principale, retirer le statut des autres de cet utilisateur
        if ($est_principale && !$adresse['est_principale']) {
            $this->db->where('user_id', $user_id);
            $this->db->where('id !=', $id);
            $this->db->update('adresses', ['est_principale' => 0]);
        }

        $data = array(
            'user_id' => $user_id,
            'nom_complet' => $this->input->post('nom_complet'),
            'entreprise' => $this->input->post('entreprise') ?: NULL,
            'adresse_ligne1' => $this->input->post('adresse_ligne1'),
            'adresse_ligne2' => $this->input->post('adresse_ligne2') ?: NULL,
            'code_postal' => $this->input->post('code_postal'),
            'ville' => $this->input->post('ville'),
            'pays_id' => $this->input->post('pays_id'),
            'telephone' => $this->input->post('telephone'),
            'est_principale' => $est_principale,
            'type' => $this->input->post('type'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('adresses', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Adresse mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Adresses'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $rsp = $this->Model->delete('adresses', ['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Adresse supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Adresses'));
    }

    function SetPrincipale(){
        $id = $this->input->post('id');
        $adresse = $this->Model->readOne('adresses', ['id' => $id]);
        
        if ($adresse) {
            // Retirer le statut principal des autres adresses de l'utilisateur
            $this->db->where('user_id', $adresse['user_id']);
            $this->db->update('adresses', ['est_principale' => 0]);
            
            // Définir cette adresse comme principale
            $this->Model->update('adresses', ['id' => $id], [
                'est_principale' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->session->set_flashdata('success', 'Adresse définie comme principale.');
        } else {
            $this->session->set_flashdata('error', 'Adresse non trouvée.');
        }
        redirect(base_url('Adresses'));
    }

    // Récupérer les adresses d'un utilisateur (pour AJAX)
    public function GetByUser($user_id)
    {
        $adresses = $this->Model->read('adresses', ['user_id' => $user_id], 'est_principale', 'DESC');
        echo json_encode($adresses);
    }
}