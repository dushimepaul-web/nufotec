<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - African Green Farmers
 * 
 * Gère les tableaux de bord pour les administrateurs et les médecins.
 */
class Dashboard extends MY_Controller {

    private $theme_colors = [
        'primary'   => '#062C54',
        'secondary' => '#1a8c78',
        'success'   => '#0F766E',
        'warning'   => '#FF8C00',
        'danger'    => '#DC143C',
        'info'      => '#FFD000',
        'light'     => '#f8f9fa',
        'dark'      => '#212529',
        'NUFOTEC_green' => '#2E7D32',
        'NUFOTEC_blue'  => '#01579B'
    ];

    private $cache_duration = 300; // 5 minutes

    public function __construct() {
        parent::__construct();
        
        $this->load->helper(['security', 'text', 'date', 'number']);
        $this->load->library(['user_agent', 'form_validation', 'upload']);
        
        // Vérifier si cache library est disponible
        if (file_exists(APPPATH . 'libraries/Cache.php')) {
            $this->load->library('cache');
        }
        
        $this->_update_user_activity();
    }

    /**
     * Redirige vers le dashboard approprié selon le rôle
     */
    public function index() {
        $role_slug = $this->session->userdata('role_slug');

        $dashboard_map = [
            'admin'   => 'admin_dashboard',
            'medecin' => 'medecin_dashboard',
        ];
          
        if (!isset($dashboard_map[$role_slug])) {
            redirect('Admin');
        }

        $method = $dashboard_map[$role_slug];
        $this->$method();
    }

    // ========================================================================
    // DASHBOARD ADMINISTRATEUR
    // ========================================================================

    public function admin_dashboard() {
        if (!$this->is_admin()) {
            redirect('Admin');
        }

        $data = [
            'page_title'       => 'Tableau de Bord NUFOTEC - Administration',
            'user_role'        => $this->session->userdata('role_nom'),
            'theme_colors'     => $this->theme_colors,
            'current_date'     => date('d/m/Y H:i'),
            'last_update'      => date('H:i:s'),
        ];

        try {
            $data['global_stats']       = $this->_get_global_stats();
            $data['facility_stats']     = $this->_get_facility_stats();
            $data['financial_metrics']  = $this->_get_financial_metrics();
            $data['visitor_analytics']  = $this->_get_visitor_analytics();
            $data['ecommerce_stats']    = $this->_get_ecommerce_stats();
            $data['telemedecine_stats'] = $this->_get_telemedecine_stats();
            $data['investment_stats']   = $this->_get_investment_stats();
            $data['charts_data']        = $this->_get_charts_data();
            $data['recent_activities']  = $this->_get_recent_activities(10);
            $data['system_alerts']      = $this->_get_system_alerts();
            $data['quick_actions']      = $this->_get_quick_actions();
            $data['latest_users']       = $this->_get_latest_users(8);
            $data['latest_orders']      = $this->_get_latest_orders(8);
            $data['latest_consultations'] = $this->_get_latest_consultations(5);
            $data['low_stock_products'] = $this->_get_low_stock_products(6);
            $data['pending_verifications'] = $this->_get_pending_verifications();

        } catch (Exception $e) {
            log_message('error', 'Dashboard Error: ' . $e->getMessage());
            $data['error_message'] = 'Erreur de chargement des données.';
        }

        $this->load->view('admin', $data);
    }

    // ========================================================================
    // DASHBOARD MÉDECIN
    // ========================================================================

