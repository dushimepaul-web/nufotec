<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commande_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function get_user_adresses($user_id)
    {
        return $this->db->where('user_id', $user_id)
                        ->order_by('est_principale', 'DESC')
                        ->get('adresses')
                        ->result();
    }

    public function get_adresse_by_id($adresse_id)
    {
        return $this->db->where('id', $adresse_id)->get('adresses')->row();
    }

    public function create_adresse($data)
    {
        if (!empty($data['est_principale'])) {
            $this->db->where('user_id', $data['user_id'])
                     ->update('adresses', ['est_principale' => 0]);
        }
        
        $this->db->insert('adresses', $data);
        return $this->db->insert_id();
    }

    public function create_commande($data)
    {
        $this->db->insert('commandes', $data);
        return $this->db->insert_id();
    }

    public function add_ligne_commande($data)
    {
        $this->db->insert('commande_lignes', $data);
        return $this->db->insert_id();
    }

    public function get_commande_by_id($commande_id)
    {
        return $this->db->where('id', $commande_id)->get('commandes')->row();
    }

    public function get_lignes_commande($commande_id)
    {
        return $this->db->where('commande_id', $commande_id)
                        ->get('commande_lignes')
                        ->result();
    }


    public function update_paiement($commande_id, $statut_paiement, $transaction_id = null)
{
    $data = [
        'statut_paiement' => $statut_paiement,
        'transaction_id' => $transaction_id,
        'date_paiement' => date('Y-m-d H:i:s')
    ];
    $this->db->where('id', $commande_id)->update('commandes', $data);
}
}