<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vision_Mission extends Public_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('text');
        $this->load->model('Model');
    }

    /**
     * Page Vision & Mission multilingue
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        // Langue : paramètre ou celle de MY_Controller
        if ($lang === null) {
            $lang = $this->current_lang;
        }

        // Slug fixe pour cette page
        $slug = 'vision-mission';

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

        // 3. Récupération des sections actives (toutes les sections, pas seulement hero)
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

            // Nettoyage des colonnes de traduction
            unset($sec["titre_section_{$lang}"], $sec["sous_titre_{$lang}"], $sec["contenu_texte_{$lang}"], $sec["bouton_texte_{$lang}"]);
        }

        // 5. Récupération des appels à action (traduits)
        $appels_action = $this->Model->read('appels_action', null, 'ordre', 'ASC');
        foreach ($appels_action as &$action) {
            $action['titre']        = $action["titre_{$lang}"] ?? $action['titre_fr'] ?? $action['titre'] ?? '';
            $action['description']  = $action["description_{$lang}"] ?? $action['description_fr'] ?? $action['description'] ?? '';
            $action['bouton_texte'] = $action["bouton_texte_{$lang}"] ?? $action['bouton_texte_fr'] ?? $action['bouton_texte'] ?? '';
        }
        $data['appels_action'] = $appels_action;

        // 6. Récupération des company_statements (déclarations vision/mission) - traduits
        // Note: La table company_statements utilise 'title' et 'description' (pas 'titre' et 'contenu')
        $statements = $this->Model->read('company_statements', ['is_active' => 1], 'order', 'ASC');
        foreach ($statements as &$stmt) {
            // Traduction du titre (colonne 'title')
            $stmt['title'] = $stmt["title_{$lang}"] ?? $stmt['title_fr'] ?? $stmt['title'] ?? '';
            
            // Traduction de la description (colonne 'description')
            $stmt['description'] = $stmt["description_{$lang}"] ?? $stmt['description_fr'] ?? $stmt['description'] ?? '';
            
            // Pour compatibilité avec l'ancienne vue qui pourrait utiliser 'titre' ou 'contenu'
            $stmt['titre'] = $stmt['title'];
            $stmt['contenu'] = $stmt['description'];
        }
        $data['statements'] = $statements;

        // 7. Récupération de la section hero (pour compatibilité avec la vue existante)
        $hero = null;
        foreach ($sections as $sec) {
            if ($sec['type_section'] === 'hero') {
                $hero = $sec;
                break;
            }
        }
        $data['hero_section'] = $hero;

        // 8. Sections pour la vue (si la vue utilise $sections directement)
        $data['sections'] = $sections;

        // 9. Données de la page
        $data['page'] = $page;
        $data['lang'] = $lang;

        // 10. Données SEO
        $seo = $this->_prepare_seo_data($page, $lang);
        $data = array_merge($data, $seo);

        $this->load->view('Vision_Mission', $data);
    }

    /**
     * Prépare les métadonnées SEO
     * @param array $page Données de la page
     * @param string $lang Code langue
     * @return array Métadonnées SEO
     */
    private function _prepare_seo_data($page, $lang) {
        $site_name = $this->Model->get_setting('site_name', 'NUFOTEC Phytomed');
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
}