<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commandes extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer les filtres
        $filters = $this->input->get();
        
        // Construction de la requête avec jointures
        $this->db->select('commandes.*, users.nom as user_nom, users.prenom as user_prenom, users.email as user_email, users.telephone as user_telephone');
        $this->db->from('commandes');
        $this->db->join('users', 'users.id = commandes.user_id', 'left');
        
        // Appliquer les filtres
        $this->applyFilters($filters);
        
        $data['commandes'] = $this->db->order_by('commandes.created_at', 'DESC')
                                      ->get()->result_array();
        
        // Récupérer les détails pour chaque commande
        foreach ($data['commandes'] as &$commande) {
            // Nombre de produits dans la commande (lignes)
            $commande['nb_produits'] = $this->db->where('commande_id', $commande['id'])->count_all_results('commande_lignes');
            
            // Lignes de commande avec infos produit
            $this->db->select('commande_lignes.*, produits.image_principale');
            $this->db->from('commande_lignes');
            $this->db->join('produits', 'produits.id_produit = commande_lignes.produit_id', 'left');
            $this->db->where('commande_lignes.commande_id', $commande['id']);
            $commande['lignes'] = $this->db->get()->result_array();
            
            // Adresses
            $commande['adresse_livraison'] = $this->Model->readOne('adresses', ['id' => $commande['adresse_livraison_id']]);
            $commande['adresse_facturation'] = $this->Model->readOne('adresses', ['id' => $commande['adresse_facturation_id']]);
            
            // Pays des adresses
            if ($commande['adresse_livraison']) {
                $commande['adresse_livraison']['pays'] = $this->Model->readOne('pays', ['id' => $commande['adresse_livraison']['pays_id']]);
            }
            if ($commande['adresse_facturation']) {
                $commande['adresse_facturation']['pays'] = $this->Model->readOne('pays', ['id' => $commande['adresse_facturation']['pays_id']]);
            }
        }
        
        $data['users'] = $this->Model->read('users', ['deleted_at' => NULL, 'is_active' => 1], 'nom', 'ASC');
        $data['filters'] = $filters;
        
        // Statistiques pour le dashboard
        $data['stats'] = $this->getStats();
        
        $this->load->view('Commandes_View', $data);
    }

    // Méthode de filtrage avancée
    private function applyFilters($filters)
    {
        // Filtre par numéro de commande
        if (!empty($filters['numero_commande'])) {
            $this->db->like('commandes.numero_commande', $filters['numero_commande']);
        }
        
        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $this->db->where('commandes.user_id', $filters['user_id']);
        }
        
        // Filtre par statut
        if (!empty($filters['statut'])) {
            $this->db->where('commandes.statut', $filters['statut']);
        }
        
        // Filtre par statut de paiement
        if (!empty($filters['statut_paiement'])) {
            $this->db->where('commandes.statut_paiement', $filters['statut_paiement']);
        }
        
        // Filtre par mode de paiement
        if (!empty($filters['mode_paiement'])) {
            $this->db->where('commandes.mode_paiement', $filters['mode_paiement']);
        }
        
        // Filtre par date de création
        if (!empty($filters['date_debut'])) {
            $this->db->where('commandes.created_at >=', $filters['date_debut'] . ' 00:00:00');
        }
        if (!empty($filters['date_fin'])) {
            $this->db->where('commandes.created_at <=', $filters['date_fin'] . ' 23:59:59');
        }
        
        // Filtre par montant min/max
        if (!empty($filters['montant_min'])) {
            $this->db->where('commandes.total_general_ttc >=', $filters['montant_min']);
        }
        if (!empty($filters['montant_max'])) {
            $this->db->where('commandes.total_general_ttc <=', $filters['montant_max']);
        }
        
        // Filtre par recherche texte
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('commandes.numero_commande', $filters['search']);
            $this->db->or_like('users.nom', $filters['search']);
            $this->db->or_like('users.prenom', $filters['search']);
            $this->db->or_like('users.email', $filters['search']);
            $this->db->or_like('commandes.transaction_id', $filters['search']);
            $this->db->group_end();
        }
    }

    // Statistiques des commandes
    private function getStats()
    {
        $stats = [];
        
        // Total des commandes
        $stats['total'] = $this->db->count_all_results('commandes');
        
        // Commandes par statut
        $stats['par_statut'] = [];
        $statuts = ['en_attente', 'confirmee', 'preparation', 'expediee', 'livree', 'annulee', 'remboursee'];
        foreach ($statuts as $s) {
            $stats['par_statut'][$s] = $this->db->where('statut', $s)->count_all_results('commandes');
        }
        
        // Chiffre d'affaires total
        $this->db->select_sum('total_general_ttc', 'ca_total');
        $result = $this->db->where('statut !=', 'annulee')->get('commandes')->row_array();
        $stats['ca_total'] = $result['ca_total'] ?? 0;
        
        // Commandes du jour
        $stats['aujourd_hui'] = $this->db->where('DATE(created_at)', date('Y-m-d'))->count_all_results('commandes');
        
        // Commandes en attente de traitement
        $stats['en_attente_traitement'] = $this->db->where_in('statut', ['en_attente', 'confirmee', 'preparation'])->count_all_results('commandes');
        
        return $stats;
    }

    // Export CSV des commandes filtrées
    public function Export()
    {
        $filters = $this->input->get();
        
        $this->db->select('commandes.*, users.nom as user_nom, users.prenom as user_prenom, users.email as user_email');
        $this->db->from('commandes');
        $this->db->join('users', 'users.id = commandes.user_id', 'left');
        $this->applyFilters($filters);
        
        $commandes = $this->db->order_by('commandes.created_at', 'DESC')->get()->result_array();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=commandes_export_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Numéro', 'Client', 'Email', 'Total HT', 'TVA', 'Total TTC', 'Frais Livraison', 'Total Général', 'Statut', 'Paiement', 'Statut Paiement', 'Mode Livraison', 'Date Commande']);
        
        foreach ($commandes as $c) {
            fputcsv($output, [
                $c['id'],
                $c['numero_commande'],
                ($c['user_nom'] ?? '') . ' ' . ($c['user_prenom'] ?? ''),
                $c['user_email'] ?? '',
                $c['total_ht'],
                $c['total_tva'],
                $c['total_ttc'],
                $c['frais_livraison_ht'],
                $c['total_general_ttc'],
                $c['statut'],
                $c['mode_paiement'] ?? '-',
                $c['statut_paiement'],
                $c['mode_livraison'],
                $c['created_at']
            ]);
        }
        fclose($output);
        exit;
    }

    // Voir le détail d'une commande
