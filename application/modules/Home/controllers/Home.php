<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
        $this->load->model('Model');
    }

    /**
     * Page d'accueil dynamique (français uniquement)
     */
    public function index()
    {
        $this->Model->log_visit();

        $data['show_translator'] = false;

        $page = $this->Model->readOne('pages', ['slug' => 'home', 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');

        // Données spécifiques à la home
        $data['slides']   = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
        $data['chiffres'] = $this->Model->read('chiffres_cles', ['id_page_associee' => $page['id_page']], 'ordre', 'ASC');
        $data['appels_action'] = $this->Model->get_appels_action_translated('fr');

        // Données communes (sections, enfants, etc.)
        $data = array_merge($data, $this->_get_page_data($page['id_page']));

        // SEO
        $data = array_merge($data, $this->_prepare_seo_data($page));
        $data['meta_title']       = $page['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $page['meta_description'] ?? $this->Model->get_setting('site_description');

        $this->load->view('Home_View', $data);
    }

    /**
     * Afficher une page dynamique par slug (français uniquement)
     */
    public function view($slug = null)
    {
        if (!$slug) redirect('/');

        $slug = $this->security->xss_clean($slug);

        $page = $this->Model->readOne('pages', ['slug' => $slug, 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;

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
        $data = [];

        // ----- 1. Sections de contenu (français uniquement) -----
        $sections = $this->Model->read('sections_contenu', ['id_page' => $page_id], 'ordre', 'ASC');
        $data['sections'] = $sections;

        // ----- 2. Pages enfants (sous-pages) -----
        $children = $this->Model->read('pages', ['menu_parent_id' => $page_id, 'est_publiee' => 1], 'menu_ordre', 'ASC');
        $data['children'] = $children;

        // ----- 3. Pages sœurs (même parent) -----
        $parent = $this->Model->readOne('pages', ['id_page' => $page_id]);
        $parent_id = $parent['menu_parent_id'] ?? null;
        if ($parent_id) {
            $siblings = $this->Model->read('pages', ['menu_parent_id' => $parent_id, 'est_publiee' => 1, 'id_page !=' => $page_id], 'menu_ordre', 'ASC');
            $data['siblings'] = $siblings;
        }

        // ----- 4. Fil d'Ariane -----
        $data['breadcrumb'] = $this->_build_breadcrumb($page_id);

        return $data;
    }

    /**
     * Construit le fil d'Ariane
     */
    private function _build_breadcrumb($page_id)
    {
        $breadcrumb = [];

        while ($page_id) {
            $page = $this->Model->readOne('pages', ['id_page' => $page_id]);
            if (!$page) break;

            $url = ($page['slug'] === 'home')
                ? base_url()
                : base_url($page['slug']);

            array_unshift($breadcrumb, [
                'titre' => $page['titre_page'],
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
            'canonical_url'    => base_url($page['slug'] ?? '')
        ];
    }

    /**
     * Changer de langue - redirige simplement vers l'accueil (plus utilisé)
     */
    public function switch_lang($lang = 'fr')
    {
        // Rediriger vers la page d'accueil
        redirect(base_url());
    }
}