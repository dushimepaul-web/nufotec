<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ============================================================
 * Dashboard Controller – NUFOTEC BURUNDI
 * ============================================================
 * Tableaux de bord complets pour Admin et Médecin.
 * Couvre : utilisateurs, e-commerce, télémédecine, investisseurs,
 * courtiers, newsletter, WhatsApp, médias, visiteurs, finances.
 * ============================================================
 */
class Dashboard extends MY_Controller {

    /* ── Palette de couleurs ─────────────────────────────── */
    private $colors = [
        'primary'   => '#062C54',
        'green'     => '#0F766E',
        'teal'      => '#1a8c78',
        'orange'    => '#FF8C00',
        'crimson'   => '#DC143C',
        'gold'      => '#FFD000',
        'navy'      => '#01579B',
    ];

    private $cache_ttl = 300; // 5 minutes

    /* ── Constructeur ────────────────────────────────────── */
    public function __construct() {
        parent::__construct();
        $this->load->helper(['security', 'text', 'date', 'number']);
        $this->load->library(['user_agent', 'form_validation']);
        $this->_touch_session();
    }

    /* ══════════════════════════════════════════════════════
     *  ROUTEUR PRINCIPAL
     * ══════════════════════════════════════════════════════ */
    public function index() {
        $slug = $this->session->userdata('role_slug');
        $map  = ['admin' => 'admin_dashboard', 'medecin' => 'medecin_dashboard'];
        if (!isset($map[$slug])) redirect('Admin');
        $this->{$map[$slug]}();
    }

    /* ══════════════════════════════════════════════════════
     *  DASHBOARD ADMINISTRATEUR
     * ══════════════════════════════════════════════════════ */
    public function admin_dashboard() {
        if (!$this->_is_admin()) redirect('Admin');

        try {
            $data = [
                'page_title'        => 'Dashboard NUFOTEC – Administration',
                'colors'            => $this->colors,
                'generated_at'      => date('d/m/Y H:i:s'),

                /* ── KPIs globaux ── */
                'kpi_users'         => $this->_kpi_users(),
                'kpi_orders'        => $this->_kpi_orders(),
                'kpi_finance'       => $this->_kpi_finance(),
                'kpi_telemedecine'  => $this->_kpi_telemedecine(),
                'kpi_products'      => $this->_kpi_products(),
                'kpi_media'         => $this->_kpi_media(),

                /* ── Modules ── */
                'visitor_stats'     => $this->_visitor_stats(),
                'newsletter_stats'  => $this->_newsletter_stats(),
                'whatsapp_stats'    => $this->_whatsapp_stats(),
                'investor_stats'    => $this->_investor_stats(),
                'broker_stats'      => $this->_broker_stats(),
                'investment_phases' => $this->_investment_phases(),
                'ecommerce'         => $this->_ecommerce_deep(),
                'advertise'         => $this->_advertise_stats(),
                'order_requests'    => $this->_order_requests_stats(),
                'telemedecine'      => $this->_telemedecine_deep(),
                'media_engagement'  => $this->_media_engagement(),
                'social_networks'   => $this->_social_network_stats(),
                'contact_messages'  => $this->_contact_messages(),

                /* ── Tableaux récents ── */
                'latest_users'      => $this->_latest_users(8),
                'latest_orders'     => $this->_latest_orders(8),
                'latest_consultations' => $this->_latest_consultations(6),
                'latest_order_requests'=> $this->_latest_order_requests(8),
                'latest_investors'  => $this->_latest_investors(5),
                'latest_brokers'    => $this->_latest_brokers(5),

                /* ── Graphiques ── */
                'chart_revenue_30d' => $this->_chart_series('commandes', 'total_ttc', 30, "statut != 'annulee'"),
                'chart_users_30d'   => $this->_chart_count('users', 30),
                'chart_consult_30d' => $this->_chart_count('consultations', 30),
                'chart_visits_30d'  => $this->_chart_visits_30d(),
                'chart_labels_30d'  => $this->_chart_labels(30),
                'chart_newsletter'  => $this->_chart_count('newsletter', 30, 'date_inscription'),
                'chart_order_req'   => $this->_chart_count('order_requests', 30),

                /* ── Répartitions (Doughnut / Pie) ── */
                'dist_user_types'   => $this->_dist('users',         'type_utilisateur'),
                'dist_order_status' => $this->_dist('commandes',     'statut'),
                'dist_consult_type' => $this->_dist('consultations', 'type'),
                'dist_consult_stat' => $this->_dist('consultations', 'statut'),
                'dist_media_type'   => $this->_dist('galerie_medias','type'),
                'dist_order_req_status' => $this->_dist('order_requests', 'order_status'),
                'dist_invest_commit'=> $this->_dist_investors_commitment(),
                'dist_devices'      => $this->_dist('visitors_logs', 'device'),
                'dist_brokers_country' => $this->_brokers_country_dist(),

                /* ── Alertes & Actions ── */
                'alerts'            => $this->_alerts(),
                'quick_actions'     => $this->_quick_actions(),
                'pending_verif'     => $this->_pending_verif(),
                'top_products'      => $this->_top_products_advertise(6),
                'top_medias'        => $this->_top_medias(5),
                'upcoming_consults' => $this->_upcoming_consultations(5),
                'recent_activities' => $this->_recent_activities(10),
                'system_health'     => $this->_system_health(),
            ];
        } catch (Exception $e) {
            log_message('error', 'Dashboard::admin_dashboard – ' . $e->getMessage());
            $data['error'] = 'Erreur lors du chargement du tableau de bord.';
        }

        $this->load->view('admin', $data);
    }

