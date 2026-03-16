

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Esg_Sustainability extends Public_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page dynamique universelle
     */
    public function index($slug = 'esg-sustainability') {
        // 1. Sécurisation et nettoyage du slug
        $slug = url_title($slug, '-', TRUE);

        // 2. Récupération de la page parente
        $page = $this->Model->readOne('pages', [
            'slug'        => $slug,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            show_404();
        }

        // 3. Récupération de TOUTES les sections actives de cette page
        $sections = $this->Model->read(
            'sections_contenu',
            ['id_page' => $page['id_page'], 'est_active' => 1],
            'ordre',
            'ASC'
        );

        // 4. Traitement des sections et agrégation des données extra
        $extra_data = [];
        foreach ($sections as &$section) {
            // Décodage JSON unique
            $section['options'] = !empty($section['options_json']) 
                ? json_decode($section['options_json'], true) 
                : [];
            
            if (!is_array($section['options'])) $section['options'] = [];

            // Chargement des données spécifiques au type de section (ex: équipe, produits)
            // On fusionne sans écraser les données précédentes
            $section_data = $this->load_section_data($section['type_section'], $section['options']);
            $extra_data = array_merge($extra_data, $section_data);
        }

        

        // 6. Sous-pages (pour navigation latérale ou menu enfant)
        $children = $this->Model->read('pages', 
            ['menu_parent_id' => $page['id_page'], 'est_publiee' => 1], 
            'menu_ordre', 'ASC'
        );

        // 7. Assemblage final
        $data = array_merge([
            'page'             => $page,
            'sections'         => $sections,
            'children'         => $children ?: [],
        ], $extra_data);

        $this->load->view('Corporate_profile', $data);
    }

    /**
     * Centralisation des requêtes par type de section pour éviter la redondance
     */
    private function load_section_data($type, $options = []) {
        $data = [];
        $limit = isset($options['limit']) ? (int)$options['limit'] : NULL;

        switch ($type) {
            case 'team': case 'equipe':
                $data['members'] = $this->Model->read('equipe', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'partenaires': case 'partners_logos':
                $data['partners'] = $this->Model->read('partenaires', ['est_actif' => 1], 'ordre', 'ASC');
                break;
            case 'produits': case 'product_grid':
                $data['products'] = $this->Model->read('produits', ['est_publie' => 1], 'ordre', 'ASC', $limit ?: 6);
                break;
            case 'actualites_blog': case 'blog_posts':
                $data['posts'] = $this->Model->read('actualites_blog', ['est_publie' => 1], 'date_publication', 'DESC', $limit ?: 3);
                break;
            case 'chiffres': case 'stats_counter':
                $data['statistics'] = $this->Model->read('chiffres_cles', ['est_actif' => 1], 'ordre', 'ASC');
                break;
            case 'faq':
                $data['faqs'] = $this->Model->read('faq', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'galerie_medias': case 'gallery':
                $data['medias'] = $this->Model->read('galerie_medias', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'investment_cards':
                $data['phases'] = $this->Model->read('investissement_phases', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'etapes_projet': case 'workflow': case 'timeline':
                $data['steps'] = $this->Model->read('etapes_projet', NULL, 'date_debut', 'ASC');
                break;
            case 'services':
                $data['services'] = $this->Model->read('services', ['est_actif' => 1], 'ordre', 'ASC');
                break;
            // Ajoutez d'autres types ici si nécessaire...
        }
        return $data;
    }

    /**
     * Alias de route
     */
    public function page($slug = 'esg-sustainability') {
        $this->index($slug);
    }
}