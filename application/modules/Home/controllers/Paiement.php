<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paiement extends Public_Controller{

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {   

         // Charger le helper pour obtenir le token visiteur
        $visitor_token = get_visitor_token();
        $user_id = (int)$this->session->userdata('user_id');
        
        // Récupérer le panier actif
        $this->db->where('est_actif', 1);
        
        if ($user_id > 0) {
            $this->db->group_start();
            $this->db->where('user_id', $user_id);
            $this->db->or_where('visitor_token', $visitor_token);
            $this->db->group_end();
        } else {
            $this->db->where('visitor_token', $visitor_token);
        }
        
        $this->db->order_by('updated_at', 'DESC');
        $panier = $this->db->get('paniers')->row_array();
        
        // Récupérer les lignes du panier avec les infos produits
        $cart_items = [];
        $totals = [
            'total_ht' => 0,
            'total_ttc' => 0,
            'total_tva' => 0,
            'nombre_articles' => 0
        ];
        
        if ($panier) {
            $this->db->select('
                pl.id as ligne_id,
                pl.quantite,
                pl.prix_unitaire_ht,
                pl.taux_tva,
                pl.total_ligne_ht,
                pl.total_ligne_ttc,
                p.id as produit_id,
                p.nom,
                p.reference,
                p.image_principale,
                p.unite_vente,
                p.stock,
                p.slug,
                p.poids_kg
            ');
            $this->db->from('panier_lignes pl');
            $this->db->join('produits p', 'p.id = pl.produit_id');
            $this->db->where('pl.panier_id', $panier['id']);
            $this->db->where('p.est_actif', 1);
            $this->db->order_by('pl.created_at', 'DESC');
            $cart_items = $this->db->get()->result_array();
            
            // Calculer les totaux
            $totals['total_ht'] = $panier['total_ht'];
            $totals['total_ttc'] = $panier['total_ttc'];
            $totals['total_tva'] = $panier['total_ttc'] - $panier['total_ht'];
            $totals['nombre_articles'] = $panier['nombre_articles'];
        }
        
        // Frais de livraison (à adapter selon votre logique)
        $shipping_ht = 113.00;
        $shipping_ttc = $shipping_ht * 1.055; // TVA 5.5% sur livraison
        
        $data = [
            'cart_items' => $cart_items,
            'totals' => $totals,
            'shipping_ht' => $shipping_ht,
            'shipping_ttc' => $shipping_ttc,
            'grand_total_ht' => $totals['total_ht'] + $shipping_ht,
            'grand_total_ttc' => $totals['total_ttc'] + $shipping_ttc,
            'panier_id' => $panier['id'] ?? 0,
            'visitor_token' => $visitor_token
        ];


        $data['pays'] = $this->Model->read('pays', null, 'pays', 'ASC');
        $data['mode_payements'] = $this->Model->read('mode_payement', null, 'id_mode_payement');
        
        $this->load->view('livraison_adresse',$data);
    }
    

    public function Login()
    {   
        $this->load->view('login');
    }

    public function Paiement()
    {   
        $this->load->view('Paiement');
    }

    public function Comfirm()
    {   
        $this->load->view('Comfirmation_View');
    }


}