    /* ══════════════════════════════════════════════════════
     *  DASHBOARD MÉDECIN
     * ══════════════════════════════════════════════════════ */
    public function medecin_dashboard() {
        if (!$this->_is_medecin()) redirect('Admin');

        $uid = $this->session->userdata('user_id');
        $medecin = $this->db
            ->select('m.*, u.prenom, u.nom, u.email, u.telephone, u.photo')
            ->from('medecins m')
            ->join('users u', 'm.user_id = u.id')
            ->where('m.user_id', $uid)->get()->row();

        if (!$medecin) redirect('Admin');

        $today = date('Y-m-d');
        $mid   = $medecin->id;

        $data = [
            'page_title' => 'Mon Espace Médecin',
            'medecin'    => $medecin,
            'stats' => [
                'today'     => $this->db->where('medecin_id',$mid)->where('DATE(date_souhaitee)',$today)->count_all_results('consultations'),
                'pending'   => $this->db->where('medecin_id',$mid)->where('statut','en_attente')->count_all_results('consultations'),
                'total'     => $this->db->where('medecin_id',$mid)->count_all_results('consultations'),
                'completed' => $this->db->where('medecin_id',$mid)->where('statut','terminee')->count_all_results('consultations'),
                'revenue'   => $this->db->select('COALESCE(SUM(honoraires_consultation),0) as t')->where('medecin_id',$mid)->where('statut','terminee')->get('consultations')->row()->t,
            ],
            'upcoming' => $this->db->where('medecin_id',$mid)->where('date_souhaitee >=', date('Y-m-d H:i:s'))->where_in('statut',['confirmee','en_attente'])->order_by('date_souhaitee','ASC')->limit(10)->get('consultations')->result_array(),
            'recent_patients' => $this->db->select('c.*, u.prenom, u.nom, u.telephone')->from('consultations c')->join('users u','c.patient_id = u.id')->where('c.medecin_id',$mid)->order_by('c.created_at','DESC')->limit(10)->get()->result_array(),
            'chart_labels' => $this->_chart_labels(30),
            'chart_consults'=> $this->_chart_medecin_consults($mid, 30),
        ];

        $this->load->view('medecin', $data);
    }

    /* ══════════════════════════════════════════════════════
     *  KPIs PRINCIPAUX
     * ══════════════════════════════════════════════════════ */

    /** Statistiques utilisateurs */
    private function _kpi_users(): array {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $week_ago  = date('Y-m-d', strtotime('-7 days'));

        $total   = $this->db->where('deleted_at IS NULL',null,false)->count_all_results('users');
        $active  = $this->db->where('is_active',1)->where('deleted_at IS NULL',null,false)->count_all_results('users');
        $today_n = $this->db->where('DATE(created_at)',$today)->where('deleted_at IS NULL',null,false)->count_all_results('users');
        $week_n  = $this->db->where('DATE(created_at) >=', $week_ago)->where('deleted_at IS NULL',null,false)->count_all_results('users');
        $verif   = $this->db->where('email_verified_at IS NOT NULL',null,false)->count_all_results('users');
        $online  = $this->db->where('is_active',1)->where('last_activity >=', date('Y-m-d H:i:s', strtotime('-15 minutes')))->count_all_results('user_sessions');

        // Sessions actives
        $sessions_today = $this->db->where('DATE(login_time)', $today)->count_all_results('user_sessions');

        return compact('total','active','today_n','week_n','verif','online','sessions_today');
    }

    /** Statistiques commandes e-commerce (table commandes) */
    private function _kpi_orders(): array {
        $row = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN statut='en_attente' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN statut='preparation' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN statut='expediee' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN statut='livree' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN statut='annulee' THEN 1 ELSE 0 END) as cancelled
            FROM commandes
        ")->row_array();

