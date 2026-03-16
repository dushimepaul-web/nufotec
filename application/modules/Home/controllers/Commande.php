<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commande extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form', 'security']);
        $this->load->library(['session', 'form_validation']);
        $this->load->model('Panier_model');
        $this->load->model('Boutique_model');
        $this->load->model('Commande_model');
    }

    /**
     * Page de commande (checkout)
     */
    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Vous devez être connecté pour passer commande.');
            $this->session->set_userdata('login_redirect', current_url());
            redirect('Auth');
        }

        $panier = $this->Panier_model->get_active_cart_by_user($user_id);
        if (!$panier || $panier->nombre_articles == 0) {
            $this->session->set_flashdata('error', 'Votre panier est vide.');
            redirect('boutique');
        }

        $lignes = $this->Panier_model->get_cart_lines($panier->id);
        $total = 0;
        foreach ($lignes as $l) {
            $total += $l->total_ligne_ttc;
        }

        $data = [
            'panier' => $panier,
            'lignes' => $lignes,
            'total' => $total,
            'adresses' => $this->Commande_model->get_user_adresses($user_id),
            'pays' => $this->db->order_by('pays', 'ASC')->get('pays')->result(),
            'modes_paiement' => $this->db->get('mode_payement')->result(),
            'titre' => 'Passer commande - AGF Pharma'
        ];

        $this->load->view('commande_View',$data);
    }

    /**
     * Traitement de la commande
     */
    public function valider()
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            show_404();
        }

        $this->form_validation->set_rules('adresse_option', 'Option d\'adresse', 'required|in_list[existante,nouvelle]');

        $adresse_option = $this->input->post('adresse_option');
        $adresse_livraison_id = null;
        $adresse_facturation_id = null;

        if ($adresse_option === 'nouvelle') {
            // Validation nouvelle adresse
            $this->form_validation->set_rules('nom_complet', 'Nom complet', 'required|min_length[3]|max_length[100]');
            $this->form_validation->set_rules('entreprise', 'Entreprise', 'max_length[100]');
            $this->form_validation->set_rules('tva_intracom', 'TVA', 'max_length[50]|alpha_numeric');
            $this->form_validation->set_rules('adresse_ligne1', 'Adresse', 'required|min_length[5]|max_length[255]');
            $this->form_validation->set_rules('adresse_ligne2', 'Adresse ligne 2', 'max_length[255]');
            $this->form_validation->set_rules('code_postal', 'Code postal', 'required|min_length[3]|max_length[20]');
            $this->form_validation->set_rules('ville', 'Ville', 'required|min_length[2]|max_length[100]');
            $this->form_validation->set_rules('region', 'Région', 'max_length[100]');
            $this->form_validation->set_rules('pays_id', 'Pays', 'required|integer');
            $this->form_validation->set_rules('telephone', 'Téléphone', 'required|min_length[8]|max_length[20]');
            $this->form_validation->set_rules('instructions', 'Instructions', 'max_length[500]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('commande');
                return;
            }

            $pays_id = (int)$this->input->post('pays_id');
            $pays = $this->db->where('id', $pays_id)->get('pays')->row();
            if (!$pays) {
                $this->session->set_flashdata('error', 'Pays invalide.');
                redirect('commande');
                return;
            }

            $adresse_data = [
                'user_id' => $user_id,
                'nom_complet' => $this->security->xss_clean($this->input->post('nom_complet')),
                'entreprise' => $this->security->xss_clean($this->input->post('entreprise')),
                'tva_intracom' => $this->security->xss_clean($this->input->post('tva_intracom')),
                'adresse_ligne1' => $this->security->xss_clean($this->input->post('adresse_ligne1')),
                'adresse_ligne2' => $this->security->xss_clean($this->input->post('adresse_ligne2')),
                'code_postal' => $this->security->xss_clean($this->input->post('code_postal')),
                'ville' => $this->security->xss_clean($this->input->post('ville')),
                'region' => $this->security->xss_clean($this->input->post('region')),
                'pays_id' => $pays_id,
                'telephone' => $this->security->xss_clean($this->input->post('telephone')),
                'instructions' => $this->security->xss_clean($this->input->post('instructions')),
                'est_principale' => $this->input->post('definir_principale') ? 1 : 0,
                'type' => 'livraison'
            ];

            $adresse_id = $this->Commande_model->create_adresse($adresse_data);
            if (!$adresse_id) {
                $this->session->set_flashdata('error', "Erreur lors de l'enregistrement de l'adresse.");
                redirect('commande');
                return;
            }

            $adresse_livraison_id = $adresse_id;
            $adresse_facturation_id = $adresse_id;
        } else {
            // Adresse existante
            $adresse_id = (int)$this->input->post('adresse_id');
            if (!$adresse_id) {
                $this->session->set_flashdata('error', "Veuillez sélectionner une adresse.");
                redirect('commande');
                return;
            }

            $adresse = $this->Commande_model->get_adresse_by_id($adresse_id);
            if (!$adresse || $adresse->user_id != $user_id) {
                $this->session->set_flashdata('error', "Adresse invalide.");
                redirect('commande');
                return;
            }

            $adresse_livraison_id = $adresse_id;
            $adresse_facturation_id = $adresse_id;
        }

        // Validation mode de paiement
        $mode_paiement_id = (int)$this->input->post('mode_paiement');
        $mode_paiement = $this->db->where('id_mode_payement', $mode_paiement_id)->get('mode_payement')->row();
        if (!$mode_paiement) {
            $this->session->set_flashdata('error', "Mode de paiement invalide.");
            redirect('commande');
            return;
        }

        $panier = $this->Panier_model->get_active_cart_by_user($user_id);
        if (!$panier || $panier->nombre_articles == 0) {
            $this->session->set_flashdata('error', 'Votre panier est vide.');
            redirect('boutique');
            return;
        }

        $numero_commande = $this->generate_numero_commande();

        $total_ht = (float)$panier->total_ht;
        $total_ttc = (float)$panier->total_ttc;
        $total_tva = $total_ttc - $total_ht;
        $frais_livraison_ht = 0.00;

        $commande_data = [
            'numero_commande' => $numero_commande,
            'user_id' => $user_id,
            'adresse_livraison_id' => $adresse_livraison_id,
            'adresse_facturation_id' => $adresse_facturation_id,
            'total_ht' => $total_ht,
            'total_tva' => $total_tva,
            'total_ttc' => $total_ttc,
            'frais_livraison_ht' => $frais_livraison_ht,
            'statut' => 'en_attente',
            'mode_paiement' => $mode_paiement->description,
            'statut_paiement' => 'en_attente',
            'mode_livraison' => 'standard',
            'ip_commande' => $this->input->ip_address()
        ];

        $commande_id = $this->Commande_model->create_commande($commande_data);
        if (!$commande_id) {
            $this->session->set_flashdata('error', 'Erreur lors de la création de la commande.');
            redirect('commande');
            return;
        }

        $lignes = $this->Panier_model->get_cart_lines($panier->id);
        foreach ($lignes as $ligne) {
            $produit = $this->Boutique_model->get_product_by_id($ligne->produit_id);
            $ligne_commande = [
                'commande_id' => $commande_id,
                'produit_id' => $ligne->produit_id,
                'nom_produit' => $produit ? $produit->nom_produit : 'Produit #' . $ligne->produit_id,
                'reference_produit' => $produit ? ($produit->reference ?? 'REF-' . $ligne->produit_id) : 'REF-' . $ligne->produit_id,
                'quantite' => $ligne->quantite,
                'prix_unitaire_ht' => $ligne->prix_unitaire_ht,
                'taux_tva' => $ligne->taux_tva
            ];
            $this->Commande_model->add_ligne_commande($ligne_commande);
        }

        $this->Panier_model->desactiver_panier($panier->id);

        $this->session->set_flashdata('success', 'Votre commande #' . $numero_commande . ' a été enregistrée avec succès !');
        redirect('commande/paiement/' . $commande_id);
    }

    /**
     * Générer numéro de commande unique
     */
   

