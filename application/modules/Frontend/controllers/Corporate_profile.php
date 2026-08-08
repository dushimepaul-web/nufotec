<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Corporate_profile - Gestion des pages dynamiques avec sections (multilingue)
 */
class Corporate_profile extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('text');
    }

    public function index($lang = null) {
        if ($lang === null) {
            $lang = $this->current_lang ?? 'fr';
        }

        $slug = 'about';
        $page = static_pages_one([
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (!$page) {
            show_404();
        }

        $page['titre_page']       = $page['titre_page'] ?? '';
        $page['contenu_page']     = $page['contenu_page'] ?? '';
        $page['meta_description'] = $page['meta_description'] ?? '';

        $sections = static_sections_where([
            'id_page'    => $page['id_page'],
            'est_active' => 1,
            'deleted_at' => null
        ], 'ordre', 'ASC');

        foreach ($sections as &$sec) {
            $sec['titre_section'] = $sec['titre_section'] ?? '';
            $sec['sous_titre']    = $sec['sous_titre']    ?? '';
            $sec['contenu_texte'] = $sec['contenu_texte'] ?? '';
            $sec['bouton_texte']  = $sec['bouton_texte']  ?? '';

            $sec['options'] = !empty($sec['options_json']) ? json_decode($sec['options_json'], true) : [];
            if (!is_array($sec['options'])) $sec['options'] = [];
        }

        $children = static_pages_where([
            'menu_parent_id' => $page['id_page'],
            'est_publiee' => 1
        ], 'menu_ordre', 'ASC');
        foreach ($children as &$child) {
            $child['titre_page'] = $child['titre_page'] ?? '';
        }

        $seo = $this->_prepare_seo_data($page, $lang);
        $extra_data = $this->load_sections_data($sections, $lang);

        $data = array_merge([
            'page'     => $page,
            'sections' => $sections,
            'children' => $children,
            'lang'     => $lang,
        ], $extra_data, $seo);

        $this->load->view('Corporate_profile', $data);
    }

    private function _prepare_seo_data($page, $lang) {
        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $site_description = $this->Model->get_setting('site_description', 'Pionniers de la phytothérapie africaine');

        $meta_title = (!empty($page['meta_title']) ? $page['meta_title'] : $page['titre_page']) . ' - ' . $site_name;
        $meta_description = !empty($page['meta_description']) ? $page['meta_description'] : $site_description;

        return [
            'site_name'        => $site_name,
            'site_description' => $site_description,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords'    => $page['meta_keywords'] ?? '',
            'og_image'         => !empty($page['image_social']) ? base_url($page['image_social']) : base_url('assets/images/og-default.jpg'),
            'canonical_url'    => base_url($lang . '/' . ($page['slug'] ?? ''))
        ];
    }

    private function load_sections_data($sections, $lang) {
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
            'ressources_telechargeables' => '_load_resources',
            'risques_mitigations' => '_load_risks',
            'statistiques_reseaux' => '_load_social_stats',
        ];

        $data = [];
        $loaded_types = [];

        foreach ($sections as $section) {
            $type = $section['type_section'];
            $options = $section['options'];

            if (!isset($loaded_types[$type]) && isset($loaders[$type])) {
                $loader_method = $loaders[$type];
                $result = $this->$loader_method($options, $lang);
                if (!empty($result)) {
                    $data = array_merge($data, $result);
                }
                $loaded_types[$type] = true;
            }
        }

        return $data;
    }

    private function _load_team($options = [], $lang) {
        $members = [];
        if ($this->db->table_exists('equipe')) {
            $members = $this->Model->read('equipe', ['est_actif' => 1], 'id', 'ASC');
        }
        return ['members' => $members];
    }

    private function _load_partners($options = [], $lang) {
        $partners = $this->Model->read('partenaires', ['est_actif' => 1, 'deleted_at' => NULL], 'id_partenaire', 'DESC');
        foreach ($partners as &$partner) {
            $partner['nom'] = $partner['nom'] ?? '';
            $partner['description'] = $partner['description'] ?? '';
            $partner['logo_url'] = !empty($partner['logo_url']) ? base_url($partner['logo_url']) : base_url('attachments/partenaires/default-logo.png');
        }
        return ['partners' => $partners];
    }

    private function _load_testimonials($options = [], $lang) {
        $testimonials = $this->Model->read('temoignages', ['est_approuve' => 1], 'date_reception', 'DESC');
        return ['testimonials' => $testimonials];
    }

    private function _load_stats($options = [], $lang) {
        $stats = $this->Model->read('chiffres_cles', [], 'ordre', 'ASC');
        return ['statistics' => $stats];
    }

    private function _load_certifications($options = [], $lang) {
        $certs = [];
        if ($this->db->table_exists('licences_certifications')) {
            $certs = $this->Model->read('licences_certifications', ['est_actif' => 1], 'id', 'DESC');
        }
        return ['certifications' => $certs];
    }

    private function _load_phases($options = [], $lang) {
        $phases = $this->Model->read('investissement_phases', [], 'annee_debut', 'ASC');
        return ['phases' => $phases];
    }

    private function _load_products($options = [], $lang) {
        $products = [];
        if ($this->db->table_exists('produits')) {
            $products = $this->Model->read('produits', ['est_actif' => 1], 'id', 'DESC');
        }
        return ['products' => $products];
    }

    private function _load_posts($options = [], $lang) {
        $posts = $this->Model->read('actualites_blog', ['est_publiee' => 1], 'date_publication', 'DESC', 3);
        return ['posts' => $posts];
    }

    private function _load_faq($options = [], $lang) {
        $faqs = $this->Model->read('faq', ['est_publiee' => 1], 'ordre', 'ASC');
        return ['faqs' => $faqs];
    }

    private function _load_gallery($options = [], $lang) {
        $medias = $this->Model->read('galerie_medias', [], 'date_prise', 'DESC');
        return ['medias' => $medias];
    }

    private function _load_resources($options = [], $lang) {
        $resources = [];
        if ($this->db->table_exists('ressources_telechargeables')) {
            $resources = $this->Model->read('ressources_telechargeables', ['est_public' => 1], 'id', 'DESC');
        }
        return ['resources' => $resources];
    }

    private function _load_risks($options = [], $lang) { return []; }
    private function _load_social_stats($options = [], $lang) { return []; }
}
