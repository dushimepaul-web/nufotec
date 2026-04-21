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

    /**
     * Affiche une page dynamique avec ses sections (multilingue)
     * @param string|null $lang Code langue (fr, en, sw)
     */
    public function index($lang = null) {
        // Langue
        if ($lang === null) {
            $lang = $this->current_lang;
        }

        // Slug par défaut
        $slug = 'about';

        // Récupération de la page
        $page = $this->Model->readOne('pages', [
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (!$page) {
            show_404();
        }

        // Traduction des champs de la page
        $page['titre_page']       = $page["titre_page_{$lang}"] ?? $page['titre_page_fr'] ?? $page['titre_page'];
        $page['contenu_page']     = $page["contenu_page_{$lang}"] ?? $page['contenu_page_fr'] ?? '';
        $page['meta_description'] = $page["meta_description_{$lang}"] ?? $page['meta_description_fr'] ?? '';

        // Récupération des sections actives
        $sections = $this->Model->read('sections_contenu', [
            'id_page'    => $page['id_page'],
            'est_active' => 1
        ], 'ordre', 'ASC');

        // Traduction des sections et décodage JSON
        foreach ($sections as &$sec) {
            $sec['titre_section'] = $sec["titre_section_{$lang}"] ?? $sec['titre_section_fr'] ?? $sec['titre_section'] ?? '';
            $sec['sous_titre']    = $sec["sous_titre_{$lang}"]    ?? $sec['sous_titre_fr']    ?? $sec['sous_titre']    ?? '';
            $sec['contenu_texte'] = $sec["contenu_texte_{$lang}"] ?? $sec['contenu_texte_fr'] ?? $sec['contenu_texte'] ?? '';
            $sec['bouton_texte']  = $sec["bouton_texte_{$lang}"]  ?? $sec['bouton_texte_fr']  ?? $sec['bouton_texte']  ?? '';

            $sec['options'] = !empty($sec['options_json']) ? json_decode($sec['options_json'], true) : [];
            if (!is_array($sec['options'])) $sec['options'] = [];

            unset($sec["titre_section_{$lang}"], $sec["sous_titre_{$lang}"], $sec["contenu_texte_{$lang}"], $sec["bouton_texte_{$lang}"]);
        }

        // Pages enfants pour sous-menu (traduites)
        $children = $this->Model->read('pages', [
            'menu_parent_id' => $page['id_page'],
            'est_publiee' => 1
        ], 'menu_ordre', 'ASC');
        foreach ($children as &$child) {
            $child['titre_page'] = $child["titre_page_{$lang}"] ?? $child['titre_page_fr'] ?? $child['titre_page'];
        }

        // Données SEO
        $seo = $this->_prepare_seo_data($page, $lang);

        // Chargement des données additionnelles pour les sections (équipe, partenaires, etc.)
        $extra_data = $this->load_sections_data($sections, $lang);

        // Assemblage final
        $data = array_merge([
            'page'     => $page,
            'sections' => $sections,
            'children' => $children,
            'lang'     => $lang,
        ], $extra_data, $seo);

        $this->load->view('Corporate_profile', $data);
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
     * Charge les données nécessaires pour tous les types de sections présents
     * Évite les requêtes multiples pour un même type.
     *
     * @param array $sections Liste des sections de la page
     * @param string $lang Langue courante
     * @return array Données additionnelles
     */
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

    // ========================================================================
    // Méthodes de chargement spécifiques (avec prise en compte de la langue)
    // ========================================================================

    private function _load_team($options = [], $lang) {
        $members = $this->Model->read('equipe', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        // Traduction des champs de l'équipe si nécessaire (ex: poste_fr, etc.)
        foreach ($members as &$member) {
            $member['nom_complet'] = $member["nom_complet_{$lang}"] ?? $member['nom_complet_fr'] ?? $member['nom_complet'];
            $member['poste'] = $member["poste_{$lang}"] ?? $member['poste_fr'] ?? $member['poste'];
            $member['bio']   = $member["bio_{$lang}"]   ?? $member['bio_fr']   ?? $member['bio'];
        }
        return ['members' => $members];
    }

    private function _load_partners($options = [], $lang) {
        $partners = $this->Model->read('partenaires', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        foreach ($partners as &$partner) {
            $partner['nom'] = $partner["nom_{$lang}"] ?? $partner['nom_fr'] ?? $partner['nom'];
            $partner['description'] = $partner["description_{$lang}"] ?? $partner['description_fr'] ?? $partner['description'];
        }
        return ['partners' => $partners];
    }

    private function _load_testimonials($options = [], $lang) {
        $testimonials = $this->Model->read('temoignages', ['est_approuve' => 1], 'date_reception', 'DESC');
        foreach ($testimonials as &$test) {
            $test['contenu'] = $test["contenu_{$lang}"] ?? $test['contenu_fr'] ?? $test['contenu'];
            $test['auteur']  = $test["auteur_{$lang}"]  ?? $test['auteur_fr']  ?? $test['auteur'];
        }
        return ['testimonials' => $testimonials];
    }

    private function _load_stats($options = [], $lang) {
        $stats = $this->Model->read('chiffres_cles', ['est_actif' => 1], 'ordre_affichage', 'ASC');
        foreach ($stats as &$stat) {
            $stat['titre'] = $stat["titre_{$lang}"] ?? $stat['titre_fr'] ?? $stat['titre'];
            $stat['description'] = $stat["description_{$lang}"] ?? $stat['description_fr'] ?? $stat['description'];
        }
        return ['statistics' => $stats];
    }

    private function _load_certifications($options = [], $lang) {
        $certs = $this->Model->read('licences_certifications', ['est_actif' => 1], 'date_obtention', 'DESC');
        foreach ($certs as &$cert) {
            $cert['nom'] = $cert["nom_{$lang}"] ?? $cert['nom_fr'] ?? $cert['nom'];
            $cert['description'] = $cert["description_{$lang}"] ?? $cert['description_fr'] ?? $cert['description'];
        }
        return ['certifications' => $certs];
    }

    private function _load_phases($options = [], $lang) {
        $phases = $this->Model->read('investissement_phases', [], 'annee_debut', 'ASC');
        foreach ($phases as &$phase) {
            $phase['titre'] = $phase["titre_{$lang}"] ?? $phase['titre_fr'] ?? $phase['titre'];
            $phase['description'] = $phase["description_{$lang}"] ?? $phase['description_fr'] ?? $phase['description'];
        }
        return ['phases' => $phases];
    }

    private function _load_products($options = [], $lang) {
        $limit = $options['limit'] ?? 6;
        $this->db->select('p.*, c.nom_categorie as categorie_nom');
        $this->db->from('produits p');
        $this->db->join('categories c', 'p.id_categorie = c.id_categorie', 'left');
        $this->db->where('p.est_actif', 1);
        $this->db->order_by('p.ordre_affichage', 'ASC');
        $this->db->limit($limit);
        $products = $this->db->get()->result_array();
        foreach ($products as &$prod) {
            $prod['nom_produit'] = $prod["nom_produit_{$lang}"] ?? $prod['nom_produit_fr'] ?? $prod['nom_produit'];
            $prod['description_courte'] = $prod["description_courte_{$lang}"] ?? $prod['description_courte_fr'] ?? $prod['description_courte'];
            $prod['description_longue'] = $prod["description_longue_{$lang}"] ?? $prod['description_longue_fr'] ?? $prod['description_longue'];
        }
        return ['products' => $products];
    }

    private function _load_posts($options = [], $lang) {
        $limit = $options['limit'] ?? 3;
        $posts = $this->Model->read('actualites_blog', ['est_publiee' => 1], 'date_publication', 'DESC', $limit);
        foreach ($posts as &$post) {
            $post['titre'] = $post["titre_{$lang}"] ?? $post['titre_fr'] ?? $post['titre'];
            $post['contenu'] = $post["contenu_{$lang}"] ?? $post['contenu_fr'] ?? $post['contenu'];
            $post['extrait'] = $post["extrait_{$lang}"] ?? $post['extrait_fr'] ?? $post['extrait'];
        }
        return ['posts' => $posts];
    }

    private function _load_faq($options = [], $lang) {
        $faqs = $this->Model->read('faq', ['est_publiee' => 1], 'ordre', 'ASC');
        foreach ($faqs as &$faq) {
            $faq['question'] = $faq["question_{$lang}"] ?? $faq['question_fr'] ?? $faq['question'];
            $faq['reponse']  = $faq["reponse_{$lang}"]  ?? $faq['reponse_fr']  ?? $faq['reponse'];
        }
        return ['faqs' => $faqs];
    }

    private function _load_gallery($options = [], $lang) {
        $medias = $this->Model->read('galerie_medias', [], 'date_prise', 'DESC');
        foreach ($medias as &$media) {
            $media['titre'] = $media["titre_{$lang}"] ?? $media['titre_fr'] ?? $media['titre'];
            $media['description'] = $media["description_{$lang}"] ?? $media['description_fr'] ?? $media['description'];
        }
        return ['medias' => $medias];
    }

    private function _load_events($options = [], $lang) {
        $events = $this->Model->read('evenements', ['est_public' => 1, 'date_debut >= ' => date('Y-m-d')], 'date_debut', 'ASC');
        foreach ($events as &$event) {
            $event['titre'] = $event["titre_{$lang}"] ?? $event['titre_fr'] ?? $event['titre'];
            $event['description'] = $event["description_{$lang}"] ?? $event['description_fr'] ?? $event['description'];
            $event['lieu'] = $event["lieu_{$lang}"] ?? $event['lieu_fr'] ?? $event['lieu'];
        }
        return ['events' => $events];
    }

    private function _load_resources($options = [], $lang) {
        $resources = $this->Model->read('ressources_telechargeables', ['est_public' => 1], 'date_publication', 'DESC');
        foreach ($resources as &$res) {
            $res['titre'] = $res["titre_{$lang}"] ?? $res['titre_fr'] ?? $res['titre'];
            $res['description'] = $res["description_{$lang}"] ?? $res['description_fr'] ?? $res['description'];
        }
        return ['resources' => $resources];
    }

    private function _load_risks($options = [], $lang) {
        $risks = $this->Model->read('risques_mitigations', [], 'ordre', 'ASC');
        foreach ($risks as &$risk) {
            $risk['risque'] = $risk["risque_{$lang}"] ?? $risk['risque_fr'] ?? $risk['risque'];
            $risk['mitigation'] = $risk["mitigation_{$lang}"] ?? $risk['mitigation_fr'] ?? $risk['mitigation'];
        }
        return ['risks' => $risks];
    }

    private function _load_social_stats($options = [], $lang) {
        $social = $this->Model->read('statistiques_reseaux', [], 'date_mesure', 'DESC');
        // Généralement pas de traduction pour les stats chiffrées, mais on peut garder tel quel
        return ['social_stats' => $social];
    }

    /**
     * Méthode alternative pour charger une page spécifique
     */
    public function page($slug = 'about') {
        $this->index($slug);
    }
}