public function Detail($id = null)
{
    // Vérification de l'ID
    if ($id === null || !is_numeric($id)) {
        $this->session->set_flashdata('error', 'ID de commande invalide.');
        redirect(base_url('Commandes'));
        return;
    }

    // Récupérer la commande avec infos client
    $this->db->select('commandes.*, users.nom as user_nom, users.prenom as user_prenom, users.email as user_email, users.telephone as user_telephone');
    $this->db->from('commandes');
    $this->db->join('users', 'users.id = commandes.user_id', 'left');
    $this->db->where('commandes.id', $id);
    $data['commande'] = $this->db->get()->row_array();

    if (!$data['commande']) {
        $this->session->set_flashdata('error', 'Commande non trouvée.');
        redirect(base_url('Commandes'));
        return;
    }

    // Informations client complet
    $data['client'] = $this->Model->readOne('users', ['id' => $data['commande']['user_id']]);

    // Adresses avec pays
    $data['adresse_livraison'] = $this->Model->readOne('adresses', ['id' => $data['commande']['adresse_livraison_id']]);
    $data['adresse_facturation'] = $this->Model->readOne('adresses', ['id' => $data['commande']['adresse_facturation_id']]);

    if ($data['adresse_livraison']) {
        $data['adresse_livraison']['pays'] = $this->Model->readOne('pays', ['id' => $data['adresse_livraison']['pays_id']]);
    }
    if ($data['adresse_facturation']) {
        $data['adresse_facturation']['pays'] = $this->Model->readOne('pays', ['id' => $data['adresse_facturation']['pays_id']]);
    }

    // Lignes de commande avec infos produit
    $this->db->select('commande_lignes.*, produits.image_principale, produits.description_courte');
    $this->db->from('commande_lignes');
    $this->db->join('produits', 'produits.id = commande_lignes.produit_id', 'left');
    $this->db->where('commande_lignes.commande_id', $id);
    $data['lignes'] = $this->db->get()->result_array();

    // === CALCUL DES TOTAUX ===
    $data['totaux'] = [
        'total_lignes_ht' => 0,
        'total_lignes_ttc' => 0,
        'total_tva_lignes' => 0,
        'frais_livraison_ttc' => 0,
        'tva_livraison' => 0,
        'total_general' => 0
    ];

    // Calcul des totaux des lignes
    foreach ($data['lignes'] as $ligne) {
        $data['totaux']['total_lignes_ht'] += floatval($ligne['total_ligne_ht']);
        $data['totaux']['total_lignes_ttc'] += floatval($ligne['total_ligne_ttc']);
    }

    // TVA des produits
    $data['totaux']['total_tva_lignes'] = $data['totaux']['total_lignes_ttc'] - $data['totaux']['total_lignes_ht'];

    // Frais de livraison
    $frais_livraison_ht = floatval($data['commande']['frais_livraison_ht'] ?? 0);
    $data['totaux']['frais_livraison_ttc'] = $frais_livraison_ht * 1.2;
    $data['totaux']['tva_livraison'] = $frais_livraison_ht * 0.2;

    // Total général (utilise la colonne générée ou calcule)
    $data['totaux']['total_general'] = floatval($data['commande']['total_general_ttc'] ?? ($data['totaux']['total_lignes_ttc'] + $data['totaux']['frais_livraison_ttc']));

    $this->load->view('CommandeDetail_View', $data);
}

    // Changer le statut d'une commande
    function ChangeStatut(){
        $id = $this->input->post('id');
        $nouveau_statut = $this->input->post('statut');
        $notes = $this->input->post('notes') ?: null;
        
        $statuts_valides = ['en_attente', 'confirmee', 'preparation', 'expediee', 'livree', 'annulee', 'remboursee'];
        
        if (!in_array($nouveau_statut, $statuts_valides)) {
            $this->session->set_flashdata('error', 'Statut invalide.');
            redirect(base_url('Commandes'));
            return;
        }
        
        $data = [
            'statut' => $nouveau_statut,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Si livrée, mettre à jour la date de livraison réelle
        if ($nouveau_statut == 'livree') {
            $data['date_livraison_reelle'] = date('Y-m-d H:i:s');
        }
        
        // Ajouter les notes si fournies
        if ($notes) {
            $commande = $this->Model->readOne('commandes', ['id' => $id]);
            $notes_existantes = $commande['notes'] ? $commande['notes'] . "\n" : '';
            $data['notes'] = $notes_existantes . date('d/m/Y H:i') . ' - ' . $notes;
        }
        
        $rsp = $this->Model->update('commandes', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de la commande mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Commandes'));
    }

    // Mettre à jour le statut de paiement
    function UpdatePaiement(){
        $id = $this->input->post('id');
        $statut_paiement = $this->input->post('statut_paiement');
        $transaction_id = $this->input->post('transaction_id') ?: null;
        
        $data = [
            'statut_paiement' => $statut_paiement,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($transaction_id) {
            $data['transaction_id'] = $transaction_id;
        }
        
        if ($statut_paiement == 'paye') {
            $data['date_paiement'] = date('Y-m-d H:i:s');
        }
        
        $rsp = $this->Model->update('commandes', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de paiement mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Commandes'));
    }

    // Mettre à jour les informations de livraison
    function UpdateLivraison(){
        $id = $this->input->post('id');
        
        $data = [
            'mode_livraison' => $this->input->post('mode_livraison'),
            'frais_livraison_ht' => $this->input->post('frais_livraison_ht') ?: 0,
            'date_livraison_prevue' => $this->input->post('date_livraison_prevue') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $rsp = $this->Model->update('commandes', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Informations de livraison mises à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Commandes'));
    }

    // Créer une commande (manuellement par admin)
    function Create(){
        // Validation
        $this->form_validation->set_rules('user_id', 'Client', 'required|numeric');
        $this->form_validation->set_rules('adresse_livraison_id', 'Adresse de livraison', 'required|numeric');
        $this->form_validation->set_rules('adresse_facturation_id', 'Adresse de facturation', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Commandes'));
            return;
        }

        // Générer le numéro de commande
        $numero_commande = $this->generateNumeroCommande();
        
        // Récupérer les produits du POST
        $produits_post = $this->input->post('produits');
        
        if (empty($produits_post) || !is_array($produits_post)) {
            $this->session->set_flashdata('error', 'Vous devez ajouter au moins un produit à la commande.');
            redirect(base_url('Commandes'));
            return;
        }

        // Calculer les totaux à partir des produits
        $total_ht = 0;
        $total_tva = 0;
        $lignes_commande = [];

        foreach ($produits_post as $p) {
            if (empty($p['produit_id']) || empty($p['quantite']) || empty($p['prix_ht'])) {
                continue;
            }

            $produit = $this->Model->readOne('produits', ['id' => $p['produit_id']]);
            if (!$produit) {
                // Essayer par référence
                $produit = $this->Model->readOne('produits', ['reference' => $p['produit_id']]);
            }

            if ($produit) {
                $taux_tva = $produit['tva'] ?? 20;
                $ligne_ht = $p['quantite'] * $p['prix_ht'];
                $ligne_tva = $ligne_ht * $taux_tva / 100;
                
                $total_ht += $ligne_ht;
                $total_tva += $ligne_tva;

                $lignes_commande[] = [
                    'produit_id' => $produit['id'],
                    'nom_produit' => $produit['nom'],
                    'reference_produit' => $produit['reference'],
                    'quantite' => $p['quantite'],
                    'prix_unitaire_ht' => $p['prix_ht'],
                    'taux_tva' => $taux_tva
                ];

                // Décrémenter le stock
                $this->db->set('stock', 'stock - ' . $p['quantite'], FALSE);
                $this->db->where('id', $produit['id']);
                $this->db->update('produits');
            }
        }

        if (empty($lignes_commande)) {
            $this->session->set_flashdata('error', 'Aucun produit valide trouvé pour cette commande.');
            redirect(base_url('Commandes'));
            return;
        }

        $total_ttc = $total_ht + $total_tva;
        $frais_livraison = $this->input->post('frais_livraison_ht') ?: 0;

        $data = array(
            'numero_commande' => $numero_commande,
            'user_id' => $this->input->post('user_id'),
            'adresse_livraison_id' => $this->input->post('adresse_livraison_id'),
            'adresse_facturation_id' => $this->input->post('adresse_facturation_id'),
            'total_ht' => $total_ht,
            'total_tva' => $total_tva,
            'total_ttc' => $total_ttc,
            'frais_livraison_ht' => $frais_livraison,
            'statut' => $this->input->post('statut') ?: 'en_attente',
            'mode_paiement' => $this->input->post('mode_paiement') ?: NULL,
            'statut_paiement' => $this->input->post('statut_paiement') ?: 'en_attente',
            'mode_livraison' => $this->input->post('mode_livraison') ?: 'standard',
            'date_livraison_prevue' => $this->input->post('date_livraison_prevue') ?: NULL,
            'notes' => $this->input->post('notes') ?: NULL,
            'ip_commande' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $commande_id = $this->Model->create('commandes', $data);

        if ($commande_id) {
            // Créer les lignes de commande
            foreach ($lignes_commande as $ligne) {
                $ligne['commande_id'] = $commande_id;
                $this->Model->create('commande_lignes', $ligne);
            }
            
            $this->session->set_flashdata('success', 'Commande ' . $numero_commande . ' créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la commande.');
        }
        redirect(base_url('Commandes'));
    }

    // Annuler une commande
    function Annuler(){
        $id = $this->input->post('id');
        $raison = $this->input->post('raison') ?: 'Annulation administrative';
        
        $commande = $this->Model->readOne('commandes', ['id' => $id]);
        
        // Vérifier si la commande peut être annulée
        if (in_array($commande['statut'], ['expediee', 'livree'])) {
            $this->session->set_flashdata('error', 'Impossible d\'annuler une commande déjà expédiée ou livrée.');
            redirect(base_url('Commandes'));
            return;
        }
        
        $notes = $commande['notes'] ? $commande['notes'] . "\n" : '';
        $notes .= date('d/m/Y H:i') . ' - ANNULATION: ' . $raison;

        $rsp = $this->Model->update('commandes', ['id' => $id], [
            'statut' => 'annulee',
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Remettre le stock
            $this->restituerStock($id);
            $this->session->set_flashdata('success', 'Commande annulée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de l\'annulation.');
        }
        redirect(base_url('Commandes'));
    }

    // Générer un numéro de commande unique
    private function generateNumeroCommande()
    {
        $prefix = 'CMD-' . date('Y') . '-';
        $this->db->like('numero_commande', $prefix, 'after');
        $count = $this->db->count_all_results('commandes');
        return $prefix . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    // Restituer le stock lors d'une annulation
    private function restituerStock($commande_id)
    {
        $lignes = $this->Model->read('commande_lignes', ['commande_id' => $commande_id]);
        foreach ($lignes as $ligne) {
            $this->db->set('stock', 'stock + ' . $ligne['quantite'], FALSE);
            $this->db->where('id', $ligne['produit_id']);
            $this->db->update('produits');
        }
    }

    // Récupérer les adresses d'un utilisateur (pour AJAX)
    public function GetAdressesUser($user_id)
    {
        $this->db->select('adresses.*, pays.pays as pays_nom');
        $this->db->from('adresses');
        $this->db->join('pays', 'pays.id = adresses.pays_id', 'left');
        $this->db->where('adresses.user_id', $user_id);
        $this->db->order_by('adresses.est_principale', 'DESC');
        $adresses = $this->db->get()->result_array();
        echo json_encode($adresses);
    }

    // Rechercher un produit par ID ou référence (AJAX)
    public function SearchProduit()
    {
        $term = $this->input->get('term');
        
        $this->db->select('id, nom, reference, prix_ht, tva, stock');
        $this->db->from('produits');
        $this->db->where('est_actif', 1);
        $this->db->where('est_disponible', 1);
        $this->db->group_start();
        $this->db->like('id', $term);
        $this->db->or_like('reference', $term);
        $this->db->or_like('nom', $term);
        $this->db->group_end();
        $this->db->limit(10);
        
        $result = $this->db->get()->result_array();
        echo json_encode($result);
    }




    

}
