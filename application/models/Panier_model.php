<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panier_model extends CI_Model {

    private $table_paniers = 'paniers';
    private $table_lignes = 'panier_lignes';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupérer le panier actif d'un utilisateur connecté
     */
    public function get_active_cart_by_user($user_id)
    {
        return $this->db->where('user_id', $user_id)
                        ->where('est_actif', 1)
                        ->order_by('created_at', 'DESC')
                        ->get($this->table_paniers)
                        ->row();
    }

    /**
     * Récupérer le panier actif d'un visiteur (token)
     */
    public function get_active_cart_by_token($visitor_token)
    {
        return $this->db->where('visitor_token', $visitor_token)
                        ->where('est_actif', 1)
                        ->order_by('created_at', 'DESC')
                        ->get($this->table_paniers)
                        ->row();
    }

    /**
     * Créer un nouveau panier
     */
public function create_cart($user_id = null, $visitor_token = null)
{
    $data = [
        'user_id' => $user_id,        // null ou entier valide
        'visitor_token' => $visitor_token,
        'total_ht' => 0,
        'total_ttc' => 0,
        'nombre_articles' => 0,
        'est_actif' => 1
    ];
    $this->db->insert($this->table_paniers, $data);
    return $this->db->insert_id();
}
    /**
     * Ajouter ou mettre à jour une ligne de panier
     */
   public function add_or_update_line($panier_id, $produit_id, $quantite, $prix_unitaire_ht, $taux_tva)
{
    $data = [
        'panier_id' => $panier_id,
        'produit_id' => $produit_id,
        'quantite' => $quantite,
        'prix_unitaire_ht' => $prix_unitaire_ht,
        'taux_tva' => $taux_tva
    ];
    // NE PAS inclure total_ligne_ht, total_ligne_ttc
    $this->db->insert('panier_lignes', $data);
    return $this->db->affected_rows() > 0;
}

    /**
     * Récupérer les lignes d'un panier avec les infos produit
     */
    public function get_cart_lines($panier_id)
    {
        $this->db->select('l.*, p.nom_produit, p.slug, p.image_principale')
                 ->from($this->table_lignes . ' l')
                 ->join('produits p', 'p.id_produit = l.produit_id')
                 ->where('l.panier_id', $panier_id);
        return $this->db->get()->result();
    }

    /**
     * Mettre à jour les totaux du panier (nombre d'articles, total HT, TTC)
     */
   public function update_cart_totals($panier_id)
{
    $this->db->select('SUM(total_ligne_ht) as total_ht, SUM(total_ligne_ttc) as total_ttc, SUM(quantite) as nb_articles');
    $this->db->from('panier_lignes');
    $this->db->where('panier_id', $panier_id);
    $query = $this->db->get();
    $row = $query->row();
    
    $data = [
        'total_ht' => $row->total_ht ?? 0,
        'total_ttc' => $row->total_ttc ?? 0,
        'nombre_articles' => $row->nb_articles ?? 0
    ];
    $this->db->where('id', $panier_id)->update('paniers', $data);
}

    /**
     * Mettre à jour la quantité d'une ligne spécifique
     */
    public function update_line_quantity($ligne_id, $quantite)
    {
        return $this->db->where('id', $ligne_id)
                        ->update($this->table_lignes, ['quantite' => $quantite]);
    }

    /**
     * Supprimer une ligne du panier
     */
    public function delete_line($ligne_id)
    {
        return $this->db->delete($this->table_lignes, ['id' => $ligne_id]);
    }

public function get_product_by_id($id)
{
    return $this->db->get_where('produits', ['id_produit' => $id])->row();
}

public function get_cart_by_id($panier_id)
{
    return $this->db->get_where('paniers', ['id' => $panier_id])->row();
}

public function desactiver_panier($panier_id)
{
    return $this->db->where('id', $panier_id)
                    ->update($this->table_paniers, ['est_actif' => 0]);
}
}