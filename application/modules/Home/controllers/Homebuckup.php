<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    $this->load->helper('cookie');
    $this->load->helper('text');
    $this->load->model('Cart_model');
    $this->load->library('pagination');
    }

    public function index()
    {   
        $data['pays'] = $this->Model->read('pays', null, 'pays', 'ASC');

        
        // 1. PRODUITS POPULAIRES (les plus vendus)
        $sql_produits = "SELECT p.*, 
                                COUNT(DISTINCT cl.id) as nombre_ventes,
                                AVG(pa.note) as note_moyenne_calculee
                        FROM produits p
                        LEFT JOIN commande_lignes cl ON p.id = cl.produit_id
                        LEFT JOIN commandes c ON cl.commande_id = c.id 
                            AND c.statut = 'livree'
                        LEFT JOIN produit_avis pa ON p.id = pa.produit_id 
                            AND pa.est_valide = 1
                        WHERE p.est_actif = 1
                        GROUP BY p.id
                        ORDER BY nombre_ventes DESC, note_moyenne_calculee DESC
                        LIMIT 8";
        
        $data['produits_populaires'] = $this->Model->query($sql_produits);
        
        // 2. MÉDECINS DISPONIBLES
        $sql_medecins = "SELECT m.*, u.nom, u.prenom, u.photo
                        FROM medecins m
                        INNER JOIN users u ON m.user_id = u.id
                        WHERE m.est_disponible = 1 
                        AND u.is_active = 1
                        ORDER BY m.note_moyenne DESC
                        LIMIT 5";
        
        $data['medecins_disponibles'] = $this->Model->query($sql_medecins);
        
        // 3. PROJETS VEDETTE
        $sql_projets = "SELECT pi.*, p.pays as pays_nom,
                               (pi.montant_collecte / pi.montant_objectif) * 100 as pourcentage_atteint,
                               DATEDIFF(pi.date_fin, NOW()) as jours_restants
                        FROM projets_investissement pi
                        INNER JOIN pays p ON pi.pays_id = p.id
                        WHERE pi.statut = 'en_cours' 
                        AND pi.est_en_vedette = 1
                        ORDER BY pi.est_urgent DESC, pi.created_at DESC
                        LIMIT 3";
        
        $data['projets_vedette'] = $this->Model->query($sql_projets);
        
        // 4. TÉMOIGNAGES
        $sql_temoignages = "SELECT pa.*, u.nom, u.prenom, u.photo, 'Client' as type_utilisateur
                           FROM produit_avis pa
                           INNER JOIN users u ON pa.user_id = u.id
                           WHERE pa.est_valide = 1
                           ORDER BY pa.created_at DESC
                           LIMIT 5";
        
        $data['temoignages'] = $this->Model->query($sql_temoignages);
        
        // Journaliser la visite
        $this->Model->log_visit();


        // On récupère uniquement les slides actives, triées par 'slide_order'

    $data['slides'] = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
   $data['produits'] = $this->Model->read('produits', ['est_actif' => 1, 'est_disponible' => 1 ], 'nombre_ventes','DESC',3);
        
        // Charger la vue avec toutes les données
        $this->load->view('Home_View', $data);
    }



