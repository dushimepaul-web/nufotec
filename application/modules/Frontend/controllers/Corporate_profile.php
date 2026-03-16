<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Corporate_profile - Gestion des pages dynamiques avec sections
 * 
 * Optimisé pour éviter la duplication de code et adapter aux tables existantes.
 */
class Corporate_profile extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('text');
    }

    /**
     * Affiche une page dynamique avec ses sections
     * @param string $slug Slug de la page (par défaut 'about')
     */
    public function index($slug = 'about') {
        // Récupérer la page par slug (publiee)
        $page = $this->Model->readOne('pages', [
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (!$page) {
            show_404();
        }

        // Récupérer les sections actives de cette page
        $sections = $this->Model->read('sections_contenu', [
            'id_page' => $page['id_page'],
            'est_active' => 1
        ], 'ordre', 'ASC');

        // Parser les options JSON
        foreach ($sections as &$section) {
            $section['options'] = !empty($section['options_json'])
                ? json_decode($section['options_json'], true)
                : [];
        }

        // Récupérer les pages enfants pour le sous-menu
        $children = $this->Model->read('pages', [
            'menu_parent_id' => $page['id_page'],
            'est_publiee' => 1
        ], 'menu_ordre', 'ASC');

        // Préparer les données de la vue
        $data = [
            'page'      => $page,
            'sections'  => $sections,
            'children'  => $children,
            'site_name' => $this->Model->get_setting('site_name', 'AGF Phytomed')
        ];

        // Charger les données supplémentaires nécessaires aux sections (une seule fois par type)
        $extra_data = $this->load_sections_data($sections);
        $data = array_merge($data, $extra_data);

        // Charger la vue
        $this->load->view('Corporate_profile', $data);
    }

    /**
     * Charge les données nécessaires pour tous les types de sections présents
     * Évite les requêtes multiples pour un même type.
     *
     * @param array $sections Liste des sections de la page
     * @return array Données additionnelles indexées par type (ex: 'members', 'partners', ...)
     */
    private function load_sections_data($sections) {
        // Définir le mapping type de section => méthode de chargement
        $loaders = [
            'equipe'        => '_load_team',
            'partenaires'   => '_load_partners',
            'temoignages'   => '_load_testimonials',
            'chiffres'      => '_load_stats',
            'certifications'=> '_load_certifications',
            'investment_cards' => '_load_phases',
            'produits'      => '_load_products',
            'actualites_blog'=> '_load_posts',
            'faq'           => '_load_faq',
            'galerie_medias'=> '_load_gallery',
            'evenements'    => '_load_events',
            'ressources_telechargeables' => '_load_resources',
            'risques_mitigations' => '_load_risks',
            'statistiques_reseaux' => '_load_social_stats',
        ];

        $data = [];
        $loaded_types = [];

        foreach ($sections as $section) {
            $type = $section['type_section'];
            $options = $section['options'];

            // Si ce type n'a pas encore été traité et qu'un loader existe
            if (!isset($loaded_types[$type]) && isset($loaders[$type])) {
                $loader_method = $loaders[$type];
                $result = $this->$loader_method($options);
                if (!empty($result)) {
                    $data = array_merge($data, $result);
                }
                $loaded_types[$type] = true;
            }
        }

        return $data;
    }

    // ========================================================================
    // Méthodes de chargement spécifiques (une par type de section)
    // ========================================================================

    private function _load_team($options = []) {
        $members = $this->Model->read('equipe', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        return ['members' => $members];
    }

    private function _load_partners($options = []) {
        $partners = $this->Model->read('partenaires', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        return ['partners' => $partners];
    }

    private function _load_testimonials($options = []) {
        $testimonials = $this->Model->read('temoignages', ['est_approuve' => 1], 'date_reception', 'DESC');
        return ['testimonials' => $testimonials];
    }

    private function _load_stats($options = []) {
        $stats = $this->Model->read('chiffres_cles', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        return ['statistics' => $stats];
    }

    private function _load_certifications($options = []) {
        // Table licences_certifications – à vérifier si elle existe
        $certs = $this->Model->read('licences_certifications', ['est_actif' => 1], 'date_obtention', 'DESC');
        return ['certifications' => $certs];
    }

    private function _load_phases($options = []) {
        $phases = $this->Model->read('investissement_phases', [], 'annee_debut', 'ASC');
        return ['phases' => $phases];
    }

    private function _load_products($options = []) {
        $limit = $options['limit'] ?? 6;
        $this->db->select('p.*, c.nom_categorie as categorie_nom');
        $this->db->from('produits p');
        $this->db->join('categories c', 'p.id_categorie = c.id_categorie', 'left');
        $this->db->where('p.est_actif', 1);
        $this->db->order_by('p.ordre_affichage', 'ASC');
        $this->db->limit($limit);
        $products = $this->db->get()->result_array();
        return ['products' => $products];
    }

    private function _load_posts($options = []) {
        $limit = $options['limit'] ?? 3;
        $posts = $this->Model->read('actualites_blog', ['est_publiee' => 1], 'date_publication', 'DESC', $limit);
        return ['posts' => $posts];
    }

    private function _load_faq($options = []) {
        $faqs = $this->Model->read('faq', ['est_publiee' => 1], 'ordre', 'ASC');
        return ['faqs' => $faqs];
    }

    private function _load_gallery($options = []) {
        $medias = $this->Model->read('galerie_medias', [], 'date_prise', 'DESC');
        return ['medias' => $medias];
    }

    private function _load_events($options = []) {
        $events = $this->Model->read('evenements', ['est_public' => 1, 'date_debut >= ' => date('Y-m-d')], 'date_debut', 'ASC');
        return ['events' => $events];
    }

    private function _load_resources($options = []) {
        $resources = $this->Model->read('ressources_telechargeables', ['est_public' => 1], 'date_publication', 'DESC');
        return ['resources' => $resources];
    }

    private function _load_risks($options = []) {
        $risks = $this->Model->read('risques_mitigations', [], 'ordre', 'ASC');
        return ['risks' => $risks];
    }

    private function _load_social_stats($options = []) {
        $social = $this->Model->read('statistiques_reseaux', [], 'date_mesure', 'DESC');
        return ['social_stats' => $social];
    }

    /**
     * Méthode alternative pour charger une page spécifique (ex: corporate_profile/page/about)
     */
    public function page($slug = 'about') {
        $this->index($slug);
    }
}