        // Demandes de commande (advertise products)
        $req = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN order_status='pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN order_status='processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN order_status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) as today
            FROM order_requests
        ")->row_array();

        return ['shop' => $row, 'requests' => $req];
    }

    /** Métriques financières */
    private function _kpi_finance(): array {
        $orders = $this->db->query("
            SELECT
                COALESCE(SUM(total_ttc),0) as total,
                COALESCE(SUM(CASE WHEN statut='livree' THEN total_ttc ELSE 0 END),0) as confirmed,
                COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN total_ttc ELSE 0 END),0) as today,
                COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) THEN total_ttc ELSE 0 END),0) as this_month,
                COALESCE(AVG(total_ttc),0) as avg_order
            FROM commandes WHERE statut != 'annulee'
        ")->row_array();

        $consults = $this->db->query("
            SELECT
                COALESCE(SUM(prix_ttc),0) as total,
                COALESCE(SUM(CASE WHEN DATE(date_fin)=CURDATE() THEN prix_ttc ELSE 0 END),0) as today,
                COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE()) THEN prix_ttc ELSE 0 END),0) as this_month
            FROM consultations WHERE statut='terminee'
        ")->row_array();

        $invest = $this->db->select_sum('montant_total')->get('investissement_phases')->row()->montant_total ?? 0;

        return [
            'orders'           => $orders,
            'consultations'    => $consults,
            'total_revenue'    => (float)$orders['total'] + (float)$consults['total'],
            'today_revenue'    => (float)$orders['today'] + (float)$consults['today'],
            'month_revenue'    => (float)$orders['this_month'] + (float)$consults['this_month'],
            'investment_planned'=> (float)$invest,
        ];
    }

    /** KPI Télémédecine */
    private function _kpi_telemedecine(): array {
        $today = date('Y-m-d');
        return [
            'today'     => $this->db->where('DATE(date_souhaitee)',$today)->count_all_results('consultations'),
            'pending'   => $this->db->where('statut','en_attente')->count_all_results('consultations'),
            'confirmed' => $this->db->where('statut','confirmee')->count_all_results('consultations'),
            'ongoing'   => $this->db->where('statut','en_cours')->count_all_results('consultations'),
            'completed' => $this->db->where('statut','terminee')->count_all_results('consultations'),
            'cancelled' => $this->db->where_in('statut',['annulee','refusee'])->count_all_results('consultations'),
            'total'     => $this->db->count_all_results('consultations'),
            'medecins'  => $this->db->count_all_results('medecins'),
            'available' => $this->db->where('est_disponible',1)->count_all_results('medecins'),
            'avg_rating'=> round((float)($this->db->select('AVG(note_moyenne) as a')->get('medecins')->row()->a ?? 0), 2),
        ];
    }

    /** KPI Produits */
    private function _kpi_products(): array {
        return [
            'catalogue_total'  => $this->db->where('est_actif',1)->count_all_results('produits'),
            'catalogue_vedette'=> $this->db->where('est_vedette',1)->where('est_actif',1)->count_all_results('produits'),
            'advertise_total'  => $this->db->where('is_active',1)->count_all_results('advertise_product'),
            'advertise_vedette'=> $this->db->where('in_vedette',1)->count_all_results('advertise_product'),
            'total_price_requests'=> (int)($this->db->select_sum('price_request_count')->get('advertise_product')->row()->price_request_count ?? 0),
            'categories_catalogue'=> $this->db->count_all_results('categories'),
            'categories_advertise'=> $this->db->count_all_results('product_categories'),
        ];
    }

    /** KPI Médias */
    private function _kpi_media(): array {
        return [
            'total'     => $this->db->where('est_actif',1)->count_all_results('galerie_medias'),
            'audio'     => $this->db->where('type','audio')->where('est_actif',1)->count_all_results('galerie_medias'),
            'video'     => $this->db->where('type','video')->where('est_actif',1)->count_all_results('galerie_medias'),
            'image'     => $this->db->where('type','image')->where('est_actif',1)->count_all_results('galerie_medias'),
            'document'  => $this->db->where('type','document')->where('est_actif',1)->count_all_results('galerie_medias'),
            'total_views'=> (int)$this->db->count_all_results('media_views'),
            'total_likes'=> (int)$this->db->count_all_results('media_likes'),
            'total_comments'=> (int)$this->db->count_all_results('media_comments'),
            'total_downloads'=> (int)$this->db->count_all_results('media_downloads'),
            'total_plays'=> (int)$this->db->count_all_results('media_plays'),
        ];
    }

    /* ══════════════════════════════════════════════════════
     *  MODULES ANALYTIQUES
     * ══════════════════════════════════════════════════════ */

    /** Statistiques visiteurs */
    private function _visitor_stats(): array {
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $today_v   = $this->db->where('visit_date',$today)->count_all_results('visitors_logs');
        $yest_v    = $this->db->where('visit_date',$yesterday)->count_all_results('visitors_logs');
        $unique    = $this->db->where('visit_date',$today)->distinct()->select('ip_address')->count_all_results('visitors_logs');
        $week      = $this->db->where('visit_date >=', date('Y-m-d',strtotime('-7 days')))->count_all_results('visitors_logs');

        $top_pages = $this->db->query("
            SELECT page, COUNT(*) as cnt
            FROM visitors_logs WHERE visit_date=CURDATE()
            GROUP BY page ORDER BY cnt DESC LIMIT 6
        ")->result_array();

        $top_countries = $this->db->query("
            SELECT country, COUNT(*) as cnt
            FROM visitors_logs WHERE country IS NOT NULL
            GROUP BY country ORDER BY cnt DESC LIMIT 5
        ")->result_array();

        return [
            'today'         => $today_v,
            'yesterday'     => $yest_v,
            'unique_today'  => $unique,
            'week'          => $week,
            'trend_pct'     => $yest_v > 0 ? round(($today_v - $yest_v) / $yest_v * 100, 1) : 0,
            'top_pages'     => $top_pages,
            'top_countries' => $top_countries,
        ];
    }

    /** Statistiques newsletter */
    private function _newsletter_stats(): array {
        $total_email  = $this->db->where('email IS NOT NULL',null,false)->count_all_results('newsletter');
        $total_phone  = $this->db->where('telephone IS NOT NULL',null,false)->count_all_results('newsletter');
        $total        = $this->db->count_all_results('newsletter');
        $today        = $this->db->where('DATE(date_inscription)',date('Y-m-d'))->count_all_results('newsletter');
        $week         = $this->db->where('DATE(date_inscription) >=', date('Y-m-d',strtotime('-7 days')))->count_all_results('newsletter');
        $month        = $this->db->where('DATE(date_inscription) >=', date('Y-m-01'))->count_all_results('newsletter');

        return compact('total','total_email','total_phone','today','week','month');
    }

    /** Statistiques WhatsApp */
    private function _whatsapp_stats(): array {
        $groups     = $this->db->where('actif',1)->count_all_results('groupes_whatsapp');
        $total_logs = $this->db->count_all_results('whatsapp_logs');
        $sent       = $this->db->where('status','sent')->count_all_results('whatsapp_logs');
        $received   = $this->db->where('status','received')->count_all_results('whatsapp_logs');
        $failed     = $this->db->where('status','failed')->count_all_results('whatsapp_logs');
        $queue_pending = $this->db->where('status','pending')->count_all_results('whatsapp_queue');
        $blacklisted= $this->db->count_all_results('whatsapp_blacklist');
        $templates  = $this->db->count_all_results('whatsapp_templates');

        // Réseau stats (WhatsApp)
        $wa_network = $this->db->where('plateforme','WhatsApp')->get('statistiques_reseaux')->row_array() ?? [];

        return [
            'groups'        => $groups,
            'total_logs'    => $total_logs,
            'sent'          => $sent,
            'received'      => $received,
            'failed'        => $failed,
            'queue_pending' => $queue_pending,
            'blacklisted'   => $blacklisted,
            'templates'     => $templates,
            'wa_groups'     => $wa_network['nombre_groupes'] ?? 0,
            'wa_members'    => $wa_network['nombre_participants'] ?? 0,
        ];
    }

    /** Statistiques investisseurs */
    private function _investor_stats(): array {
        $total       = $this->db->count_all_results('investors');
        $this_month  = $this->db->where('DATE(created_at) >=', date('Y-m-01'))->count_all_results('investors');

        $by_commitment = $this->db->query("
            SELECT commitment_range, COUNT(*) as cnt
            FROM investors GROUP BY commitment_range ORDER BY cnt DESC
        ")->result_array();

        $by_timeline = $this->db->query("
            SELECT timeline, COUNT(*) as cnt
            FROM investors GROUP BY timeline
        ")->result_array();

        $by_country = $this->db->query("
            SELECT p.pays as country, COUNT(i.id) as cnt
            FROM investors i JOIN pays p ON i.id_pays = p.id
            GROUP BY p.pays ORDER BY cnt DESC LIMIT 5
        ")->result_array();

        // Intérêts multiples
        $interests = [
            'equity'            => $this->db->where('interest_equity',1)->count_all_results('investors'),
            'debt'              => $this->db->where('interest_debt',1)->count_all_results('investors'),
            'blended_finance'   => $this->db->where('interest_blended_finance',1)->count_all_results('investors'),
            'grant'             => $this->db->where('interest_grant',1)->count_all_results('investors'),
            'strategic'         => $this->db->where('interest_strategic_partnership',1)->count_all_results('investors'),
            'technical'         => $this->db->where('interest_technical_collaboration',1)->count_all_results('investors'),
        ];

        return compact('total','this_month','by_commitment','by_timeline','by_country','interests');
    }

    /** Statistiques courtiers */
    private function _broker_stats(): array {
        $total       = $this->db->count_all_results('brokers');
        $this_month  = $this->db->where('DATE(created_at) >=', date('Y-m-01'))->count_all_results('brokers');

        $by_status = $this->db->query("
            SELECT regulatory_status, COUNT(*) as cnt
            FROM brokers GROUP BY regulatory_status
        ")->result_array();

        $by_country = $this->db->query("
            SELECT p.pays as country, COUNT(b.id) as cnt
            FROM brokers b JOIN pays p ON b.id_pays = p.id
            GROUP BY p.pays ORDER BY cnt DESC LIMIT 5
        ")->result_array();

        $capacities = [
            'investment_broker'        => $this->db->where('capacity_investment_broker',1)->count_all_results('brokers'),
            'placement_agent'          => $this->db->where('capacity_placement_agent',1)->count_all_results('brokers'),
            'corporate_finance_advisor'=> $this->db->where('capacity_corporate_finance_advisor',1)->count_all_results('brokers'),
            'fund_manager'             => $this->db->where('capacity_fund_manager',1)->count_all_results('brokers'),
            'esg_advisor'              => $this->db->where('capacity_esg_advisor',1)->count_all_results('brokers'),
        ];

        return compact('total','this_month','by_status','by_country','capacities');
    }

    /** Phases d'investissement */
    private function _investment_phases(): array {
        $phases = $this->db->order_by('annee_debut','ASC')->get('investissement_phases')->result_array();
        $total  = array_sum(array_column($phases,'montant_total'));
        return ['phases' => $phases, 'total' => $total, 'count' => count($phases)];
    }

    /** E-commerce approfondissement */
    private function _ecommerce_deep(): array {
        $cart = $this->db->query("
            SELECT
                COUNT(*) as total_carts,
                SUM(CASE WHEN est_actif=1 THEN 1 ELSE 0 END) as active_carts,
                AVG(total_ttc) as avg_cart_value,
                MAX(total_ttc) as max_cart_value,
                SUM(nombre_articles) as total_items
            FROM paniers
        ")->row_array();

        $abandoned = $this->db->query("
            SELECT COUNT(*) as cnt
            FROM paniers p
            WHERE p.est_actif=1
            AND p.updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND EXISTS (SELECT 1 FROM panier_lignes pl WHERE pl.panier_id = p.id)
        ")->row()->cnt;

        $top_cat = $this->db->query("
            SELECT c.name, COUNT(p.id_produit) as cnt
            FROM product_categories c
            LEFT JOIN produits p ON c.id = p.id_categorie AND p.est_actif=1
            GROUP BY c.id ORDER BY cnt DESC
        ")->result_array();

        $favorites = $this->db->query("
            SELECT p.nom_produit, COUNT(f.id) as fav_count
            FROM favoris f
            JOIN produits p ON f.produit_id = p.id_produit
            GROUP BY f.produit_id ORDER BY fav_count DESC LIMIT 5
        ")->result_array();

        return [
            'cart'        => $cart,
            'abandoned'   => (int)$abandoned,
            'top_cat'     => $top_cat,
            'favorites'   => $favorites,
        ];
    }

    /** Statistiques Advertise Products */
    private function _advertise_stats(): array {
        $total     = $this->db->where('is_active',1)->count_all_results('advertise_product');
        $deleted   = $this->db->where('deleted_at IS NOT NULL',null,false)->count_all_results('advertise_product');

        $top_requested = $this->db->query("
            SELECT id, title, price, price_request_count, in_vedette
            FROM advertise_product WHERE is_active=1
            ORDER BY price_request_count DESC LIMIT 6
        ")->result_array();

        return compact('total','deleted','top_requested');
    }

    /** Statistiques order_requests (commandes de produits advertise) */
    private function _order_requests_stats(): array {
        $countries = $this->db->query("
            SELECT customer_country, COUNT(*) as cnt
            FROM order_requests
            GROUP BY customer_country ORDER BY cnt DESC LIMIT 8
        ")->result_array();

        $products = $this->db->query("
            SELECT product_title, COUNT(*) as cnt, SUM(whatsapp_sent) as wa_sent
            FROM order_requests GROUP BY product_id ORDER BY cnt DESC LIMIT 6
        ")->result_array();

        $daily = $this->db->query("
            SELECT DATE(created_at) as d, COUNT(*) as cnt
            FROM order_requests WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY d ORDER BY d ASC
        ")->result_array();

        return compact('countries','products','daily');
    }

    /** Détail télémédecine */
    private function _telemedecine_deep(): array {
        $specialties = $this->db->query("
            SELECT m.specialite, COUNT(c.id) as total,
                   SUM(CASE WHEN c.statut='terminee' THEN 1 ELSE 0 END) as completed
            FROM medecins m
            LEFT JOIN consultations c ON m.id = c.medecin_id
            GROUP BY m.id ORDER BY total DESC
        ")->result_array();

        $payment_status = $this->db->query("
            SELECT paiement_statut, COUNT(*) as cnt, COALESCE(SUM(prix_ttc),0) as total
            FROM consultations GROUP BY paiement_statut
        ")->result_array();

        $by_country = $this->db->query("
            SELECT p.pays as country, COUNT(c.id) as cnt
            FROM consultations c JOIN pays p ON c.country_id = p.id
            GROUP BY p.pays ORDER BY cnt DESC LIMIT 5
        ")->result_array();

        return compact('specialties','payment_status','by_country');
    }

    /** Engagement médias */
    private function _media_engagement(): array {
        $top_viewed = $this->db->query("
            SELECT gm.id_media, gm.titre, gm.type,
                   COUNT(mv.id) as views
            FROM galerie_medias gm
            LEFT JOIN media_views mv ON gm.id_media = mv.id_media
            WHERE gm.est_actif=1
            GROUP BY gm.id_media ORDER BY views DESC LIMIT 5
        ")->result_array();

        $top_liked = $this->db->query("
            SELECT gm.id_media, gm.titre, gm.type,
                   COUNT(ml.id) as likes
            FROM galerie_medias gm
            LEFT JOIN media_likes ml ON gm.id_media = ml.id_media
            WHERE gm.est_actif=1
            GROUP BY gm.id_media ORDER BY likes DESC LIMIT 5
        ")->result_array();

        $recent_comments = $this->db->query("
            SELECT mc.*, gm.titre as media_titre,
                   mc.author_name, mc.comment, mc.created_at
            FROM media_comments mc
            JOIN galerie_medias gm ON mc.id_media = gm.id_media
            ORDER BY mc.created_at DESC LIMIT 5
        ")->result_array();

        return compact('top_viewed','top_liked','recent_comments');
    }

    /** Réseaux sociaux */
    private function _social_network_stats(): array {
        return $this->db->order_by('nombre_participants','DESC')->get('statistiques_reseaux')->result_array();
    }

    /** Messages contact */
    private function _contact_messages(): array {
        return [
            'total'   => $this->db->count_all_results('contact_us'),
            'unread'  => $this->db->where('is_readed',0)->count_all_results('contact_us'),
            'today'   => $this->db->where('DATE(Date_creation)',date('Y-m-d'))->count_all_results('contact_us'),
            'latest'  => $this->db->order_by('Date_creation','DESC')->limit(4)->get('contact_us')->result_array(),
        ];
    }

    /* ══════════════════════════════════════════════════════
     *  TABLEAUX RÉCENTS
     * ══════════════════════════════════════════════════════ */

    private function _latest_users(int $n): array {
        return $this->db->query("
            SELECT u.*, r.nom as role_nom, r.slug as role_slug
            FROM users u LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _latest_orders(int $n): array {
        return $this->db->query("
            SELECT c.*, u.prenom, u.nom, u.email,
                   (SELECT COUNT(*) FROM commande_lignes cl WHERE cl.commande_id = c.id) as items
            FROM commandes c JOIN users u ON c.user_id = u.id
            ORDER BY c.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _latest_consultations(int $n): array {
        return $this->db->query("
            SELECT c.*,
                   p.prenom as pat_prenom, p.nom as pat_nom, p.telephone as pat_tel,
                   mu.prenom as med_prenom, mu.nom as med_nom, m.specialite
            FROM consultations c
            JOIN users p ON c.patient_id = p.id
            LEFT JOIN medecins m ON c.medecin_id = m.id
            LEFT JOIN users mu ON m.user_id = mu.id
            ORDER BY c.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _latest_order_requests(int $n): array {
        return $this->db->query("
            SELECT r.*, a.main_image
            FROM order_requests r
            LEFT JOIN advertise_product a ON r.product_id = a.id
            ORDER BY r.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _latest_investors(int $n): array {
        return $this->db->query("
            SELECT i.*, p.pays as country_name
            FROM investors i JOIN pays p ON i.id_pays = p.id
            ORDER BY i.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _latest_brokers(int $n): array {
        return $this->db->query("
            SELECT b.*, p.pays as country_name
            FROM brokers b JOIN pays p ON b.id_pays = p.id
            ORDER BY b.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    private function _upcoming_consultations(int $n): array {
        return $this->db->query("
            SELECT c.*,
                   p.prenom as pat_prenom, p.nom as pat_nom,
                   mu.prenom as med_prenom, m.specialite
            FROM consultations c
            JOIN users p ON c.patient_id = p.id
            LEFT JOIN medecins m ON c.medecin_id = m.id
            LEFT JOIN users mu ON m.user_id = mu.id
            WHERE c.date_souhaitee >= NOW()
            AND c.statut IN ('confirmee','en_attente')
            ORDER BY c.date_souhaitee ASC LIMIT {$n}
        ")->result_array();
    }

    private function _top_products_advertise(int $n): array {
        return $this->db->query("
            SELECT a.*, pc.name as category_name,
                   (SELECT COUNT(*) FROM order_requests r WHERE r.product_id = a.id) as order_count
            FROM advertise_product a
            LEFT JOIN product_categories pc ON a.category_id = pc.id
            WHERE a.is_active=1
            ORDER BY a.price_request_count DESC LIMIT {$n}
        ")->result_array();
    }

    private function _top_medias(int $n): array {
        return $this->db->query("
            SELECT gm.id_media, gm.titre, gm.type, gm.miniature,
                   COUNT(DISTINCT mv.id) as views,
                   COUNT(DISTINCT ml.id) as likes,
                   COUNT(DISTINCT mc.id) as comments
            FROM galerie_medias gm
            LEFT JOIN media_views mv ON gm.id_media = mv.id_media
            LEFT JOIN media_likes ml ON gm.id_media = ml.id_media
            LEFT JOIN media_comments mc ON gm.id_media = mc.id_media
            WHERE gm.est_actif=1
            GROUP BY gm.id_media ORDER BY views DESC LIMIT {$n}
        ")->result_array();
    }

    private function _recent_activities(int $n): array {
        return $this->db->query("
            SELECT ua.*, u.prenom, u.nom, u.photo, u.type_utilisateur
            FROM user_activities ua JOIN users u ON ua.user_id = u.id
            ORDER BY ua.created_at DESC LIMIT {$n}
        ")->result_array();
    }

    /* ══════════════════════════════════════════════════════
     *  GRAPHIQUES – SÉRIES TEMPORELLES
     * ══════════════════════════════════════════════════════ */

    private function _chart_labels(int $days): array {
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = date('d/m', strtotime("-{$i} days"));
        }
        return $labels;
    }

    private function _chart_count(string $table, int $days, string $date_col = 'created_at'): array {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data[] = (int)$this->db->where("DATE({$date_col})", $date)->count_all_results($table);
        }
        return $data;
    }

    private function _chart_series(string $table, string $col, int $days, string $where = ''): array {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $this->db->select("COALESCE(SUM({$col}),0) as v");
            $this->db->where("DATE(created_at)", $date);
            if ($where) $this->db->where($where);
            $data[] = (float)$this->db->get($table)->row()->v;
        }
        return $data;
    }

    private function _chart_visits_30d(): array {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data[] = (int)$this->db->where('visit_date', $date)->count_all_results('visitors_logs');
        }
        return $data;
    }

    private function _chart_medecin_consults(int $medecin_id, int $days): array {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data[] = (int)$this->db->where('medecin_id', $medecin_id)->where('DATE(created_at)', $date)->count_all_results('consultations');
        }
        return $data;
    }

    /* ══════════════════════════════════════════════════════
     *  RÉPARTITIONS (Doughnut / Pie)
     * ══════════════════════════════════════════════════════ */

    private function _dist(string $table, string $col): array {
        return $this->db->query("
            SELECT {$col} as label, COUNT(*) as value
            FROM {$table} GROUP BY {$col}
        ")->result_array();
    }

    private function _dist_investors_commitment(): array {
        return $this->db->query("
            SELECT commitment_range as label, COUNT(*) as value
            FROM investors GROUP BY commitment_range
        ")->result_array();
    }

    private function _brokers_country_dist(): array {
        return $this->db->query("
            SELECT p.pays as label, COUNT(b.id) as value
            FROM brokers b JOIN pays p ON b.id_pays = p.id
            GROUP BY p.pays ORDER BY value DESC LIMIT 8
        ")->result_array();
    }

    /* ══════════════════════════════════════════════════════
     *  ALERTES & ACTIONS
     * ══════════════════════════════════════════════════════ */

    private function _alerts(): array {
        $a = [];

        $pending_orders = $this->db->where('statut','en_attente')->count_all_results('commandes');
        if ($pending_orders > 0)
            $a[] = ['type'=>'warning','icon'=>'bx bx-cart','title'=>'Commandes en attente','msg'=>"{$pending_orders} commande(s) à traiter",'link'=>base_url('Commandes?statut=en_attente')];

        $pending_consults = $this->db->where('statut','en_attente')->count_all_results('consultations');
        if ($pending_consults > 0)
            $a[] = ['type'=>'info','icon'=>'bx bx-calendar','title'=>'Consultations en attente','msg'=>"{$pending_consults} consultation(s) à confirmer",'link'=>base_url('Consultations?statut=en_attente')];

        $unread_msgs = $this->db->where('is_readed',0)->count_all_results('contact_us');
        if ($unread_msgs > 0)
            $a[] = ['type'=>'danger','icon'=>'bx bx-envelope','title'=>'Messages non lus','msg'=>"{$unread_msgs} message(s) en attente",'link'=>base_url('Contact')];

        $pending_req = $this->db->where('order_status','pending')->count_all_results('order_requests');
        if ($pending_req > 0)
            $a[] = ['type'=>'success','icon'=>'bx bx-shopping-bag','title'=>'Demandes de commande','msg'=>"{$pending_req} demande(s) de produits",'link'=>base_url('OrderRequests')];

        $wa_queue = $this->db->where('status','pending')->count_all_results('whatsapp_queue');
        if ($wa_queue > 0)
            $a[] = ['type'=>'warning','icon'=>'bx bxl-whatsapp','title'=>'File WhatsApp','msg'=>"{$wa_queue} message(s) en file",'link'=>base_url('Whatsapp')];

        return $a;
    }

    private function _quick_actions(): array {
        return [
            ['title'=>'Nouvel utilisateur',    'icon'=>'bx bx-user-plus',   'color'=>'primary', 'link'=>base_url('Users/create')],
            ['title'=>'Ajouter produit',       'icon'=>'bx bx-package',      'color'=>'success', 'link'=>base_url('Products/create')],
            ['title'=>'Planifier consultation','icon'=>'bx bx-calendar-plus','color'=>'info',    'link'=>base_url('Consultations/create')],
            ['title'=>'Ajouter média',         'icon'=>'bx bx-image-add',    'color'=>'warning', 'link'=>base_url('Galerie/create')],
            ['title'=>'Envoyer newsletter',    'icon'=>'bx bx-mail-send',    'color'=>'danger',  'link'=>base_url('Newsletter')],
            ['title'=>'Broadcast WhatsApp',    'icon'=>'bx bxl-whatsapp',    'color'=>'success', 'link'=>base_url('Whatsapp/broadcast')],
            ['title'=>'Paramètres',            'icon'=>'bx bx-cog',          'color'=>'dark',    'link'=>base_url('Configurations')],
        ];
    }

    private function _pending_verif(): array {
        return [
            'users_inactive'     => $this->db->where('is_active',0)->where('deleted_at IS NULL',null,false)->count_all_results('users'),
            'unverified_email'   => $this->db->where('email_verified_at IS NULL',null,false)->count_all_results('users'),
            'consults_pending'   => $this->db->where('statut','en_attente')->count_all_results('consultations'),
            'orders_pending'     => $this->db->where('statut','en_attente')->count_all_results('commandes'),
            'req_pending'        => $this->db->where('order_status','pending')->count_all_results('order_requests'),
            'contact_unread'     => $this->db->where('is_readed',0)->count_all_results('contact_us'),
        ];
    }

    private function _system_health(): array {
        return [
            'total_logs'   => $this->db->count_all_results('logs'),
            'errors_today' => $this->db->where('niveau','error')->where('DATE(created_at)',date('Y-m-d'))->count_all_results('logs'),
            'warnings'     => $this->db->where('niveau','warning')->where('DATE(created_at)',date('Y-m-d'))->count_all_results('logs'),
            'logins_today' => $this->db->where('action','login_success')->where('DATE(created_at)',date('Y-m-d'))->count_all_results('logs'),
            'failed_logins'=> $this->db->where('action','login_failed')->where('DATE(created_at)',date('Y-m-d'))->count_all_results('logs'),
        ];
    }

    /* ══════════════════════════════════════════════════════
     *  API AJAX
     * ══════════════════════════════════════════════════════ */

    public function api_stats() {
        if (!$this->input->is_ajax_request()) show_404();

        $type = $this->input->get('type');
        $resp = ['success' => true, 'ts' => date('c')];

        switch ($type) {
            case 'realtime':
                $resp['data'] = [
                    'online'        => count($this->db->where('is_active',1)->where('last_activity >=', date('Y-m-d H:i:s', strtotime('-15 minutes')))->get('user_sessions')->result_array()),
                    'today_visits'  => $this->db->where('visit_date',date('Y-m-d'))->count_all_results('visitors_logs'),
                    'pending_orders'=> $this->db->where('statut','en_attente')->count_all_results('commandes'),
                    'pending_req'   => $this->db->where('order_status','pending')->count_all_results('order_requests'),
                    'wa_queue'      => $this->db->where('status','pending')->count_all_results('whatsapp_queue'),
                    'server_time'   => date('H:i:s'),
                ];
                break;
            default:
                $resp = ['success' => false, 'error' => 'Type inconnu'];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($resp));
    }

    /* ══════════════════════════════════════════════════════
     *  HELPERS PRIVÉS
     * ══════════════════════════════════════════════════════ */

    private function _is_admin():   bool { return $this->session->userdata('role_slug') === 'admin'; }
    private function _is_medecin(): bool { return $this->session->userdata('role_slug') === 'medecin'; }

    private function _touch_session(): void {
        $uid = $this->session->userdata('user_id');
        if ($uid) {
            $this->db->where('id', $uid)->update('users', ['last_login_at' => date('Y-m-d H:i:s')]);
        }
    }
}