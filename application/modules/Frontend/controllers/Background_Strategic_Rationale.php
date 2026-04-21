<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Background_Strategic_Rationale extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper(['text', 'url']);
    }

    /**
     * Page dynamique multilingue
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        // Langue : paramètre ou celle de MY_Controller
        if ($lang === null) {
            $lang = $this->current_lang;
        }

        // Slug fixe pour cette page
        $slug = 'background-strategic-rationale';

        // 1. Récupération de la page
        $page = $this->Model->readOne('pages', [
            'slug'        => $slug,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            show_404();
        }

        // 2. Traduction des champs de la page
        $page['titre_page']       = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
        $page['contenu_page']     = $page["contenu_page_{$lang}"] ?? $page['contenu_page_fr'] ?? '';
        $page['meta_description'] = $page["meta_description_{$lang}"] ?? $page['meta_description_fr'] ?? '';

        // 3. Récupération des sections actives
        $sections = $this->Model->read(
            'sections_contenu',
            ['id_page' => $page['id_page'], 'est_active' => 1],
            'ordre',
            'ASC'
        );

        // 4. Traduction des sections et décodage JSON
        foreach ($sections as &$sec) {
            // Champs textuels traduits
            $sec['titre_section'] = $sec["titre_section_{$lang}"] ?? $sec['titre_section_fr'] ?? $sec['titre_section'] ?? '';
            $sec['sous_titre']    = $sec["sous_titre_{$lang}"]    ?? $sec['sous_titre_fr']    ?? $sec['sous_titre']    ?? '';
            $sec['contenu_texte'] = $sec["contenu_texte_{$lang}"] ?? $sec['contenu_texte_fr'] ?? $sec['contenu_texte'] ?? '';
            $sec['bouton_texte']  = $sec["bouton_texte_{$lang}"]  ?? $sec['bouton_texte_fr']  ?? $sec['bouton_texte']  ?? '';

            // Décodage JSON des options
            $sec['options'] = !empty($sec['options_json']) ? json_decode($sec['options_json'], true) : [];
            if (!is_array($sec['options'])) $sec['options'] = [];

            // Nettoyage des colonnes de traduction (optionnel)
            unset($sec["titre_section_{$lang}"], $sec["sous_titre_{$lang}"], $sec["contenu_texte_{$lang}"], $sec["bouton_texte_{$lang}"]);
        }

        // 5. Chargement des données additionnelles selon les types de section
        $extra_data = [];
        foreach ($sections as $section) {
            $section_data = $this->load_section_data($section['type_section'], $section['options']);
            $extra_data = array_merge($extra_data, $section_data);
        }

        // 6. Sous-pages pour navigation latérale (traduites)
        $children = $this->Model->read('pages', 
            ['menu_parent_id' => $page['id_page'], 'est_publiee' => 1], 
            'menu_ordre', 'ASC'
        );
        foreach ($children as &$child) {
            $child['titre_page'] = $child["titre_page_{$lang}"] ?? $child['titre_page_fr'] ?? $child['titre_page'];
        }

        // 7. Données SEO
        $seo = $this->_prepare_seo_data($page, $lang);

        // 8. Assemblage final
        $data = array_merge([
            'page'     => $page,
            'sections' => $sections,
            'children' => $children ?: [],
            'lang'     => $lang,
        ], $extra_data, $seo);

        $this->load->view('Corporate_profile', $data);
    }

    /**
     * Charge les données spécifiques selon le type de section
     * (ex: équipe, produits, etc.)
     */
    private function load_section_data($type, $options = []) {
        $data = [];
        $limit = isset($options['limit']) ? (int)$options['limit'] : null;

        switch ($type) {
            case 'team':
            case 'equipe':
                $data['members'] = $this->Model->read('equipe', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'partenaires':
            case 'partners_logos':
                $data['partners'] = $this->Model->read('partenaires', ['est_actif' => 1], 'ordre', 'ASC');
                break;
            case 'produits':
            case 'product_grid':
                $data['products'] = $this->Model->read('produits', ['est_publie' => 1], 'ordre', 'ASC', $limit ?: 6);
                break;
            case 'actualites_blog':
            case 'blog_posts':
                $data['posts'] = $this->Model->read('actualites_blog', ['est_publie' => 1], 'date_publication', 'DESC', $limit ?: 3);
                break;
            case 'chiffres':
            case 'stats_counter':
                $data['statistics'] = $this->Model->read('chiffres_cles', ['est_actif' => 1], 'ordre', 'ASC');
                break;
            case 'faq':
                $data['faqs'] = $this->Model->read('faq', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'galerie_medias':
            case 'gallery':
                $data['medias'] = $this->Model->read('galerie_medias', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'investment_cards':
                $data['phases'] = $this->Model->read('investissement_phases', ['est_active' => 1], 'ordre', 'ASC');
                break;
            case 'etapes_projet':
            case 'workflow':
            case 'timeline':
                $data['steps'] = $this->Model->read('etapes_projet', null, 'date_debut', 'ASC');
                break;
            case 'services':
                $data['services'] = $this->Model->read('services', ['est_actif' => 1], 'ordre', 'ASC');
                break;
        }
        return $data;
    }

    /**
     * Prépare les métadonnées SEO
     */
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

    /**
     * Alias de route pour compatibilité
     */
    public function page($slug = 'background-strategic-rationale') {
        $this->index($slug);
    }
}