/*
    public function index()
    {   
    
        $this->Model->log_visit();
        
        // Charger la vue avec toutes les données
        $this->load->view('Boutique_View');
    }*/



    public function Overview(){
         $this->load->view('Trategic_Overview_View');
    }

     public function Contact(){
         $this->load->view('Contact_View');
    }

  

    public function Command(){
         $this->load->view('commande_View');
    }
    




















    /**
     * Page détail produit
     
    public function detail($slug = null) {
        if (empty($slug)) {
            redirect('Home/Boutique');
        }

        // Récupérer le produit
        $this->load->model('Produit_model'); // Votre modèle existant
        $data['produit'] = $this->Produit_model->get_by_slug($slug);
        
        if (empty($data['produit'])) {
            show_404();
        }

        // Récupérer les images supplémentaires si vous avez une table d'images
        $data['images'] = []; // À adapter selon votre structure

        // Charger la vue
        $data['title'] = $data['produit']['nom'] . ' - AGF Phytomed';
        $this->load->view('frontend/detail_produit', $data);
    }
*/
    /**
     * AJAX: Ajouter au panier
     */
   public function ajouter_au_panier() {
    // FORCER LE JSON
    header('Content-Type: application/json');
    
    try {
        // Test 1 : Vérifier POST
        $product_id = $this->input->post('product_id');
        $quantity = $this->input->post('quantity');
        
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'product_id vide']);
            return;
        }

        // Test 2 : Vérifier modèle
        if (!isset($this->Cart_model)) {
            $this->load->model('Cart_model');
        }

        // Test 3 : Vérifier produit
        $product = $this->Cart_model->get_product($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            return;
        }

        // Test 4 : Vérifier session/cookie
        $user_id = $this->session->userdata('user_id');
        $visitor_token = get_cookie('visitor_token');
        
        if (!$user_id && !$visitor_token) {
            $visitor_token = $this->Cart_model->generate_visitor_token();
            set_cookie('visitor_token', $visitor_token, 86400 * 30);
        }

        // Test 5 : Créer panier
        $cart = $this->Cart_model->get_or_create_cart($user_id, $visitor_token);
        if (!$cart) {
            echo json_encode(['success' => false, 'message' => 'Panier non créé']);
            return;
        }

        // Test 6 : Ajouter
        $prix_ht = floatval($product['prix_ht']);
        $tva = floatval($product['tva'] ?? 20);
        
        $line_id = $this->Cart_model->add_to_cart(
            $cart['id'],
            $product_id,
            intval($quantity),
            $prix_ht,
            $tva
        );

        // Test 7 : Totaux
        $totals = $this->Cart_model->update_cart_totals($cart['id']);

        echo json_encode([
            'success' => true,
            'message' => 'Produit ajouté',
            'cart_count' => intval($totals['nombre_articles'])
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage()
        ]);
    }
}
    /**
     * Page du panier
    
    public function panier() {
        $user_id = $this->session->userdata('user_id');
        $visitor_token = get_cookie('visitor_token');
        
        $cart = $this->Cart_model->get_or_create_cart($user_id, $visitor_token);
        $cart_items = [];
        
        if ($cart) {
            $cart_items = $this->Cart_model->get_cart_details($cart['id']);
        }

        $data['cart'] = $cart;
        $data['cart_items'] = $cart_items;
        $data['title'] = 'Mon Panier - AGF Phytomed';
        
        $this->load->view('frontend/panier', $data);
    }
 */














    /**
     * Afficher la page du panier
     */
    public function Panier()
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
        
        $this->load->view('Panier', $data);
    }






    /**
     * Page boutique avec filtres recherche et catégorie
     */
   public function Boutique() {
    // Récupérer les filtres
    $search = trim($this->input->get('q') ?? '');
    $categorie_slug = $this->input->get('categorie') ?? '';
    $sort = $this->input->get('sort') ?? 'new';
    
    // Pagination
    $page = max(1, (int)($this->input->get('page') ?? 1));
    $per_page = 12;
    $offset = ($page - 1) * $per_page;
    
    // Construction requête produits
    $this->db->select('p.*, c.nom as categorie_nom, c.slug as categorie_slug');
    $this->db->from('produits p');
    $this->db->join('categories c', 'c.id = p.categorie_id', 'left');
    $this->db->where('p.est_actif', 1);
    $this->db->where('p.est_disponible', 1);
    
    // Filtre recherche
    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('p.nom', $search);
        $this->db->or_like('p.description_courte', $search);
        $this->db->or_like('p.reference', $search);
        $this->db->or_like('p.marque', $search);
        $this->db->group_end();
    }
    
    // Filtre catégorie
    if (!empty($categorie_slug)) {
        $this->db->where('c.slug', $categorie_slug);
    }
    
    // Tri
    switch($sort) {
        case 'price-asc':
            $this->db->order_by('p.prix_ht', 'ASC');
            break;
        case 'price-desc':
            $this->db->order_by('p.prix_ht', 'DESC');
            break;
        case 'name':
            $this->db->order_by('p.nom', 'ASC');
            break;
        case 'new':
        default:
            $this->db->order_by('p.created_at', 'DESC');
            break;
    }
    
    // Cloner pour count avant le limit
    $count_sql = $this->db->get_compiled_select();
    $total = $this->db->query("SELECT COUNT(*) as total FROM ({$count_sql}) as t")->row()->total;
    
    // Appliquer limit et récupérer résultats
    $this->db->select('p.*, c.nom as categorie_nom, c.slug as categorie_slug');
    $this->db->from('produits p');
    $this->db->join('categories c', 'c.id = p.categorie_id', 'left');
    $this->db->where('p.est_actif', 1);
    $this->db->where('p.est_disponible', 1);
    
    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('p.nom', $search);
        $this->db->or_like('p.description_courte', $search);
        $this->db->or_like('p.reference', $search);
        $this->db->or_like('p.marque', $search);
        $this->db->group_end();
    }
    
    if (!empty($categorie_slug)) {
        $this->db->where('c.slug', $categorie_slug);
    }
    
    switch($sort) {
        case 'price-asc': $this->db->order_by('p.prix_ht', 'ASC'); break;
        case 'price-desc': $this->db->order_by('p.prix_ht', 'DESC'); break;
        case 'name': $this->db->order_by('p.nom', 'ASC'); break;
        default: $this->db->order_by('p.created_at', 'DESC'); break;
    }
    
    $this->db->limit($per_page, $offset);
    $data['produits'] = $this->db->get()->result_array();
    
    // Catégories avec count
    $data['categories'] = $this->db->query("
        SELECT c.*, COUNT(p.id) as nb_produits 
        FROM categories c 
        LEFT JOIN produits p ON p.categorie_id = c.id AND p.est_actif = 1 AND p.est_disponible = 1 
        WHERE c.est_actif = 1 
        GROUP BY c.id 
        ORDER BY c.ordre ASC
    ")->result_array();
    
    // Pagination
    $data['pagination'] = $this->create_custom_pagination($total, $per_page, $page, [
        'q' => $search,
        'categorie' => $categorie_slug,
        'sort' => $sort
    ]);
    
    $data['search'] = $search;
    $data['categorie_active'] = $categorie_slug;
    $data['total_results'] = $total;
    
    $this->load->view('Boutique_View', $data);
}

