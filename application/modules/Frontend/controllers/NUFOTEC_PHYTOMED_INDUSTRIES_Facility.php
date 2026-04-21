<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NUFOTEC_PHYTOMED_INDUSTRIES_Facility extends Public_Controller {

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
        $slug = 'nufotec-phytomed-industries-facility';

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

            // Nettoyage des colonnes de traduction
            unset($sec["titre_section_{$lang}"], $sec["sous_titre_{$lang}"], $sec["contenu_texte_{$lang}"], $sec["bouton_texte_{$lang}"]);
        }

        // 5. Chargement des données additionnelles selon les types de section
        $extra_data = [];
        foreach ($sections as $section) {
            $section_data = $this->load_section_data($section['type_section'], $section['options'], $lang);
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
     * @param string $type Type de section
     * @param array $options Options JSON de la section
     * @param string $lang Code langue
     * @return array Données additionnelles
     */
    private function load_section_data($type, $options = [], $lang = null) {
        $data = [];
        $limit = isset($options['limit']) ? (int)$options['limit'] : null;

        if ($lang === null) {
            $lang = $this->current_lang;
        }

        switch ($type) {
            case 'team':
            case 'equipe':
                $members = $this->Model->read('equipe', ['est_active' => 1], 'ordre', 'ASC');
                foreach ($members as &$member) {
                    $member['nom_complet'] = $member["nom_complet_{$lang}"] ?? $member['nom_complet_fr'] ?? $member['nom_complet'] ?? '';
                    $member['poste']       = $member["poste_{$lang}"] ?? $member['poste_fr'] ?? $member['poste'] ?? '';
                    $member['bio']         = $member["bio_{$lang}"] ?? $member['bio_fr'] ?? $member['bio'] ?? '';
                }
                $data['members'] = $members;
                break;

            case 'partenaires':
            case 'partners_logos':
                $partners = $this->Model->read('partenaires', ['est_actif' => 1], 'ordre', 'ASC');
                foreach ($partners as &$partner) {
                    $partner['nom']        = $partner["nom_{$lang}"] ?? $partner['nom_fr'] ?? $partner['nom'] ?? '';
                    $partner['description'] = $partner["description_{$lang}"] ?? $partner['description_fr'] ?? $partner['description'] ?? '';
                }
                $data['partners'] = $partners;
                break;

            case 'produits':
            case 'product_grid':
                $this->db->select('p.*, c.nom_categorie as categorie_nom');
                $this->db->from('produits p');
                $this->db->join('categories c', 'p.id_categorie = c.id_categorie', 'left');
                $this->db->where('p.est_publie', 1);
                $this->db->order_by('p.ordre', 'ASC');
                if ($limit) $this->db->limit($limit);
                $products = $this->db->get()->result_array();
                foreach ($products as &$product) {
                    $product['nom_produit']   = $product["nom_produit_{$lang}"] ?? $product['nom_produit_fr'] ?? $product['nom_produit'] ?? '';
                    $product['description']   = $product["description_{$lang}"] ?? $product['description_fr'] ?? $product['description'] ?? '';
                    $product['categorie_nom'] = $product["categorie_nom_{$lang}"] ?? $product['categorie_nom_fr'] ?? $product['categorie_nom'] ?? '';
                }
                $data['products'] = $products;
                break;

            case 'actualites_blog':
            case 'blog_posts':
                $posts = $this->Model->read('actualites_blog', ['est_publie' => 1], 'date_publication', 'DESC', $limit ?: 3);
                foreach ($posts as &$post) {
                    $post['titre']         = $post["titre_{$lang}"] ?? $post['titre_fr'] ?? $post['titre'] ?? '';
                    $post['contenu_court'] = $post["contenu_court_{$lang}"] ?? $post['contenu_court_fr'] ?? $post['contenu_court'] ?? '';
                    $post['contenu']       = $post["contenu_{$lang}"] ?? $post['contenu_fr'] ?? $post['contenu'] ?? '';
                }
                $data['posts'] = $posts;
                break;

            case 'chiffres':
            case 'stats_counter':
                $stats = $this->Model->read('chiffres_cles', ['est_actif' => 1], 'ordre', 'ASC');
                foreach ($stats as &$stat) {
                    $stat['titre']      = $stat["titre_{$lang}"] ?? $stat['titre_fr'] ?? $stat['titre'] ?? '';
                    $stat['soustitre']  = $stat["soustitre_{$lang}"] ?? $stat['soustitre_fr'] ?? $stat['soustitre'] ?? '';
                }
                $data['statistics'] = $stats;
                break;

            case 'faq':
                $faqs = $this->Model->read('faq', ['est_active' => 1], 'ordre', 'ASC');
                foreach ($faqs as &$faq) {
                    $faq['question'] = $faq["question_{$lang}"] ?? $faq['question_fr'] ?? $faq['question'] ?? '';
                    $faq['reponse']  = $faq["reponse_{$lang}"] ?? $faq['reponse_fr'] ?? $faq['reponse'] ?? '';
                }
                $data['faqs'] = $faqs;
                break;

            case 'galerie_medias':
            case 'gallery':
                $medias = $this->Model->read('galerie_medias', ['est_active' => 1], 'ordre', 'ASC');
                foreach ($medias as &$media) {
                    $media['titre']       = $media["titre_{$lang}"] ?? $media['titre_fr'] ?? $media['titre'] ?? '';
                    $media['description'] = $media["description_{$lang}"] ?? $media['description_fr'] ?? $media['description'] ?? '';
                }
                $data['medias'] = $medias;
                break;

            case 'investment_cards':
                $phases = $this->Model->read('investissement_phases', ['est_active' => 1], 'ordre', 'ASC');
                foreach ($phases as &$phase) {
                    $phase['titre']       = $phase["titre_{$lang}"] ?? $phase['titre_fr'] ?? $phase['titre'] ?? '';
                    $phase['description'] = $phase["description_{$lang}"] ?? $phase['description_fr'] ?? $phase['description'] ?? '';
                }
                $data['phases'] = $phases;
                break;

            case 'etapes_projet':
            case 'workflow':
            case 'timeline':
                $steps = $this->Model->read('etapes_projet', null, 'date_debut', 'ASC');
                foreach ($steps as &$step) {
                    $step['nom_etape']   = $step["nom_etape_{$lang}"] ?? $step['nom_etape_fr'] ?? $step['nom_etape'] ?? '';
                    $step['description'] = $step["description_{$lang}"] ?? $step['description_fr'] ?? $step['description'] ?? '';
                }
                $data['steps'] = $steps;
                break;

            case 'services':
                $services = $this->Model->read('services', ['est_actif' => 1], 'ordre', 'ASC');
                foreach ($services as &$service) {
                    $service['nom_service'] = $service["nom_service_{$lang}"] ?? $service['nom_service_fr'] ?? $service['nom_service'] ?? '';
                    $service['description'] = $service["description_{$lang}"] ?? $service['description_fr'] ?? $service['description'] ?? '';
                }
                $data['services'] = $services;
                break;

            case 'capacite_production':
            case 'production_capacity':
                $capacities = $this->Model->read('capacite_production', ['est_actif' => 1], 'ordre', 'ASC');
                foreach ($capacities as &$cap) {
                    $cap['nom']          = $cap["nom_{$lang}"] ?? $cap['nom_fr'] ?? $cap['nom'] ?? '';
                    $cap['description']  = $cap["description_{$lang}"] ?? $cap['description_fr'] ?? $cap['description'] ?? '';
                }
                $data['production_capacities'] = $capacities;
                break;

            case 'equipements':
            case 'equipment':
                $equipment = $this->Model->read('equipements_fabrication', ['est_actif' => 1], 'ordre', 'ASC');
                foreach ($equipment as &$eq) {
                    $eq['nom']          = $eq["nom_{$lang}"] ?? $eq['nom_fr'] ?? $eq['nom'] ?? '';
                    $eq['description']  = $eq["description_{$lang}"] ?? $eq['description_fr'] ?? $eq['description'] ?? '';
                    $eq['specifications'] = $eq["specifications_{$lang}"] ?? $eq['specifications_fr'] ?? $eq['specifications'] ?? '';
                }
                $data['manufacturing_equipment'] = $equipment;
                break;

            case 'certifications':
                $certs = $this->Model->read('certifications_usine', ['est_actif' => 1], 'date_obtention', 'DESC');
                foreach ($certs as &$cert) {
                    $cert['nom']         = $cert["nom_{$lang}"] ?? $cert['nom_fr'] ?? $cert['nom'] ?? '';
                    $cert['description'] = $cert["description_{$lang}"] ?? $cert['description_fr'] ?? $cert['description'] ?? '';
                }
                $data['facility_certifications'] = $certs;
                break;

            case 'tableau':
            case 'html':
            case 'texte':
            case 'image_texte':
            case 'liste':
            case 'liste_card':
            case 'grille':
            case 'grille_card':
            case 'timeline':
                // Ces types n'ont pas de données additionnelles à charger
                break;
        }
        return $data;
    }

    /**
     * Prépare les métadonnées SEO
     * @param array $page Données de la page
     * @param string $lang Code langue
     * @return array Métadonnées SEO
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
     * @param string $slug Slug de la page
     */
    public function page($slug = 'nufotec-phytomed-industries-facility') {
        $this->index($slug);
    }
}