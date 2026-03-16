<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
        
    }

    /**
     * Page d'accueil dynamique avec sections
     */
    public function index()
    {    
        $this->Model->log_visit();

        // Récupérer la page Home (id=1)
        $data['page'] = $this->Model->readOne('pages', ['id_page' => 1, 'est_publiee' => 1]);
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        
        if (!$data['page']) {
            show_404();
        }



        // Données spécifiques à la home
        $data['slides'] = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
        $data['chiffres'] = $this->Model->read('chiffres_cles', ['id_page_associee' => 1], 'ordre', 'ASC');
        $data['appels_action'] = $this->Model->read('appels_action', NULL, 'ordre','ASC'
                );
        
        // Données communes
        $data = array_merge($data, $this->_get_page_data(1));
        
        // SEO et métadonnées
        $data = array_merge($data, $this->_prepare_seo_data($data['page']));


        $data['meta_title'] = $data['page']['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $data['page']['meta_description'] ?? $this->Model->get_setting('site_description');

        // Chargement de la vue
        $this->load->view('Home_View', $data);
        
    }

    /**
     * Afficher une page dynamique par slug avec gestion intelligente des templates
     */
    public function view($slug = null)
    {
        if (!$slug) {
            redirect('/');
        }

        // Nettoyer le slug
        $slug = $this->security->xss_clean($slug);

        // Récupérer la page par slug
        $data['page'] = $this->Model->readOne('pages', ['slug' => $slug, 'est_publiee' => 1]);

        if (!$data['page']) {
            show_404();
        }

        // Récupérer les données contextuelles selon le type de page
        $data = array_merge($data, $this->_get_page_data($data['page']['id_page']));
        
        // Charger les données spécifiques selon le template ou le type
        $data = array_merge($data, $this->_load_contextual_data($data['page']));

        // SEO et métadonnées
        $data = array_merge($data, $this->_prepare_seo_data($data['page']));

        // Déterminer la vue à charger
        $view_name = $this->_determine_view($data['page']);
        
        $this->load->view($view_name, $data);
    }

    /**
     * Récupérer les données communes à toutes les pages
     */
    private function _get_page_data($page_id)
    {
        $data = [];
        
        // Sections de contenu
        $data['sections'] = $this->Model->read('sections_contenu', 
            ['id_page' => $page_id], 
            'ordre', 
            'ASC'
        );

        // Pages enfants
        $data['children'] = $this->Model->read('pages', 
            ['menu_parent_id' => $page_id, 'est_publiee' => 1], 
            'menu_ordre', 
            'ASC'
        );

        // Frères et sœurs (pour navigation contextuelle)
        $parent_id = $this->Model->readOne('pages', ['id_page' => $page_id])['menu_parent_id'] ?? null;
        if ($parent_id) {
            $data['siblings'] = $this->Model->read('pages', 
                ['menu_parent_id' => $parent_id, 'est_publiee' => 1, 'id_page !=' => $page_id], 
                'menu_ordre', 
                'ASC'
            );
        }

        // Breadcrumb
        $data['breadcrumb'] = $this->_build_breadcrumb($page_id);

        return $data;
    }

    /**
     * Charger les données contextuelles selon le type/template de page
     */
    private function _load_contextual_data($page)
    {
        $data = [];
        $template = $page['template_specifique'] ?? 'default';
        
        switch ($template) {
            case 'blog':
            case 'actualites':
                $data['articles'] = $this->Model->read('actualites_blog', 
                    ['est_publie' => 1], 
                    'date_publication', 
                    'DESC',
                    6 // limit
                );
                break;

            case 'produits':
            case 'catalogue':
                $data['categories'] = $this->Model->read('categories_produits', 
                    ['active' => 1], 
                    'ordre', 
                    'ASC'
                );
                $data['produits'] = $this->Model->read('produits', 
                    ['est_actif' => 1], 
                    'ordre_affichage', 
                    'ASC'
                );
                break;

            case 'equipe':
                $data['membres'] = $this->Model->read('equipe', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'contact':
                $data['pays'] = $this->Model->read('pays', null, 'pays', 'ASC');
                $data['coordonnees'] = $this->Model->get_setting('contact_coordonnees');
                break;

            case 'partenaires':
                $data['partenaires'] = $this->Model->read('partenaires', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'temoignages':
                $data['temoignages'] = $this->Model->read('temoignages', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;




            case 'faq':
                $data['faq_categories'] = $this->Model->read('faq_categories', ['active' => 1]);
                $data['faq'] = $this->Model->read('faq', ['est_publie' => 1], 'ordre', 'ASC');
                break;

            case 'galerie':
                $data['medias'] = $this->Model->read('galerie_medias', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;

            case 'services':
                $data['services'] = $this->Model->read('services', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'evenements':
                $data['evenements'] = $this->Model->read('evenements', 
                    ['date_evenement >=' => date('Y-m-d')], 
                    'date_evenement', 
                    'ASC'
                );
                break;

            case 'ressources':
                $data['ressources'] = $this->Model->read('ressources_telechargeables', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;

            case 'chiffres_cles':
                $data['chiffres'] = $this->Model->read('chiffres_cles', 
                    ['id_page_associee' => $page['id_page']], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'investissement':
                $data['phases'] = $this->Model->read('investissement_phases', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                $data['risques'] = $this->Model->read('risques_mitigations', 
                    ['est_actif' => 1]
                );
                break;

            case 'etapes_projet':
                $data['etapes'] = $this->Model->read('etapes_projet', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'licences':
                $data['licences'] = $this->Model->read('licences_certifications', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'statistiques':
                $data['stats'] = $this->Model->read('statistiques_reseaux', 
                    ['est_public' => 1], 
                    'date_stat', 
                    'DESC',
                    12
                );
                break;

            default:
                // Pour les pages génériques, vérifier si des données sont liées via sections
                $data['chiffres'] = $this->Model->read('chiffres_cles', 
                    ['id_page_associee' => $page['id_page']], 
                    'ordre', 
                    'ASC'
                );
                break;
        }

        return $data;
    }

    /**
     * Déterminer quelle vue charger selon le template
     */
    private function _determine_view($page)
    {
        $template = $page['template_specifique'] ?? 'default';
        
        // Vérifier si une vue spécifique existe
        $specific_view = 'templates/' . $template;
        if (file_exists(APPPATH . 'views/' . $specific_view . '.php')) {
            return $specific_view;
        }
        
        // Sinon, utiliser la vue générique dynamique
        return 'Home_View';
    }

    /**
     * Préparer les données SEO
     */
    private function _prepare_seo_data($page)
    {
        $site_name = $this->Model->get_setting('agf_nom_complet', 'AGF Phytomed');
        $site_description = $this->Model->get_setting('site_description', 'Pionniers de la phytothérapie africaine');
        
        return [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'meta_title' => (!empty($page['meta_title']) ? $page['meta_title'] : $page['titre_page']) . ' - ' . $site_name,
            'meta_description' => !empty($page['meta_description']) ? $page['meta_description'] : $site_description,
            'meta_keywords' => $page['meta_keywords'] ?? '',
            'og_image' => !empty($page['image_social']) ? base_url($page['image_social']) : base_url('assets/images/og-default.jpg'),
            'canonical_url' => base_url($page['slug'])
        ];
    }

    /**
     * Construire le fil d'Ariane
     */
    private function _build_breadcrumb($page_id)
    {
        $breadcrumb = [];
        
        while ($page_id) {
            $page = $this->Model->readOne('pages', ['id_page' => $page_id]);
            if (!$page) break;
            
            array_unshift($breadcrumb, [
                'titre' => $page['titre_page'],
                'slug' => $page['slug'],
                'url' => $page['slug'] === 'home' ? base_url() : base_url($page['slug'])
            ]);
            
            $page_id = $page['menu_parent_id'];
        }
        
        return $breadcrumb;
    }

    /**
     * API pour charger plus de contenu (AJAX)
     */
    public function load_more()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type');
        $page = (int) $this->input->post('page', 1);
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $data = [];
        
        switch ($type) {
            case 'actualites':
                $data['items'] = $this->Model->read('actualites_blog', 
                    ['est_publie' => 1], 
                    'date_publication', 
                    'DESC',
                    $limit,
                    $offset
                );
                break;
                
            case 'produits':
                $data['items'] = $this->Model->read('produits', 
                    ['est_actif' => 1], 
                    'ordre_affichage', 
                    'ASC',
                    $limit,
                    $offset
                );
                break;
        }

        $this->output->set_content_type('application/json');
        echo json_encode($data);
    }

    /**
     * Soumission de formulaire de contact
     */
    public function submit_contact()
    {
        if ($this->input->method() !== 'post') {
            redirect('/');
        }

        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('message', 'Message', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($this->input->server('HTTP_REFERER'));
        }

        // Traitement du formulaire...
        $contact_data = [
            'nom' => $this->input->post('nom'),
            'email' => $this->input->post('email'),
            'telephone' => $this->input->post('telephone'),
            'sujet' => $this->input->post('sujet'),
            'message' => $this->input->post('message'),
            'date_creation' => date('Y-m-d H:i:s'),
            'est_lu' => 0
        ];

        $this->Model->create('contacts', $contact_data);
        
        $this->session->set_flashdata('success', 'Votre message a été envoyé avec succès.');
        redirect($this->input->server('HTTP_REFERER'));
    }
























    /**
     * Génère un token visiteur unique
     */
    private function generate_visitor_token() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Récupère ou crée un panier
     */
    private function get_or_create_cart($user_id = null, $visitor_token = null) {
        $this->db->where('est_actif', 1);
        
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        } elseif ($visitor_token) {
            $this->db->where('visitor_token', $visitor_token);
        } else {
            return false;
        }

        $cart = $this->db->get('paniers')->row_array();

        if ($cart) {
            return $cart;
        }

        // Créer nouveau panier
        $data = [
            'user_id' => $user_id ?: 0,
            'visitor_token' => $visitor_token,
            'total_ht' => 0.00,
            'total_ttc' => 0.00,
            'nombre_articles' => 0,
            'est_actif' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('paniers', $data);
        $data['id'] = $this->db->insert_id();
        
        return $data;
    }

    /**
     * Récupère un produit par ID
     
    private function get_product($product_id) {
        return $this->db->where('id', $product_id)
                        ->where('est_actif', 1)
                        ->where('est_disponible', 1)
                        ->get('produits')
                        ->row_array();
    }*/

    /**
     * Ajoute ou met à jour une ligne dans le panier
     
    private function add_cart_line($cart_id, $product_id, $quantity, $prix_unitaire_ht, $taux_tva) {
        // Vérifier si existe déjà
        $existing = $this->db->where('panier_id', $cart_id)
                             ->where('produit_id', $product_id)
                             ->get('panier_lignes')
                             ->row_array();

        if ($existing) {
            // Mettre à jour quantité
            $new_qty = $existing['quantite'] + $quantity;
            $this->db->where('id', $existing['id'])
                     ->update('panier_lignes', [
                         'quantite' => $new_qty,
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);
            return $existing['id'];
        } else {
            // Nouvelle ligne
            $data = [
                'panier_id' => $cart_id,
                'produit_id' => $product_id,
                'quantite' => $quantity,
                'prix_unitaire_ht' => $prix_unitaire_ht,
                'taux_tva' => $taux_tva,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('panier_lignes', $data);
            return $this->db->insert_id();
        }
    }
*/
    /**
     * Recalcule les totaux du panier
    
    private function update_cart_totals($cart_id) {
        $this->db->select('SUM(quantite * prix_unitaire_ht) as total_ht');
        $this->db->select('SUM(quantite * prix_unitaire_ht * (1 + taux_tva / 100)) as total_ttc');
        $this->db->select('SUM(quantite) as nombre_articles');
        $this->db->where('panier_id', $cart_id);
        
        $result = $this->db->get('panier_lignes')->row_array();

        $update_data = [
            'total_ht' => $result['total_ht'] ?: 0.00,
            'total_ttc' => $result['total_ttc'] ?: 0.00,
            'nombre_articles' => $result['nombre_articles'] ?: 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $cart_id)->update('paniers', $update_data);
        
        return $update_data;
    } */

    /**
     * Récupère les détails du panier avec produits
    
    private function get_cart_details($cart_id) {
        $this->db->select('pl.*, p.nom, p.slug, p.image_principale, p.stock, p.unite_vente, p.reference');
        $this->db->from('panier_lignes pl');
        $this->db->join('produits p', 'p.id = pl.produit_id');
        $this->db->where('pl.panier_id', $cart_id);
        
        return $this->db->get()->result_array();
    } */

    // ============================================================
    // PAGES ET MÉTHODES PUBLIQUES
    // ============================================================

    /* public function index() {   
        $data['pays'] = $this->Model->read('pays', null, 'pays', 'ASC');

        PRODUITS POPULAIRES
        $sql_produits = "SELECT p.*, 
                                COUNT(DISTINCT cl.id) as nombre_ventes,
                                AVG(pa.note) as note_moyenne_calculee
                        FROM produits p
                        LEFT JOIN commande_lignes cl ON p.id = cl.produit_id
                        LEFT JOIN commandes c ON cl.commande_id = c.id AND c.statut = 'livree'
                        LEFT JOIN produit_avis pa ON p.id = pa.produit_id AND pa.est_valide = 1
                        WHERE p.est_actif = 1
                        GROUP BY p.id
                        ORDER BY nombre_ventes DESC, note_moyenne_calculee DESC
                        LIMIT 8";
        
        $data['produits_populaires'] = $this->Model->query($sql_produits);
        
        // MÉDECINS DISPONIBLES
        $sql_medecins = "SELECT m.*, u.nom, u.prenom, u.photo
                        FROM medecins m
                        INNER JOIN users u ON m.user_id = u.id
                        WHERE m.est_disponible = 1 AND u.is_active = 1
                        ORDER BY m.note_moyenne DESC
                        LIMIT 5";
        
        $data['medecins_disponibles'] = $this->Model->query($sql_medecins);
        
        
        
        // TÉMOIGNAGES
        $sql_temoignages = "SELECT pa.*, u.nom, u.prenom, u.photo, 'Client' as type_utilisateur
                           FROM produit_avis pa
                           INNER JOIN users u ON pa.user_id = u.id
                           WHERE pa.est_valide = 1
                           ORDER BY pa.created_at DESC
                           LIMIT 5";
        
        $data['temoignages'] = $this->Model->query($sql_temoignages);
        
        $this->Model->log_visit();

        $data['slides'] = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
       // $data['produits'] = $this->Model->read('produits', ['est_actif' => 1, 'est_disponible' => 1], 'nombre_ventes', 'DESC', 3);
        
        $this->load->view('Home_View', $data);
    }*/

    public function Overview() {
        $this->load->view('Trategic_Overview_View');
    }

    public function Contact() {
        $this->load->view('Contact_View');
    }

    public function Command() {
        $this->load->view('commande_View');
    }

    /**
     * AJAX: Ajouter au panier
     
    public function ajouter_au_panier() {
        header('Content-Type: application/json');
        
        try {
            $product_id = $this->input->post('product_id');
            $quantity = intval($this->input->post('quantity') ?? 1);

            if (empty($product_id)) {
                echo json_encode(['success' => false, 'message' => 'ID produit requis']);
                return;
            }

            // Vérifier produit
            $product = $this->get_product($product_id);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Produit non trouvé ou indisponible']);
                return;
            }

            // Vérifier stock
            if ($product['stock'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Stock insuffisant. Disponible: ' . $product['stock']]);
                return;
            }

            // Gestion user/visitor
            $user_id = $this->session->userdata('user_id');
            $visitor_token = get_cookie('visitor_token');

            if (!$user_id && !$visitor_token) {
                $visitor_token = $this->generate_visitor_token();
                set_cookie([
                    'name' => 'visitor_token',
                    'value' => $visitor_token,
                    'expire' => 86400 * 30,
                    'secure' => FALSE,
                    'httponly' => TRUE
                ]);
            }

            // Créer/récupérer panier
            $cart = $this->get_or_create_cart($user_id, $visitor_token);
            if (!$cart) {
                echo json_encode(['success' => false, 'message' => 'Erreur création panier']);
                return;
            }

            // Calculer prix (avec promo si applicable)
            $prix_ht = floatval($product['prix_ht']);
            $tva = floatval($product['tva'] ?? 20);

            if (!empty($product['est_en_promo']) && !empty($product['prix_promo_ht'])) {
                $now = date('Y-m-d H:i:s');
                if ((!$product['date_debut_promo'] || $product['date_debut_promo'] <= $now) &&
                    (!$product['date_fin_promo'] || $product['date_fin_promo'] >= $now)) {
                    $prix_ht = floatval($product['prix_promo_ht']);
                }
            }

            // Ajouter au panier
            $this->add_cart_line($cart['id'], $product_id, $quantity, $prix_ht, $tva);

            // Mettre à jour totaux
            $totals = $this->update_cart_totals($cart['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart_count' => intval($totals['nombre_articles']),
                'cart_total' => number_format($totals['total_ttc'], 0, ',', ' ') . ' BIF'
            ]);

        } catch (Exception $e) {
            log_message('error', 'ajouter_au_panier: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
*/
    /**
     * Page du panier
     
    public function Panier() {
        $user_id = $this->session->userdata('user_id');
        $visitor_token = get_cookie('visitor_token');

        if (!$user_id && !$visitor_token) {
            $visitor_token = $this->generate_visitor_token();
            set_cookie([
                'name' => 'visitor_token',
                'value' => $visitor_token,
                'expire' => 86400 * 30,
                'secure' => FALSE,
                'httponly' => TRUE
            ]);
        }
        
        $cart = $this->get_or_create_cart($user_id, $visitor_token);
        
        $cart_items = [];
        $totals = [
            'total_ht' => 0,
            'total_ttc' => 0,
            'total_tva' => 0,
            'nombre_articles' => 0
        ];
        
        if ($cart) {
            $cart_items = $this->get_cart_details($cart['id']);
            $totals['total_ht'] = $cart['total_ht'];
            $totals['total_ttc'] = $cart['total_ttc'];
            $totals['total_tva'] = $cart['total_ttc'] - $cart['total_ht'];
            $totals['nombre_articles'] = $cart['nombre_articles'];
        }
        
        $shipping_ht = 113.00;
        $shipping_ttc = $shipping_ht * 1.055;
        
        $data = [
            'cart_items' => $cart_items,
            'totals' => $totals,
            'shipping_ht' => $shipping_ht,
            'shipping_ttc' => $shipping_ttc,
            'grand_total_ht' => $totals['total_ht'] + $shipping_ht,
            'grand_total_ttc' => $totals['total_ttc'] + $shipping_ttc,
            'panier_id' => $cart['id'] ?? 0,
            'visitor_token' => $visitor_token
        ];
        
        $this->load->view('Panier', $data);
    }
*/
    /**
     * Page boutique
    
    public function Boutique() {
        $search = trim($this->input->get('q') ?? '');
        $categorie_slug = $this->input->get('categorie') ?? '';
        $sort = $this->input->get('sort') ?? 'new';
        
        $page = max(1, (int)($this->input->get('page') ?? 1));
        $per_page = 12;
        $offset = ($page - 1) * $per_page;
        
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
        
        $count_sql = $this->db->get_compiled_select();
        $total = $this->db->query("SELECT COUNT(*) as total FROM ({$count_sql}) as t")->row()->total;
        
        // Requête finale
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
        
        $data['categories'] = $this->db->query("
            SELECT c.*, COUNT(p.id) as nb_produits 
            FROM categories c 
            LEFT JOIN produits p ON p.categorie_id = c.id AND p.est_actif = 1 AND p.est_disponible = 1 
            WHERE c.est_actif = 1 
            GROUP BY c.id 
            ORDER BY c.ordre ASC
        ")->result_array();
        
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
 */
    /**
     * Page détail produit
    
    public function Produit($slug = null) {
        if(empty($slug)) {
            redirect('Home/Boutique');
            return;
        }
        
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
        
        $this->db->where('id', $data['produit']['id']);
        $this->db->set('vue_count', 'vue_count + 1', FALSE);
        $this->db->update('produits');
        
        $this->db->where('produit_id', $data['produit']['id']);
        $this->db->order_by('est_principale', 'DESC');
        $this->db->order_by('ordre', 'ASC');
        $data['images'] = $this->db->get('produit_images')->result_array();
        
        if(empty($data['images']) && !empty($data['produit']['image_principale'])) {
            $data['images'] = [
                [
                    'url' => $data['produit']['image_principale'],
                    'est_principale' => 1,
                    'legende' => $data['produit']['nom']
                ]
            ];
        }
        
        $this->db->where('produit_id', $data['produit']['id']);
        $this->db->order_by('etape_numero', 'ASC');
        $data['workflow'] = $this->db->get('workflow_produits')->result_array();
        
        $this->db->select('id, nom, slug, prix_ht, tva, prix_ttc, image_principale, est_en_promo, prix_promo_ht');
        $this->db->where('categorie_id', $data['produit']['categorie_id']);
        $this->db->where('id !=', $data['produit']['id']);
        $this->db->where('est_actif', 1);
        $this->db->limit(4);
        $data['produits_similaires'] = $this->db->get('produits')->result_array();
        
        $this->load->view('Produitsdetail', $data);
    }
 */
    /**
     * Recherche AJAX
     
    public function search_ajax() {
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
*/
    /**
     * Page commande
     
    public function Commande() {
        $user_id = $this->session->userdata('user_id');
        $visitor_token = get_cookie('visitor_token');
        
        $cart = $this->get_or_create_cart($user_id, $visitor_token);
        
        if (!$cart || $cart['nombre_articles'] == 0) {
            $this->session->set_flashdata('error', 'Votre panier est vide');
            redirect('Home/Boutique');
        }

        $cart_items = $this->get_cart_details($cart['id']);
        
        $total_ht = $cart['total_ht'];
        $total_ttc = $cart['total_ttc'];
        
        $data['produits'] = $cart_items;
        $data['total_ht'] = $total_ht;
        $data['total_ttc'] = $total_ttc;
        $data['frais_livraison'] = $total_ttc > 50000 ? 0 : 2500;
        
        $this->load->view('Commande_View', $data);
    }

    /**
     * API: Modifier quantité panier
     
    public function api_update_cart() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $ligne_id = $this->input->post('ligne_id');
        $qty = (int) $this->input->post('qty');
        
        if ($qty <= 0) {
            $this->db->where('id', $ligne_id)->delete('panier_lignes');
        } else {
            $this->db->where('id', $ligne_id)->update('panier_lignes', ['quantite' => $qty]);
        }
        
        // Recalculer totaux
        $this->db->select('panier_id')->where('id', $ligne_id);
        $line = $this->db->get('panier_lignes')->row_array();
        
        if ($line) {
            $this->update_cart_totals($line['panier_id']);
        }
        
        echo json_encode(['success' => true]);
    }

   
    
    public function api_remove_from_cart() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $ligne_id = $this->input->post('ligne_id');
        
        $this->db->select('panier_id')->where('id', $ligne_id);
        $line = $this->db->get('panier_lignes')->row_array();
        
        $this->db->where('id', $ligne_id)->delete('panier_lignes');
        
        if ($line) {
            $this->update_cart_totals($line['panier_id']);
        }
        
        echo json_encode(['success' => true]);
    }

    /**
     * Newsletter
     */
 
    
    /**
     * Sauvegarde un nouvel abonné (email ou téléphone)
     */
    public function Abonner() {
        // Vérifier si c'est une requête AJAX
        $is_ajax = $this->input->is_ajax_request();
        
        // Déterminer le type d'inscription
        $type = $this->input->post('email') ? 'email' : 'phone';
        
        if ($type === 'email') {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[newsletter.email]', [
                'required' => 'L\'adresse email est requise',
                'valid_email' => 'Veuillez entrer une adresse email valide',
                'is_unique' => 'Cette adresse email est déjà inscrite'
            ]);
            
            if ($this->form_validation->run() === FALSE) {
                $error = validation_errors();
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(['success' => false, 'message' => strip_tags($error)]));
                    return;
                }
                $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert">' . $error . '</div>');
                redirect(base_url('Home'));
                return;
            }
            
            $data = [
                'type' => 'email',
                'email' => strtolower(trim($this->input->post('email'))),
                'telephone' => NULL,
                'indicatif' => NULL,
                'pays_code' => NULL,
                'est_actif' => 1
            ];
            
        } else {
            // Validation téléphone
            $this->form_validation->set_rules('pays_code', 'Pays', 'required', [
                'required' => 'Veuillez sélectionner un pays'
            ]);
            $this->form_validation->set_rules('telephone', 'Téléphone', 'required|min_length[8]|is_unique[newsletter.telephone]', [
                'required' => 'Le numéro de téléphone est requis',
                'min_length' => 'Le numéro doit contenir au moins 8 chiffres',
                'is_unique' => 'Ce numéro de téléphone est déjà inscrit'
            ]);
            
            if ($this->form_validation->run() === FALSE) {
                $error = validation_errors();
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(['success' => false, 'message' => strip_tags($error)]));
                    return;
                }
                $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert">' . $error . '</div>');
                redirect(base_url('Home'));
                return;
            }
            
            // Nettoyer le numéro (garder uniquement les chiffres)
            $telephone = preg_replace('/[^0-9]/', '', $this->input->post('telephone'));
            $indicatif = $this->input->post('indicatif_complet');
            
            $data = [
                'type' => 'phone',
                'email' => NULL,
                'telephone' => $telephone,
                'indicatif' => $indicatif,
                'pays_code' => strtoupper($this->input->post('pays_code')),
                'est_actif' => 1
            ];
        }
        
        // Tentative d'insertion
        try {
            $insert_id = $this->Model->create('newsletter', $data);
            
            if ($insert_id) {
                $message = ($type === 'email') 
                    ? 'Votre email a été ajouté avec succès !' 
                    : 'Votre numéro de téléphone a été ajouté avec succès !';
                
                // Log de l'activité
                log_message('info', 'Newsletter subscription: ID ' . $insert_id . ', Type: ' . $type);
                
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode([
                                     'success' => true, 
                                     'message' => $message,
                                     'id' => $insert_id
                                 ]));
                    return;
                }
                
                $this->session->set_flashdata('success', '<div class="alert alert-success fade show mt-1 message" role="alert"><i class="bi bi-check-circle-fill me-2"></i>' . $message . '</div>');
            } else {
                throw new Exception('Erreur lors de l\'insertion');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Newsletter subscription failed: ' . $e->getMessage());
            
            $error_msg = 'Une erreur est survenue. Veuillez réessayer.';
            
            if ($is_ajax) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(['success' => false, 'message' => $error_msg]));
                return;
            }
            
            $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Oups!</strong> ' . $error_msg . '</div>');
        }
        
        if (!$is_ajax) {
            redirect(base_url('Home'));
        }
    }
    
    /**
     * Désinscription de la newsletter
     */
    public function unsubscribe($token = null) {
        if (!$token) {
            show_404();
            return;
        }
        
        // Décoder le token (email ou téléphone encodé en base64)
        $contact = base64_decode($token);
        
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $this->Model->update('newsletter', 
                ['email' => $contact], 
                ['est_actif' => 0, 'date_desinscription' => date('Y-m-d H:i:s')]
            );
        } else {
            $this->Model->update('newsletter', 
                ['telephone' => $contact], 
                ['est_actif' => 0, 'date_desinscription' => date('Y-m-d H:i:s')]
            );
        }
        
        $this->session->set_flashdata('success', 'Vous avez été désinscrit avec succès.');
        redirect(base_url('Home'));
    }
    
    /**
     * Liste des inscriptions (pour admin)
     */
    public function liste() {
        // Vérifier les permissions admin
        if (!$this->session->userdata('role_id') || $this->session->userdata('role_id') > 2) {
            show_error('Accès non autorisé', 403);
            return;
        }
        
        $data['subscribers'] = $this->Model->getAll('newsletter', [], 'date_inscription DESC');
        $data['total_email'] = $this->Model->count('newsletter', ['type' => 'email', 'est_actif' => 1]);
        $data['total_phone'] = $this->Model->count('newsletter', ['type' => 'phone', 'est_actif' => 1]);
        
        $this->load->view('admin/newsletter/liste', $data);
    }

}
