<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
    public function __construct()
    {
        parent::__construct(); // MY_Controller gère déjà la langue
        $this->load->helper('text');
        $this->load->model('Model');
    }

    /**
     * Page d'accueil dynamique (multilingue)
     */
    public function index()
    {
        $this->Model->log_visit();
        $lang = $this->current_lang;

        $page = $this->Model->readOne('pages', ['slug' => 'home', 'est_publiee' => 1]);
        if (!$page) show_404();

        // Traduction des champs de la page
        $page['titre_page']       = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
        $page['contenu_page']     = $page["contenu_page_{$lang}"] ?? $page['contenu_page_fr'] ?? '';
        $page['meta_description'] = $page["meta_description_{$lang}"] ?? $page['meta_description_fr'] ?? '';

        $data['page'] = $page;
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        $data['lang'] = $lang;

        // Données spécifiques à la home
        $data['slides']   = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
        $data['chiffres'] = $this->Model->read('chiffres_cles', ['id_page_associee' => $page['id_page']], 'ordre', 'ASC');
        $data['appels_action'] = $this->Model->get_appels_action_translated($lang);

        // Données communes (sections, enfants, etc.)
        $data = array_merge($data, $this->_get_page_data($page['id_page']));

        // SEO
        $data = array_merge($data, $this->_prepare_seo_data($page));
        $data['meta_title']       = $page['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $page['meta_description'] ?? $this->Model->get_setting('site_description');

        $this->load->view('Home_View', $data);
    }

    /**
     * Afficher une page dynamique par slug (multilingue)
     */
    public function view($slug = null)
    {
        if (!$slug) redirect('/');

        $slug = $this->security->xss_clean($slug);
        if (in_array($slug, $this->available_langs)) redirect($slug);

        $page = $this->Model->readOne('pages', ['slug' => $slug, 'est_publiee' => 1]);
        if (!$page) show_404();

        $lang = $this->current_lang;
        $page['titre_page']       = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
        $page['contenu_page']     = $page["contenu_page_{$lang}"] ?? $page['contenu_page_fr'] ?? '';
        $page['meta_description'] = $page["meta_description_{$lang}"] ?? $page['meta_description_fr'] ?? '';

        $data['page'] = $page;
        $data['lang'] = $lang;

        // Données communes
        $data = array_merge($data, $this->_get_page_data($page['id_page']));
        $data = array_merge($data, $this->_load_contextual_data($page));
        $data = array_merge($data, $this->_prepare_seo_data($page));

        $view_name = $this->_determine_view($page);
        $this->load->view($view_name, $data);
    }

    /**
     * Récupère toutes les données communes à une page (sections, enfants, breadcrumb)
     */
    private function _get_page_data($page_id)
    {
        $lang = $this->current_lang;
        $data = [];

        // ----- 1. Sections de contenu (traduction complète) -----
        $sections = $this->Model->read('sections_contenu', ['id_page' => $page_id], 'ordre', 'ASC');

        foreach ($sections as &$sec) {
            // Traduction des champs textuels
            $sec['titre_section']  = $sec["titre_section_{$lang}"]  ?? $sec['titre_section_fr']  ?? $sec['titre_section']  ?? '';
            $sec['sous_titre']     = $sec["sous_titre_{$lang}"]     ?? $sec['sous_titre_fr']     ?? $sec['sous_titre']     ?? '';
            $sec['contenu_texte']  = $sec["contenu_texte_{$lang}"]  ?? $sec['contenu_texte_fr']  ?? $sec['contenu_texte']  ?? '';
            $sec['bouton_texte']   = $sec["bouton_texte_{$lang}"]   ?? $sec['bouton_texte_fr']   ?? $sec['bouton_texte']   ?? '';
            
            // Nettoyage : suppression des colonnes de traduction pour éviter les doublons
            unset($sec["titre_section_{$lang}"], $sec["sous_titre_{$lang}"], $sec["contenu_texte_{$lang}"], $sec["bouton_texte_{$lang}"]);
        }
        $data['sections'] = $sections;

        // ----- 2. Pages enfants (sous-pages) -----
        $children = $this->Model->read('pages', ['menu_parent_id' => $page_id, 'est_publiee' => 1], 'menu_ordre', 'ASC');
        foreach ($children as &$child) {
            $child['titre_page'] = $child["titre_page_{$lang}"] ?? $child['titre_page_fr'] ?? $child['titre_page'];
        }
        $data['children'] = $children;

        // ----- 3. Pages sœurs (même parent) -----
        $parent = $this->Model->readOne('pages', ['id_page' => $page_id]);
        $parent_id = $parent['menu_parent_id'] ?? null;
        if ($parent_id) {
            $siblings = $this->Model->read('pages', ['menu_parent_id' => $parent_id, 'est_publiee' => 1, 'id_page !=' => $page_id], 'menu_ordre', 'ASC');
            foreach ($siblings as &$sib) {
                $sib['titre_page'] = $sib["titre_page_{$lang}"] ?? $sib['titre_page_fr'] ?? $sib['titre_page'];
            }
            $data['siblings'] = $siblings;
        }

        // ----- 4. Fil d'Ariane -----
        $data['breadcrumb'] = $this->_build_breadcrumb($page_id);

        return $data;
    }

    /**
     * Construit le fil d'Ariane (multilingue)
     */
    private function _build_breadcrumb($page_id)
    {
        $lang = $this->current_lang;
        $breadcrumb = [];

        while ($page_id) {
            $page = $this->Model->readOne('pages', ['id_page' => $page_id]);
            if (!$page) break;

            $titre = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
            $url = ($page['slug'] === 'home')
                ? base_url($lang)
                : base_url($lang . '/' . $page['slug']);

            array_unshift($breadcrumb, [
                'titre' => $titre,
                'slug'  => $page['slug'],
                'url'   => $url
            ]);

            $page_id = $page['menu_parent_id'];
        }

        return $breadcrumb;
    }

    /**
     * Prépare les métadonnées SEO
     */
    private function _prepare_seo_data($page)
    {
        $lang = $this->current_lang;
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
     * Helper pour traduire les chaînes statiques (fichier de langue)
     */
    public function t($key)
    {
        return $this->lang->line($key);
    }

    // --------------------------------------------------------------------
    // Méthodes à conserver (Abonner, unsubscribe, etc.)
    // Elles n'ont pas besoin de modifications pour la traduction des sections
    // --------------------------------------------------------------------
}