    public function medecin_dashboard() {
        if (!$this->is_medecin()) {
            redirect('Admin');
        }

        $user_id = $this->session->userdata('user_id');
        
        // Récupérer les informations du médecin
        $medecin = $this->db
            ->select('m.*, u.prenom, u.nom, u.email, u.telephone, u.photo')
            ->from('medecins m')
            ->join('users u', 'm.user_id = u.id')
            ->where('m.user_id', $user_id)
            ->get()
            ->row();
        
        if (!$medecin) {
            redirect('Admin');
        }

        // Statistiques des consultations
        $today = date('Y-m-d');
        
        $data = [
            'page_title' => 'Mon Espace Médecin',
            'medecin'    => $medecin,
            'stats' => [
                'today_appointments'   => $this->db->where('medecin_id', $medecin->id)
                                            ->where('DATE(date_souhaitee)', $today)
                                            ->count_all_results('consultations'),
                'pending_appointments' => $this->db->where('medecin_id', $medecin->id)
                                            ->where('statut', 'en_attente')
                                            ->count_all_results('consultations'),
                'total_patients'       => $this->db->where('medecin_id', $medecin->id)
                                            ->count_all_results('consultations'),
                'completed'            => $this->db->where('medecin_id', $medecin->id)
                                            ->where('statut', 'terminee')
                                            ->count_all_results('consultations'),
                'total_revenue'        => $this->db->select('COALESCE(SUM(honoraires_consultation), 0) as total')
                                            ->where('medecin_id', $medecin->id)
                                            ->where('statut', 'terminee')
                                            ->get('consultations')
                                            ->row()
                                            ->total
            ],
            'upcoming' => $this->db->where('medecin_id', $medecin->id)
                                    ->where('date_souhaitee >=', date('Y-m-d H:i:s'))
                                    ->where_in('statut', ['confirmee', 'en_attente'])
                                    ->order_by('date_souhaitee', 'ASC')
                                    ->limit(10)
                                    ->get('consultations')
                                    ->result_array(),
            'recent_patients' => $this->db->select('c.*, u.prenom, u.nom, u.telephone')
                                    ->from('consultations c')
                                    ->join('users u', 'c.patient_id = u.id')
                                    ->where('c.medecin_id', $medecin->id)
                                    ->order_by('c.created_at', 'DESC')
                                    ->limit(10)
                                    ->get()
                                    ->result_array()
        ];

        $this->load->view('medecin', $data);
    }

    // ========================================================================
    // AUTRES MÉTHODES (protégées pour les admins)
    // ========================================================================

