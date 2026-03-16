<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boutique_model extends CI_Model {

    private $table_produits = 'produits';
    private $table_categories = 'categories';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupérer toutes les catégories actives
     */
    public function get_categories()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('code_categorie', 'ASC');
        return $this->db->get($this->table_categories)->result();
    }

    /**
     * Récupérer une catégorie par son ID
     */
    public function get_categorie_by_id($id)
    {
        return $this->db->get_where($this->table_categories, [
            'id_categorie' => $id,
            'is_active' => 1
        ])->row();
    }

    /**
     * Récupérer tous les produits avec pagination
     */
   public function get_all_products($limit = 12, $offset = 0, $user_id = null)
{
    $this->db->select('p.*, c.nom_categorie, c.code_categorie, c.icone');
    if ($user_id) {
        $this->db->select('(SELECT 1 FROM favoris WHERE user_id = '.$user_id.' AND produit_id = p.id_produit) as user_favori', false);
    } else {
        $this->db->select('0 as user_favori', false);
    }
    $this->db->from('produits p');
    $this->db->join('categories c', 'c.id_categorie = p.id_categorie');
    $this->db->where('p.est_actif', 1);
    $this->db->where('p.statut', 'commercialise');
    $this->db->order_by('p.est_vedette', 'DESC');
    $this->db->order_by('p.ordre_affichage', 'ASC');
    $this->db->order_by('p.id_produit', 'DESC');
    if ($limit) {
        $this->db->limit($limit, $offset);
    }
    return $this->db->get()->result();
}
    /**
     * Compter tous les produits actifs
     */
    public function count_all_products()
    {
        $this->db->where('est_actif', 1);
        $this->db->where('statut', 'commercialise');
        return $this->db->count_all_results($this->table_produits);
    }

    /**
     * Récupérer les produits par catégorie avec pagination
     */
    public function get_products_by_category($id_categorie, $limit = 12, $offset = 0)
    {
        $this->db->select('p.*, c.nom_categorie, c.code_categorie, c.icone');
        $this->db->from($this->table_produits . ' p');
        $this->db->join($this->table_categories . ' c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.id_categorie', $id_categorie);
        $this->db->where('p.est_actif', 1);
        $this->db->where('p.statut', 'commercialise');
        $this->db->order_by('p.est_vedette', 'DESC');
        $this->db->order_by('p.ordre_affichage', 'ASC');
        $this->db->order_by('p.date_lancement', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Compter les produits par catégorie
     */
    public function count_products_by_category($id_categorie)
    {
        $this->db->where('id_categorie', $id_categorie);
        $this->db->where('est_actif', 1);
        $this->db->where('statut', 'commercialise');
        return $this->db->count_all_results($this->table_produits);
    }

    /**
     * Récupérer un produit par son slug
     */
    public function get_product_by_slug($slug)
    {
        $this->db->select('p.*, c.nom_categorie, c.code_categorie, c.description_longue as cat_description');
        $this->db->from($this->table_produits . ' p');
        $this->db->join($this->table_categories . ' c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.slug', $slug);
        $this->db->where('p.est_actif', 1);
        
        return $this->db->get()->row();
    }

    /**
     * Récupérer les produits similaires
     */
    public function get_related_products($id_categorie, $id_produit, $limit = 4)
    {
        $this->db->select('id_produit, nom_produit, slug, prix_public, image_principale, description_courte');
        $this->db->from($this->table_produits);
        $this->db->where('id_categorie', $id_categorie);
        $this->db->where('id_produit !=', $id_produit);
        $this->db->where('est_actif', 1);
        $this->db->where('statut', 'commercialise');
        $this->db->order_by('est_vedette', 'DESC');
        $this->db->order_by('ordre_affichage', 'ASC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Rechercher des produits avec pagination
     */
    public function search_products($query, $limit = 12, $offset = 0)
    {
        $search_term = '%' . $query . '%';
        
        $this->db->select('p.*, c.nom_categorie, c.code_categorie');
        $this->db->from($this->table_produits . ' p');
        $this->db->join($this->table_categories . ' c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.est_actif', 1);
        $this->db->where('p.statut', 'commercialise');
        
        $this->db->group_start();
        $this->db->like('p.nom_produit', $search_term);
        $this->db->or_like('p.description_courte', $search_term);
        $this->db->or_like('p.description_longue', $search_term);
        $this->db->or_like('p.composition', $search_term);
        $this->db->or_like('p.indications', $search_term);
        $this->db->group_end();
        
        $this->db->order_by('p.est_vedette', 'DESC');
        $this->db->order_by('p.ordre_affichage', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Compter les résultats de recherche
     */
    public function count_search_results($query)
    {
        $search_term = '%' . $query . '%';
        
        $this->db->from($this->table_produits);
        $this->db->where('est_actif', 1);
        $this->db->where('statut', 'commercialise');
        
        $this->db->group_start();
        $this->db->like('nom_produit', $search_term);
        $this->db->or_like('description_courte', $search_term);
        $this->db->or_like('description_longue', $search_term);
        $this->db->or_like('composition', $search_term);
        $this->db->or_like('indications', $search_term);
        $this->db->group_end();
        
        return $this->db->count_all_results();
    }

    /**
     * Récupérer les produits vedettes
     */
    public function get_featured_products($limit = 8)
    {
        $this->db->select('p.*, c.nom_categorie');
        $this->db->from($this->table_produits . ' p');
        $this->db->join($this->table_categories . ' c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.est_actif', 1);
        $this->db->where('p.statut', 'commercialise');
        $this->db->where('p.est_vedette', 1);
        $this->db->order_by('p.ordre_affichage', 'ASC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Récupérer le workflow d'une catégorie
     */
    public function get_category_workflow($id_categorie)
    {
        $this->db->select('*');
        $this->db->from('workflow_categories');
        $this->db->where('id_categorie', $id_categorie);
        $this->db->where('est_active', 1);
        $this->db->order_by('etape_ordre', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Récupérer les produits associés par catégorie
     */
    public function get_related_products_by_category($id_categorie, $limit = 8)
    {
        $this->db->select('p.*, c.nom_categorie, c.code_categorie');
        $this->db->from('produits p');
        $this->db->join('categories c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.id_categorie', $id_categorie);
        $this->db->where('p.est_actif', 1);
        $this->db->where('p.statut', 'commercialise');
        $this->db->order_by('p.est_vedette', 'DESC');
        $this->db->order_by('p.ordre_affichage', 'ASC');
        $this->db->order_by('RAND()');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Récupérer les workflows des catégories principales
     */
    public function get_main_categories_workflow($limit = 3)
    {
        $sql = "SELECT c.id_categorie, c.nom_categorie, c.code_categorie, 
                       COUNT(w.id_workflow) as total_etapes
                FROM categories c
                LEFT JOIN workflow_categories w ON w.id_categorie = c.id_categorie AND w.est_active = 1
                WHERE c.is_active = 1
                GROUP BY c.id_categorie
                HAVING total_etapes > 0
                ORDER BY c.ordre_affichage ASC
                LIMIT ?";
                
        $query = $this->db->query($sql, [$limit]);
        return $query->result();
    }

    public function get_product_by_id($id)
{
    return $this->db->get_where('produits', ['id_produit' => $id])->row();
}
public function get_cart_by_id($panier_id)
{
    return $this->db->get_where('paniers', ['id' => $panier_id])->row();
}


public function get_product_images($produit_id)
{
    $this->db->select('nom_fichier, legende, alt_text, ordre_affichage, est_principale');
    $this->db->from('produit_images');
    $this->db->where('id_produit', $produit_id);
    $this->db->where('est_active', 1);
    $this->db->order_by('ordre_affichage', 'ASC');
    return $this->db->get()->result();
}
}