<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paniers extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // Vérifier que c'est un admin
       // if ($this->session->userdata('logged_in') !== TRUE || $this->session->userdata('role') !== 'admin') {
       //     redirect('Admin');
       // }
    }

    /**
     * Liste de tous les paniers actifs et abandonnés
     */
    public function index()
    {
        $filters = $this->input->get();

        // Construction de la requête
        $this->db->select('paniers.*, 
                          users.nom as user_nom, 
                          users.prenom as user_prenom, 
                          users.email as user_email,
                          users.telephone as user_telephone,
                          COUNT(panier_lignes.id) as nb_articles,
                          DATEDIFF(NOW(), paniers.updated_at) as jours_inactivite');
        $this->db->from('paniers');
        $this->db->join('users', 'users.id = paniers.user_id', 'left');
        $this->db->join('panier_lignes', 'panier_lignes.panier_id = paniers.id', 'left');
        
        // Filtres
        $this->applyFilters($filters);
        
        $this->db->group_by('paniers.id');
        $this->db->order_by('paniers.updated_at', 'DESC');
        
        $data['paniers'] = $this->db->get()->result_array();

        // Statistiques
        $data['stats'] = $this->getStats();
        $data['filters'] = $filters;

        $this->load->view('Panier_View', $data);
    }

    /**
     * Voir le détail d'un panier spécifique
     */
    public function Detail($panier_id)
    {
        if (!$panier_id || !is_numeric($panier_id)) {
            $this->session->set_flashdata('error', 'ID panier invalide');
            redirect('paniers');
        }

        // Infos panier + client
        $this->db->select('paniers.*, users.*, paniers.id as panier_id');
        $this->db->from('paniers');
        $this->db->join('users', 'users.id = paniers.user_id', 'left');
        $this->db->where('paniers.id', $panier_id);
        $data['panier'] = $this->db->get()->row_array();

        if (!$data['panier']) {
            $this->session->set_flashdata('error', 'Panier non trouvé');
            redirect('paniers');
        }

        // Lignes du panier
        $this->db->select('panier_lignes.*, produits.nom_produit, produits.image_principale, produits.stock');
        $this->db->from('panier_lignes');
        $this->db->join('produits', 'produits.id_produit = panier_lignes.produit_id', 'left');
        $this->db->where('panier_lignes.panier_id', $panier_id);
        $data['lignes'] = $this->db->get()->result_array();

        // Historique des commandes du client
        $data['historique_client'] = $this->Model->read('commandes', 
            ['user_id' => $data['panier']['user_id']], 
            'created_at', 
            'DESC', 
            5
        );

        $this->load->view('Panier_lignes', $data);
    }

    /**
     * Convertir un panier en commande (pour le support client)
     */
    public function ConvertirCommande($panier_id)
    {
        $panier = $this->Model->readOne('paniers', ['id' => $panier_id]);
        
        if (!$panier) {
            $this->session->set_flashdata('error', 'Panier non trouvé');
            redirect('paniers');
        }

        // Rediriger vers la création de commande avec pré-remplissage
        $this->session->set_flashdata('panier_a_convertir', $panier_id);
        redirect('Commandes/Create/' . $panier_id);
    }

    /**
     * Envoyer un email de relance pour panier abandonné
     */
    public function Relancer($panier_id)
    {
        $panier = $this->getPanierComplet($panier_id);
        
        if (!$panier) {
            echo json_encode(['success' => false, 'message' => 'Panier non trouvé']);
            return;
        }

        // Vérifier que le panier a plus de 24h et moins de 7 jours
        $jours = (strtotime('now') - strtotime($panier['updated_at'])) / 86400;
        
        if ($jours < 1) {
            echo json_encode(['success' => false, 'message' => 'Panier trop récent']);
            return;
        }

        // Envoyer l'email (à implémenter avec votre librairie email)
        $this->load->library('email');
        
        $this->email->from('contact@votresite.com', 'Votre Site');
        $this->email->to($panier['user_email']);
        $this->email->subject('Vous avez oublié quelque chose dans votre panier !');
        
        $data['panier'] = $panier;
        $message = $this->load->view('emails/relance_panier', $data, TRUE);
        
        $this->email->message($message);
        
        if ($this->email->send()) {
            // Logger la relance
            $this->Model->create('paniers_relances', [
                'panier_id' => $panier_id,
                'user_id' => $panier['user_id'],
                'date_relance' => date('Y-m-d H:i:s'),
                'type' => 'email'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Email envoyé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur envoi email']);
        }
    }

    /**
     * Supprimer les vieux paniers inactifs (cron job ou manuel)
     */
    public function Nettoyer($jours = 30)
    {
        $date_limite = date('Y-m-d H:i:s', strtotime("-$jours days"));
        
        // Récupérer les paniers à nettoyer
        $this->db->where('updated_at <', $date_limite);
        $this->db->where('est_actif', 1);
        $paniers = $this->db->get('paniers')->result_array();
        
        $supprimes = 0;
        
        foreach ($paniers as $panier) {
            // Optionnel : sauvegarder dans historique avant suppression
            // $this->sauvegarderAbandon($panier);
            
            // Supprimer les lignes
            $this->db->where('panier_id', $panier['id']);
            $this->db->delete('panier_lignes');
            
            // Désactiver le panier
            $this->Model->update('paniers', ['id' => $panier['id']], [
                'est_actif' => 0,
                'total_ht' => 0,
                'total_ttc' => 0,
                'nombre_articles' => 0
            ]);
            
            $supprimes++;
        }
        
        $this->session->set_flashdata('success', "$supprimes paniers nettoyés");
        redirect('paniers');
    }

    /**
     * Export CSV des paniers abandonnés
     */
    public function Export()
    {
        $this->db->select('paniers.*, users.email, users.nom, users.prenom');
        $this->db->from('paniers');
        $this->db->join('users', 'users.id = paniers.user_id');
        $this->db->where('paniers.est_actif', 1);
        $this->db->where('paniers.total_ttc >', 0);
        $paniers = $this->db->get()->result_array();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=paniers_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Client', 'Email', 'Montant TTC', 'Articles', 'Dernière activité', 'Jours inactivité']);

        foreach ($paniers as $p) {
            $jours = floor((time() - strtotime($p['updated_at'])) / 86400);
            fputcsv($output, [
                $p['id'],
                $p['prenom'] . ' ' . $p['nom'],
                $p['email'],
                $p['total_ttc'],
                $p['nombre_articles'],
                $p['updated_at'],
                $jours
            ]);
        }
        fclose($output);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function applyFilters($filters)
    {
        // Filtre par statut (actif/abandonné)
        if (!empty($filters['statut'])) {
            if ($filters['statut'] === 'abandonne') {
                $this->db->where('DATEDIFF(NOW(), paniers.updated_at) >', 2);
            } elseif ($filters['statut'] === 'recent') {
                $this->db->where('DATEDIFF(NOW(), paniers.updated_at) <=', 1);
            }
        }

        // Filtre par montant minimum
        if (!empty($filters['montant_min'])) {
            $this->db->having('paniers.total_ttc >=', $filters['montant_min']);
        }

        // Filtre par client
        if (!empty($filters['user_id'])) {
            $this->db->where('paniers.user_id', $filters['user_id']);
        }

        // Recherche
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('users.nom', $filters['search']);
            $this->db->or_like('users.prenom', $filters['search']);
            $this->db->or_like('users.email', $filters['search']);
            $this->db->group_end();
        }
    }

    private function getStats()
    {
        $stats = [];

        // Total paniers actifs
        $stats['total_actifs'] = $this->db->where('est_actif', 1)->count_all_results('paniers');

        // Paniers abandonnés (> 2 jours)
        $this->db->where('est_actif', 1);
        $this->db->where('DATEDIFF(NOW(), updated_at) >', 2);
        $stats['abandonnes'] = $this->db->count_all_results('paniers');

        // Valeur totale des paniers
        $this->db->select_sum('total_ttc', 'montant');
        $result = $this->db->where('est_actif', 1)->get('paniers')->row();
        $stats['valeur_totale'] = $result->montant ?? 0;

        // Panier moyen
        if ($stats['total_actifs'] > 0) {
            $stats['panier_moyen'] = $stats['valeur_totale'] / $stats['total_actifs'];
        } else {
            $stats['panier_moyen'] = 0;
        }

        // Top produits dans les paniers
        $this->db->select('produits.nom_produit, SUM(panier_lignes.quantite) as total');
        $this->db->from('panier_lignes');
        $this->db->join('paniers', 'paniers.id = panier_lignes.panier_id');
        $this->db->join('produits', 'produits.id_produit = panier_lignes.produit_id');
        $this->db->where('paniers.est_actif', 1);
        $this->db->group_by('panier_lignes.produit_id');
        $this->db->order_by('total', 'DESC');
        $this->db->limit(5);
        $stats['top_produits'] = $this->db->get()->result_array();

        return $stats;
    }

    private function getPanierComplet($panier_id)
    {
        $this->db->select('paniers.*, users.nom, users.prenom, users.email');
        $this->db->from('paniers');
        $this->db->join('users', 'users.id = paniers.user_id');
        $this->db->where('paniers.id', $panier_id);
        return $this->db->get()->row_array();
    }
}