    public function moderator_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $data['page_title'] = 'Tableau de Bord Modérateur';
        $data['stats'] = [
            'pending_actualites' => $this->db->where('est_publiee', 0)->count_all_results('actualites_blog'),
            'pending_faq'        => $this->db->where('est_publiee', 0)->count_all_results('faq')
        ];
        $this->load->view('backend/dashboard/moderator_dashboard', $data);
    }

    public function investisseur_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $data = [
            'page_title' => 'Opportunités d\'Investissement',
            'phases' => $this->db->get('investissement_phases')->result_array()
        ];
        $this->load->view('backend/dashboard/investisseur_dashboard', $data);
    }

    public function broker_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $data['page_title'] = 'Tableau de Bord Courtier';
        $data['message'] = 'Fonctionnalité en construction.';
        $this->load->view('backend/dashboard/broker_dashboard', $data);
    }

    public function entreprise_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $user_id = $this->session->userdata('user_id');
        $data = [
            'page_title' => 'Espace Entreprise',
            'orders' => $this->db->where('user_id', $user_id)
                                ->order_by('created_at', 'DESC')
                                ->limit(10)
                                ->get('commandes')
                                ->result_array(),
            'stats' => [
                'total_orders' => $this->db->where('user_id', $user_id)->count_all_results('commandes')
            ]
        ];
        $this->load->view('backend/dashboard/entreprise_dashboard', $data);
    }

    public function patient_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $user_id = $this->session->userdata('user_id');
        $data = [
            'page_title' => 'Mon Espace Santé',
            'upcoming_consultations' => $this->db->where('patient_id', $user_id)
                                            ->where_in('statut', ['confirmee', 'en_attente'])
                                            ->order_by('date_souhaitee', 'ASC')
                                            ->get('consultations')
                                            ->result_array(),
            'history' => $this->db->where('patient_id', $user_id)
                                ->where('statut', 'terminee')
                                ->order_by('date_fin', 'DESC')
                                ->limit(5)
                                ->get('consultations')
                                ->result_array()
        ];
        $this->load->view('patient', $data);
    }

    public function user_dashboard() {
        if (!$this->is_admin()) {
            redirect('Dashboard');
        }
        $user_id = $this->session->userdata('user_id');
        $data = [
            'page_title' => 'Mon Tableau de Bord',
            'user' => $this->db->where('id', $user_id)->get('users')->row_array(),
            'recent_orders' => $this->db->where('user_id', $user_id)
                                    ->order_by('created_at', 'DESC')
                                    ->limit(5)
                                    ->get('commandes')
                                    ->result_array(),
            'favorites' => $this->db->select('p.*')
                                    ->from('favoris f')
                                    ->join('produits p', 'f.produit_id = p.id_produit')
                                    ->where('f.user_id', $user_id)
                                    ->get()
                                    ->result_array()
        ];
        $this->load->view('backend/dashboard/user_dashboard', $data);
    }

    // ========================================================================
    // MÉTHODES DE RÉCUPÉRATION DE DONNÉES
    // ========================================================================

    private function _get_global_stats() {
        $cache_key = 'global_stats_' . date('YmdH');
        if (isset($this->cache) && $cached = $this->cache->get($cache_key)) {
            return $cached;
        }

        // Statistiques des utilisateurs
        $total_users = $this->db->where('deleted_at IS NULL', null, false)->count_all_results('users');
        $active_users = $this->db->where('is_active', 1)->where('deleted_at IS NULL', null, false)->count_all_results('users');
        $verified_users = $this->db->where('email_verified_at IS NOT NULL', null, false)->count_all_results('users');
        
        // Utilisateurs inscrits aujourd'hui
        $today = date('Y-m-d');
        $today_users = $this->db->where('DATE(created_at)', $today)
                                ->where('deleted_at IS NULL', null, false)
                                ->count_all_results('users');

        $stats = [
            'users' => [
                'total'    => $total_users,
                'today'    => $today_users,
                'active'   => $active_users,
                'verified' => $verified_users,
                'by_type'  => $this->_get_users_by_type()
            ],
            'medecins' => [
                'total'     => $this->db->count_all_results('medecins'),
                'available' => $this->db->where('est_disponible', 1)->count_all_results('medecins'),
                'avg_rating'=> $this->db->select('COALESCE(AVG(note_moyenne), 0) as avg')->get('medecins')->row()->avg,
                'consultations_today' => $this->_count_today('consultations', 'date_souhaitee')
            ],
            'produits' => [
                'total'        => $this->db->where('est_actif', 1)->count_all_results('produits'),
                'out_of_stock' => 0,
                'low_stock'    => 0,
                'categories'   => $this->db->count_all_results('categories')
            ],
            'commandes' => [
                'total'      => $this->db->count_all_results('commandes'),
                'today'      => $this->_count_today('commandes'),
                'pending'    => $this->db->where('statut', 'en_attente')->count_all_results('commandes'),
                'processing' => $this->db->where('statut', 'preparation')->count_all_results('commandes'),
                'shipped'    => $this->db->where('statut', 'expediee')->count_all_results('commandes'),
                'delivered'  => $this->db->where('statut', 'livree')->count_all_results('commandes')
            ],
            'sessions' => [
                'active_now'   => $this->db->where('is_active', 1)
                                        ->where('last_activity >=', date('Y-m-d H:i:s', strtotime('-15 minutes')))
                                        ->count_all_results('user_sessions'),
                'unique_today' => $this->db->where('visit_date', $today)->count_all_results('visitors_logs')
            ],
            'content' => [
                'actualites' => $this->db->count_all_results('actualites_blog'),
                'pages'      => $this->db->where('est_publiee', 1)->count_all_results('pages'),
                'evenements' => $this->db->where('est_public', 1)->where('date_debut >=', date('Y-m-d'))->count_all_results('evenements'),
                'documents'  => $this->db->where('est_public', 1)->count_all_results('ressources_telechargeables'),
                'faq'        => $this->db->where('est_publiee', 1)->count_all_results('faq')
            ]
        ];

        if (isset($this->cache)) {
            $this->cache->save($cache_key, $stats, $this->cache_duration);
        }
        return $stats;
    }

    private function _get_facility_stats() {
        $total_area = $this->db->select_sum('area_m2')
                              ->where('node_level', 4)
                              ->get('facility_tree')
                              ->row()
                              ->area_m2 ?? 0;
        $hectares = round($total_area / 10000, 2);

        $config = $this->_get_NUFOTEC_config();

        return [
            'superficie_hectares' => $hectares ?: ($config['superficie_hectares'] ?? 50),
            'capacite_production' => $config['capacite_production_tonnes'] ?? 10000,
            'cold_storage'        => $this->db->where('storage_type', 'cold_room')->count_all_results('facility_tree') . ' espaces',
            'lab_equipment'       => true,
            'zoning_compliance'   => true,
            'investissement_requis' => $config['investissement_requis'] ?? 5000000,
            'roi_estime'          => $config['roi_estime'] ?? 25
        ];
    }

    private function _get_NUFOTEC_config() {
        $configs = $this->db->where('categorie', 'NUFOTEC_identity')
                            ->or_where('categorie', 'NUFOTEC_facility')
                            ->or_where('categorie', 'NUFOTEC_finance')
                            ->or_where('categorie', 'contact')
                            ->get('configurations')
                            ->result_array();
        
        $result = [];
        foreach ($configs as $config) {
            $result[$config['cle']] = $config['valeur'];
        }
        return $result;
    }

    private function _get_financial_metrics() {
        // Statistiques des commandes
        $order_stats = $this->db->query("
            SELECT 
                COALESCE(SUM(total_ttc), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN statut = 'livree' THEN total_ttc ELSE 0 END), 0) as confirmed_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_ttc ELSE 0 END), 0) as today_revenue,
                COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_ttc ELSE 0 END), 0) as month_revenue,
                COUNT(*) as total_orders,
                AVG(total_ttc) as avg_order_value
            FROM commandes
            WHERE statut != 'annulee'
        ")->row_array();

        // Statistiques des consultations
        $consultation_stats = $this->db->query("
            SELECT 
                COALESCE(SUM(prix_ttc), 0) as total_revenue,
                COUNT(*) as total_consultations,
                COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as completed_consultations,
                COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as pending_consultations
            FROM consultations
            WHERE statut NOT IN ('annulee', 'refusee')
        ")->row_array();

        // Total des investissements planifiés
        $investment_phases = $this->db->select_sum('montant_total')->get('investissement_phases')->row()->montant_total ?? 0;

        return [
            'orders' => [
                'total_revenue'   => (float) $order_stats['total_revenue'],
                'confirmed_revenue'=> (float) $order_stats['confirmed_revenue'],
                'today'           => (float) $order_stats['today_revenue'],
                'this_month'      => (float) $order_stats['month_revenue'],
                'total_count'     => (int) $order_stats['total_orders'],
                'average_order'   => round((float) ($order_stats['avg_order_value'] ?? 0), 2)
            ],
            'consultations' => [
                'total_revenue'   => (float) $consultation_stats['total_revenue'],
                'total_count'     => (int) $consultation_stats['total_consultations'],
                'completed_count' => (int) $consultation_stats['completed_consultations'],
                'pending_count'   => (int) $consultation_stats['pending_consultations']
            ],
            'investments' => [
                'total_planned'   => (float) $investment_phases
            ],
            'global' => [
                'total_revenue'   => (float) $order_stats['total_revenue'] + (float) $consultation_stats['total_revenue']
            ]
        ];
    }

    private function _get_visitor_analytics() {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $today_visits = $this->db->where('visit_date', $today)->count_all_results('visitors_logs');
        $yesterday_visits = $this->db->where('visit_date', $yesterday)->count_all_results('visitors_logs');
        $unique_visitors = $this->db->where('visit_date', $today)->group_by('ip_address')->count_all_results('visitors_logs');

        // Top pages
        $top_pages = $this->db->query("
            SELECT page, COUNT(*) as views
            FROM visitors_logs
            WHERE visit_date = CURDATE()
            GROUP BY page
            ORDER BY views DESC
            LIMIT 5
        ")->result_array();

        // Sources de trafic
        $referrers = $this->db->query("
            SELECT 
                CASE 
                    WHEN referer IS NULL OR referer = '' THEN 'Direct'
                    WHEN referer LIKE '%google%' THEN 'Google'
                    WHEN referer LIKE '%facebook%' THEN 'Facebook'
                    ELSE 'Autres'
                END as source,
                COUNT(*) as visits
            FROM visitors_logs
            WHERE visit_date = CURDATE()
            GROUP BY source
        ")->result_array();

        // Types d'appareils
        $devices = $this->db->query("
            SELECT 
                CASE 
                    WHEN device = 'Mobile' THEN 'Mobile'
                    WHEN device = 'Desktop' THEN 'Desktop'
                    ELSE 'Autre'
                END as device_type,
                COUNT(*) as count
            FROM visitors_logs
            WHERE visit_date = CURDATE()
            GROUP BY device_type
        ")->result_array();

        // Pays visiteurs
        $countries = $this->db->query("
            SELECT country, COUNT(*) as visits 
            FROM visitors_logs 
            WHERE visit_date = CURDATE() AND country IS NOT NULL
            GROUP BY country
            ORDER BY visits DESC
            LIMIT 5
        ")->result_array();

        return [
            'today_visits'      => $today_visits,
            'yesterday_visits'  => $yesterday_visits,
            'unique_visitors'   => $unique_visitors,
            'online_now'        => $this->_get_online_users(),
            'top_pages'         => $top_pages,
            'referrers'         => $referrers,
            'devices'           => $devices,
            'countries'         => $countries,
            'trend'             => $yesterday_visits > 0 ? round((($today_visits - $yesterday_visits) / $yesterday_visits * 100), 2) : 0
        ];
    }

    private function _get_ecommerce_stats() {
        // Catégories et leur nombre de produits
        $categories = $this->db->query("
            SELECT 
                c.id as id,
                c.name as nom,
                COUNT(p.id_produit) as product_count
            FROM product_categories c
            LEFT JOIN produits p ON c.id = p.id_categorie AND p.est_actif = 1
            GROUP BY c.id
            ORDER BY product_count DESC
            LIMIT 6
        ")->result_array();

        // Produits les plus populaires (favoris)
        $top_products = $this->db->query("
            SELECT 
                p.id_produit as id,
                p.nom_produit as nom,
                p.slug,
                p.prix_public as prix_ttc,
                p.image_principale,
                p.nb_favoris,
                pc.name as categorie_nom
            FROM produits p
            LEFT JOIN product_categories pc ON p.id_categorie = pc.id
            WHERE p.est_actif = 1
            ORDER BY p.nb_favoris DESC
            LIMIT 5
        ")->result_array();

        // Statistiques des paniers
        $cart_stats = $this->db->query("
            SELECT 
                AVG(total_ttc) as avg_value,
                MAX(total_ttc) as max_value,
                MIN(total_ttc) as min_value,
                AVG(nombre_articles) as avg_items
            FROM paniers
            WHERE est_actif = 1
        ")->row_array();

        // Paniers abandonnés
        $abandoned_carts = $this->db->query("
            SELECT COUNT(*) as total
            FROM paniers p
            WHERE p.est_actif = 1
            AND p.updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND EXISTS (
                SELECT 1 FROM panier_lignes pl WHERE pl.panier_id = p.id
            )
        ")->row()->total;

        return [
            'categories'      => $categories,
            'top_products'    => $top_products,
            'cart_stats'      => [
                'average_value' => round((float) ($cart_stats['avg_value'] ?? 0), 2),
                'max_value'     => (float) ($cart_stats['max_value'] ?? 0),
                'min_value'     => (float) ($cart_stats['min_value'] ?? 0),
                'avg_items'     => round((float) ($cart_stats['avg_items'] ?? 0), 1)
            ],
            'abandoned_carts' => (int) $abandoned_carts
        ];
    }

    private function _get_telemedecine_stats() {
        $today = date('Y-m-d');
        
        return [
            'today_appointments' => $this->db
                ->where('DATE(date_souhaitee)', $today)
                ->where_in('statut', ['confirmee', 'en_cours'])
                ->count_all_results('consultations'),
            
            'pending_appointments' => $this->db->where('statut', 'en_attente')->count_all_results('consultations'),
            
            'completed_today' => $this->db
                ->where('DATE(date_fin)', $today)
                ->where('statut', 'terminee')
                ->count_all_results('consultations'),
            
            'revenue_today' => $this->db
                ->select('COALESCE(SUM(prix_ttc), 0) as total')
                ->where('DATE(date_fin)', $today)
                ->where('statut', 'terminee')
                ->get('consultations')
                ->row()
                ->total,
            
            'by_type' => $this->db->query("
                SELECT type, COUNT(*) as count
                FROM consultations
                GROUP BY type
            ")->result_array(),
            
            'specialty_distribution' => $this->db->query("
                SELECT 
                    m.specialite,
                    COUNT(c.id) as total_consultations
                FROM medecins m
                LEFT JOIN consultations c ON m.id = c.medecin_id AND c.statut = 'terminee'
                GROUP BY m.specialite
                ORDER BY total_consultations DESC
            ")->result_array(),
            
            'upcoming_appointments' => $this->db->query("
                SELECT 
                    c.*,
                    p.prenom as patient_prenom,
                    p.nom as patient_nom,
                    p.telephone,
                    m_user.prenom as medecin_prenom,
                    m.specialite
                FROM consultations c
                JOIN users p ON c.patient_id = p.id
                JOIN medecins m ON c.medecin_id = m.id
                JOIN users m_user ON m.user_id = m_user.id
                WHERE c.date_souhaitee >= NOW()
                AND c.statut IN ('confirmee', 'en_cours')
                ORDER BY c.date_souhaitee ASC
                LIMIT 5
            ")->result_array()
        ];
    }

    private function _get_investment_stats() {
        $phases = $this->db->get('investissement_phases')->result_array();
        $total_phases = count($phases);
        $montant_total = array_sum(array_column($phases, 'montant_total'));

        return [
            'phases'          => $phases,
            'total_phases'    => $total_phases,
            'montant_total'   => $montant_total,
            'by_currency'     => $this->db->query("SELECT devise, COUNT(*) as count, SUM(montant_total) as total FROM investissement_phases GROUP BY devise")->result_array()
        ];
    }

    private function _get_charts_data() {
        $days = 30;
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = date('d/m', strtotime("-$i days"));
        }

        $revenue_data = $this->_get_time_series_sum('commandes', 'total_ttc', $days, "statut != 'annulee'");
        $orders_data = $this->_get_time_series_count('commandes', $days);
        $users_data = $this->_get_time_series_count('users', $days);
        $consultations_data = $this->_get_time_series_count('consultations', $days);

        // Distribution des rôles
        $roles_dist = $this->db->query("
            SELECT r.nom as role, COUNT(u.id) as count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id AND u.deleted_at IS NULL
            GROUP BY r.id
            ORDER BY count DESC
        ")->result_array();

        // Statuts des commandes
        $order_status = $this->db->query("
            SELECT statut, COUNT(*) as count, SUM(total_ttc) as total
            FROM commandes
            GROUP BY statut
        ")->result_array();

        // Types d'utilisateurs
        $user_types = $this->db->query("
            SELECT type_utilisateur, COUNT(*) as count
            FROM users
            WHERE deleted_at IS NULL
            GROUP BY type_utilisateur
        ")->result_array();

        return [
            'revenue' => [
                'labels' => $labels,
                'data'   => $revenue_data,
                'trend'  => $this->_calculate_trend($revenue_data)
            ],
            'orders'  => ['labels' => $labels, 'data' => $orders_data],
            'users'   => ['labels' => $labels, 'data' => $users_data],
            'consultations' => ['labels' => $labels, 'data' => $consultations_data],
            'roles_distribution' => $roles_dist,
            'order_status'       => $order_status,
            'user_types'         => $user_types,
            'last_updated'       => date('Y-m-d H:i:s')
        ];
    }

    // ========================================================================
    // FONCTIONS UTILITAIRES
    // ========================================================================

    private function _get_online_users($minutes = 15) {
        return $this->db->query("
            SELECT 
                u.id,
                u.prenom,
                u.nom,
                u.photo,
                u.type_utilisateur,
                s.ip_address,
                s.last_activity,
                s.browser,
                s.platform
            FROM user_sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.is_active = 1
            AND s.last_activity >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
            ORDER BY s.last_activity DESC
        ")->result_array();
    }

    private function _get_time_series_count($table, $days) {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $count = $this->db->where('DATE(created_at)', $date)->count_all_results($table);
            $data[] = (int) $count;
        }
        return $data;
    }

    private function _get_time_series_sum($table, $column, $days, $extra_where = '') {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $this->db->select("COALESCE(SUM($column), 0) as total");
            $this->db->where("DATE(created_at)", $date);
            if ($extra_where) $this->db->where($extra_where);
            $sum = $this->db->get($table)->row()->total;
            $data[] = (float) $sum;
        }
        return $data;
    }

    private function _calculate_trend($data) {
        if (count($data) < 2) return 0;
        $first = array_slice($data, 0, floor(count($data) / 2));
        $second = array_slice($data, floor(count($data) / 2));
        $avg_first = array_sum($first) / count($first);
        $avg_second = array_sum($second) / count($second);
        return $avg_first > 0 ? round((($avg_second - $avg_first) / $avg_first) * 100, 2) : 0;
    }

    private function _get_users_by_type() {
        return $this->db->query("
            SELECT type_utilisateur, COUNT(*) as count
            FROM users
            WHERE deleted_at IS NULL
            GROUP BY type_utilisateur
        ")->result_array();
    }

    private function _get_recent_activities($limit = 10) {
        return $this->db->query("
            SELECT 
                ua.*,
                u.prenom,
                u.nom,
                u.photo,
                u.type_utilisateur
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            ORDER BY ua.created_at DESC
            LIMIT {$limit}
        ")->result_array();
    }

    private function _get_system_alerts() {
        $alerts = [];
        
        $pending_orders = $this->db->where('statut', 'en_attente')->count_all_results('commandes');
        if ($pending_orders > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'bx bx-cart',
                'title' => 'Commandes en attente',
                'message' => "{$pending_orders} commande(s) en attente de traitement",
                'link' => base_url('Commandes?statut=en_attente')
            ];
        }
        
        $pending_consultations = $this->db->where('statut', 'en_attente')->count_all_results('consultations');
        if ($pending_consultations > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'bx bx-calendar',
                'title' => 'Consultations en attente',
                'message' => "{$pending_consultations} consultation(s) en attente de confirmation",
                'link' => base_url('Consultations?statut=en_attente')
            ];
        }
        
        $unverified_users = $this->db->where('email_verified_at IS NULL', null, false)
                                     ->where('is_active', 0)
                                     ->count_all_results('users');
        if ($unverified_users > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'bx bx-user-x',
                'title' => 'Utilisateurs non vérifiés',
                'message' => "{$unverified_users} utilisateur(s) en attente de vérification",
                'link' => base_url('Users?verified=0')
            ];
        }
        
        return $alerts;
    }

    private function _get_quick_actions() {
        return [
            ['title' => 'Nouvel utilisateur', 'icon' => 'bx bx-user-plus', 'color' => 'primary', 'link' => base_url('Users/create')],
            ['title' => 'Nouveau produit', 'icon' => 'bx bx-package', 'color' => 'success', 'link' => base_url('Produits/create')],
            ['title' => 'Nouvelle consultation', 'icon' => 'bx bx-calendar-plus', 'color' => 'warning', 'link' => base_url('Consultations/create')],
            ['title' => 'Nouvelle commande', 'icon' => 'bx bx-cart-add', 'color' => 'danger', 'link' => base_url('Commandes/create')],
            ['title' => 'Paramètres', 'icon' => 'bx bx-cog', 'color' => 'dark', 'link' => base_url('Configurations')]
        ];
    }

    private function _get_latest_users($limit = 8) {
        return $this->db->query("
            SELECT u.*, r.nom as role_nom, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.deleted_at IS NULL 
            ORDER BY u.created_at DESC 
            LIMIT {$limit}
        ")->result_array();
    }

    private function _get_latest_orders($limit = 8) {
        return $this->db->query("
            SELECT 
                cmd.*,
                u.prenom,
                u.nom,
                u.email,
                u.telephone,
                (SELECT COUNT(*) FROM commande_lignes cl WHERE cl.commande_id = cmd.id) as nb_items
            FROM commandes cmd
            JOIN users u ON cmd.user_id = u.id
            ORDER BY cmd.created_at DESC
            LIMIT {$limit}
        ")->result_array();
    }

    private function _get_latest_consultations($limit = 5) {
        return $this->db->query("
            SELECT 
                c.*,
                p.prenom as patient_prenom,
                p.nom as patient_nom,
                p.telephone as patient_tel,
                m_user.prenom as medecin_prenom,
                m_user.nom as medecin_nom,
                m.specialite
            FROM consultations c
            JOIN users p ON c.patient_id = p.id
            LEFT JOIN medecins m ON c.medecin_id = m.id
            LEFT JOIN users m_user ON m.user_id = m_user.id
            ORDER BY c.created_at DESC
            LIMIT {$limit}
        ")->result_array();
    }

    private function _get_low_stock_products($limit = 6) {
        // Table produits n'a pas de champ stock, retourner tableau vide
        return [];
    }

    private function _get_pending_verifications() {
        return [
            'users_unverified'  => $this->db->where('email_verified_at IS NULL', null, false)
                                            ->where('is_active', 0)
                                            ->count_all_results('users'),
            'users_inactive'    => $this->db->where('is_active', 0)->count_all_results('users'),
            'produits_inactive' => $this->db->where('est_actif', 0)->count_all_results('produits'),
            'consultations_pending' => $this->db->where('statut', 'en_attente')->count_all_results('consultations')
        ];
    }

    // ========================================================================
    // FONCTIONS PRIVÉES GÉNÉRIQUES
    // ========================================================================

    private function _count_today($table, $date_field = 'created_at') {
        return $this->db->where("DATE({$date_field})", date('Y-m-d'))->count_all_results($table);
    }

    private function _update_user_activity() {
        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            $this->db->where('id', $user_id)
                     ->update('users', ['last_login_at' => date('Y-m-d H:i:s')]);
        }
    }

    // ========================================================================
    // MÉTHODES DE VÉRIFICATION DES RÔLES
    // ========================================================================

    private function is_admin() {
        $role_slug = $this->session->userdata('role_slug');
        return $role_slug === 'admin';
    }

    private function is_medecin() {
        $role_slug = $this->session->userdata('role_slug');
        return $role_slug === 'medecin';
    }

    // ========================================================================
    // API ENDPOINTS
    // ========================================================================

    public function api_stats() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $type = $this->input->get('type');
        $response = ['success' => true, 'timestamp' => date('c')];
        
        switch($type) {
            case 'realtime':
                $response['data'] = [
                    'online_users' => count($this->_get_online_users()),
                    'today_visits' => $this->db->where('visit_date', date('Y-m-d'))->count_all_results('visitors_logs'),
                    'pending_orders' => $this->db->where('statut', 'en_attente')->count_all_results('commandes'),
                    'server_time' => date('H:i:s')
                ];
                break;
            case 'financial':
                $response['data'] = $this->_get_financial_metrics();
                break;
            case 'charts':
                $period = $this->input->get('period') ?: 30;
                $response['data'] = $this->_get_charts_data();
                break;
            default:
                $response = ['success' => false, 'error' => 'Type de statistique inconnu'];
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function mark_notification_read() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        echo json_encode(['success' => true]);
    }
}