private function generate_numero_commande()
{
    $prefix = 'CMD-' . date('Y');
    $this->load->dbforge();
    do {
        $numero = $prefix . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $exists = $this->db->where('numero_commande', $numero)->get('commandes')->num_rows();
    } while ($exists > 0);
    return $numero;
}
    /**
     * Page de paiement
     */
    public function paiement($commande_id)
    {
        $user_id = $this->session->userdata('user_id');
        $commande = $this->Commande_model->get_commande_by_id($commande_id);
        if (!$commande || $commande->user_id != $user_id) {
            show_404();
        }

        // Si déjà payé, rediriger vers confirmation
        if ($commande->statut_paiement == 'paye') {
            redirect('commande/confirmation/' . $commande_id);
        }

        $data['commande'] = $commande;
        $this->load->view('Paiement', $data);
    }

    /**
     * Vérification du paiement après saisie du code de transaction
     */
    public function verifier_paiement($commande_id)
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            show_404();
        }

        $commande = $this->Commande_model->get_commande_by_id((int)$commande_id);
        if (!$commande || $commande->user_id != $user_id) {
            show_404();
        }

        $transaction_code = $this->input->post('transaction_code');
        if (empty($transaction_code)) {
            $this->session->set_flashdata('error', 'Veuillez saisir le code de transaction.');
            redirect('commande/paiement/' . $commande_id);
        }

        // Ici vous intégrerez l'appel API réel
        $verifie = true; // À remplacer par un vrai service de validation

        if ($verifie) {
            $this->Commande_model->update_paiement($commande_id, 'paye', $transaction_code);
            $this->session->set_flashdata('success', 'Paiement confirmé ! Votre commande est en cours de traitement.');
            redirect('commande/confirmation/' . $commande_id);
        } else {
            $this->session->set_flashdata('error', 'Code de transaction invalide. Veuillez réessayer.');
            redirect('commande/paiement/' . $commande_id);
        }
    }

    /**
     * Page de confirmation
     */
    public function confirmation($commande_id = null)
    {
        if (!$commande_id) {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $commande = $this->Commande_model->get_commande_by_id((int)$commande_id);
        if (!$commande || $commande->user_id != $user_id) {
            show_404();
        }

        $data = [
            'commande' => $commande,
            'lignes' => $this->Commande_model->get_lignes_commande($commande_id),
            'titre' => 'Commande confirmée - AGF Pharma'
        ];
        $this->load->view('Comfirmation_View', $data);
    }
}