private function create_custom_pagination($total, $per_page, $current_page, $params = []) {
    $total_pages = ceil($total / $per_page);
    if($total_pages <= 1) return null;
    
    $pages = [];
    $range = 2;
    
    for($i = 1; $i <= $total_pages; $i++) {
        if($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
            $pages[] = $i;
        } elseif(end($pages) != '...') {
            $pages[] = '...';
        }
    }
    
    $base_url = base_url('Home/Boutique');
    $query_parts = [];
    
    foreach($params as $k => $v) {
        if(!empty($v)) $query_parts[$k] = $v;
    }
    
    $page_links = [];
    foreach($pages as $p) {
        if($p != '...') {
            $link_params = $query_parts;
            if($p > 1) $link_params['page'] = $p;
            
            $page_links[$p] = $base_url . (!empty($link_params) ? '?' . http_build_query($link_params) : '');
        }
    }
    
    $prev_link = null;
    $next_link = null;
    
    if($current_page > 1) {
        $prev_params = $query_parts;
        if($current_page - 1 > 1) $prev_params['page'] = $current_page - 1;
        $prev_link = $base_url . (!empty($prev_params) ? '?' . http_build_query($prev_params) : '');
    }
    
    if($current_page < $total_pages) {
        $next_params = $query_parts;
        $next_params['page'] = $current_page + 1;
        $next_link = $base_url . '?' . http_build_query($next_params);
    }
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'pages' => $pages,
        'page_links' => $page_links,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'prev_link' => $prev_link,
        'next_link' => $next_link
    ];
}









    /**
     * Page détail d'un produit
     */
    public function Produit($slug = null) {
    if(empty($slug)) {
        redirect('Home/Boutique');
        return;
    }
    
    // Récupérer le produit avec sa catégorie
    $this->db->select('p.*, c.nom as categorie_nom, c.slug as categorie_slug');
    $this->db->from('produits p');
    $this->db->join('categories c', 'c.id = p.categorie_id', 'left');
    $this->db->where('p.slug', $slug);
    $this->db->where('p.est_actif', 1);
    $query = $this->db->get();
    
    if($query->num_rows() == 0) {
        show_404();
        return;
    }
    
    $data['produit'] = $query->row_array();
    
    // Incrémenter le compteur de vues
    $this->db->where('id', $data['produit']['id']);
    $this->db->set('vue_count', 'vue_count + 1', FALSE);
    $this->db->update('produits');
    
    // Récupérer les images
    $this->db->where('produit_id', $data['produit']['id']);
    $this->db->order_by('est_principale', 'DESC');
    $this->db->order_by('ordre', 'ASC');
    $data['images'] = $this->db->get('produit_images')->result_array();
    
    // Si pas d'images en BDD mais image_principale dans produit
    if(empty($data['images']) && !empty($data['produit']['image_principale'])) {
        $data['images'] = [
            [
                'url' => $data['produit']['image_principale'],
                'est_principale' => 1,
                'legende' => $data['produit']['nom']
            ]
        ];
    }
    
    // Récupérer le workflow
    $this->db->where('produit_id', $data['produit']['id']);
    $this->db->order_by('etape_numero', 'ASC');
    $data['workflow'] = $this->db->get('workflow_produits')->result_array();
    
    // Produits similaires (même catégorie)
    $this->db->select('id, nom, slug, prix_ht, tva, prix_ttc, image_principale, est_en_promo, prix_promo_ht');
    $this->db->where('categorie_id', $data['produit']['categorie_id']);
    $this->db->where('id !=', $data['produit']['id']);
    $this->db->where('est_actif', 1);
    $this->db->limit(4);
    $data['produits_similaires'] = $this->db->get('produits')->result_array();
    
    $this->load->view('Produitsdetail', $data);
}

    

    /**
     * Recherche AJAX pour l'autocomplétion
     */
    public function search_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $query = $this->input->get('q');
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $this->db->select('id, nom, slug, prix_ht, image_principale');
        $this->db->from('produits');
        $this->db->like('nom', $query);
        $this->db->or_like('description_courte', $query);
        $this->db->where('est_actif', 1);
        $this->db->where('est_disponible', 1);
        $this->db->limit(10);
        
        $results = $this->db->get()->result_array();
        
        echo json_encode($results);
    }

    /**
     * Page de commande / panier
     */
    public function Commande1()
    {
        // Vérifier si l'utilisateur est connecté
        // if (!$this->session->userdata('logged_in')) {
        //     redirect('Home/Login?redirect=Commande');
        // }

        $data['page_title'] = 'Validation commande - AGF Phytomed';
        
        $this->load->view('includes/frontend/Header', $data);
        $this->load->view('Commande_View');
        $this->load->view('includes/frontend/Footer');
    }

    /**
     * API: Ajouter au panier (AJAX)
     */
    public function api_add_to_cart()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $produit_id = $this->input->post('produit_id');
        $quantite = $this->input->post('quantite');

        // Vérifier le produit
        $this->db->select('id, nom, prix_ht, stock, est_disponible');
        $this->db->from('produits');
        $this->db->where('id', $produit_id);
        $this->db->where('est_actif', 1);
        
        $produit = $this->db->get()->row_array();

        if (!$produit || !$produit['est_disponible'] || $produit['stock'] < $quantite) {
            echo json_encode(['success' => false, 'message' => 'Produit non disponible']);
            return;
        }

        // Logique panier (à adapter selon votre système)
        // Ici on renvoie juste les infos pour le JS
        echo json_encode([
            'success' => true,
            'produit' => [
                'id' => $produit['id'],
                'nom' => $produit['nom'],
                'prix' => $produit['prix_ht'],
                'quantite' => $quantite
            ]
        ]);
    }

    /**
     * Fonction utilitaire pour créer une pagination manuelle (si besoin)
     */
    private function create_pagination($total, $per_page, $current_page, $params = [])
    {
        $last_page = ceil($total / $per_page);
        
        if ($last_page <= 1) {
            return '';
        }

        $html = '<div class="pagination-wrap">';
        
        // Lien précédent
        if ($current_page > 1) {
            $params['page'] = $current_page - 1;
            $html .= '<a href="' . base_url('Home/Boutique?' . http_build_query($params)) . '" class="page-link"><i class="bi bi-chevron-left"></i></a>';
        }

        // Pages
        $start = max(1, $current_page - 2);
        $end = min($last_page, $current_page + 2);

        for ($i = $start; $i <= $end; $i++) {
            $params['page'] = $i;
            $active = ($i == $current_page) ? 'active' : '';
            $html .= '<a href="' . base_url('Home/Boutique?' . http_build_query($params)) . '" class="page-link ' . $active . '">' . $i . '</a>';
        }

        // Lien suivant
        if ($current_page < $last_page) {
            $params['page'] = $current_page + 1;
            $html .= '<a href="' . base_url('Home/Boutique?' . http_build_query($params)) . '" class="page-link"><i class="bi bi-chevron-right"></i></a>';
        }

        $html .= '</div>';
        
        return $html;
    }



    /**
     * Page Commande/Checkout
     */
    public function Commande() {
        $cart = $this->get_cart_cookie();
        
        if (empty($cart)) {
            $this->session->set_flashdata('error', 'Votre panier est vide');
            redirect('Home/Boutique');
        }

        // Récupérer détails complets des produits
        $produits = [];
        $total_ht = 0;
        $total_ttc = 0;
        
        foreach ($cart as $item) {
            $produit = $this->Model->get_produit_by_id($item['id']);
            if ($produit) {
                $ligne_ht = $produit->prix_ht * $item['qty'];
                $ligne_ttc = $produit->prix_ttc * $item['qty'];
                
                $produits[] = [
                    'produit' => $produit,
                    'quantite' => $item['qty'],
                    'total_ht' => $ligne_ht,
                    'total_ttc' => $ligne_ttc
                ];
                
                $total_ht += $ligne_ht;
                $total_ttc += $ligne_ttc;
            }
        }

        $data['produits'] = $produits;
        $data['total_ht'] = $total_ht;
        $data['total_ttc'] = $total_ttc;
        $data['frais_livraison'] = $total_ttc > 50000 ? 0 : 2500; // Gratuit > 50k
        
        // Si utilisateur connecté, récupérer adresses
        if ($this->session->userdata('user_id')) {
            $this->load->model('Adresse_model');
            $data['adresses'] = $this->Model->get_by_user($this->session->userdata('user_id'));
        }

        $this->load->view('Commande_View', $data);
    }

    /**
     * API: Récupérer panier (pour mises à jour AJAX)
     */
    public function api_get_cart() {
        $cart = $this->get_cart_cookie();
        echo json_encode([
            'items' => array_values($cart),
            'count' => array_sum(array_column($cart, 'qty')),
            'total' => array_sum(array_map(function($i) { return $i['price'] * $i['qty']; }, $cart))
        ]);
    }

    /**
     * API: Modifier quantité dans panier
     */
    public function api_update_cart() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $produit_id = $this->input->post('produit_id');
        $qty = (int) $this->input->post('qty');
        
        $cart = $this->get_cart_cookie();
        
        if ($qty <= 0) {
            unset($cart[$produit_id]);
        } else {
            $cart[$produit_id]['qty'] = $qty;
        }
        
        $this->set_cart_cookie($cart);
        echo json_encode(['success' => true]);
    }

    /**
     * API: Supprimer du panier
     */
    public function api_remove_from_cart() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $produit_id = $this->input->post('produit_id');
        $cart = $this->get_cart_cookie();
        unset($cart[$produit_id]);
        $this->set_cart_cookie($cart);
        
        echo json_encode(['success' => true]);
    }

    // ============ MÉTHODES PRIVÉES COOKIE ============

    /**
     * Récupère le panier depuis cookie sécurisé
     */
    private function get_cart_cookie() {
        $cookie = get_cookie('agf_cart');
        if (!$cookie) return [];
        
        // Déchiffrement simple (à remplacer par vrai chiffrement en production)
        $data = json_decode(base64_decode($cookie), true);
        return is_array($data) ? $data : [];
    }

    /**
     * Sauvegarde le panier dans cookie sécurisé
     */
    private function set_cart_cookie($cart) {
        $value = base64_encode(json_encode($cart));
        
        set_cookie([
            'name'   => 'agf_cart',
            'value'  => $value,
            'expire' => 604800, // 7 jours
            'secure' => TRUE,   // HTTPS only
            'httponly' => TRUE, // Pas accessible JS
            'samesite' => 'Strict'
        ]);
        
        // Mise à jour session pour accès rapide
        $this->session->set_userdata('cart_count', array_sum(array_column($cart, 'qty')));
    }

    private function get_cart_count() {
        return $this->session->userdata('cart_count') ?: 0;
    }

    private function get_cart_total() {
        $cart = $this->get_cart_cookie();
        return array_sum(array_map(function($i) { return $i['price'] * $i['qty']; }, $cart));
    }





    public function Newsletter(){
        $email = $this->input->post('email');

        $data = array(
            'email' => $email
        );

        $rsp = $this->Model->create('newsletter', $data);

        $sms = [];
        if ($rsp) {
            $sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
                             Email ajouté avec succès.
                         </div>';
        } else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
                             <strong>Oups!</strong> Cet email existe déjà ou une erreur est survenue.
                         </div>';
        }

        $this->session->set_flashdata($sms);
        redirect(base_url('Home'));
    }
}

