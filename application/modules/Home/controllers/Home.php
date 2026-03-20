<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
        
    }

    /**
     * Page d'accueil dynamique avec sections
     */
    public function index()
    {    
        $this->Model->log_visit();

        // Récupérer la page Home (id=1)
        $data['page'] = $this->Model->readOne('pages', ['id_page' => 1, 'est_publiee' => 1]);
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        
        if (!$data['page']) {
            show_404();
        }



        // Données spécifiques à la home
        $data['slides'] = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
        $data['chiffres'] = $this->Model->read('chiffres_cles', ['id_page_associee' => 1], 'ordre', 'ASC');
        $data['appels_action'] = $this->Model->read('appels_action', NULL, 'ordre','ASC'
                );
        
        // Données communes
        $data = array_merge($data, $this->_get_page_data(1));
        
        // SEO et métadonnées
        $data = array_merge($data, $this->_prepare_seo_data($data['page']));


        $data['meta_title'] = $data['page']['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $data['page']['meta_description'] ?? $this->Model->get_setting('site_description');

        // Chargement de la vue
        $this->load->view('Home_View', $data);
        
    }

    /**
     * Afficher une page dynamique par slug avec gestion intelligente des templates
     */
    public function view($slug = null)
    {
        if (!$slug) {
            redirect('/');
        }

        // Nettoyer le slug
        $slug = $this->security->xss_clean($slug);

        // Récupérer la page par slug
        $data['page'] = $this->Model->readOne('pages', ['slug' => $slug, 'est_publiee' => 1]);

        if (!$data['page']) {
            show_404();
        }

        // Récupérer les données contextuelles selon le type de page
        $data = array_merge($data, $this->_get_page_data($data['page']['id_page']));
        
        // Charger les données spécifiques selon le template ou le type
        $data = array_merge($data, $this->_load_contextual_data($data['page']));

        // SEO et métadonnées
        $data = array_merge($data, $this->_prepare_seo_data($data['page']));

        // Déterminer la vue à charger
        $view_name = $this->_determine_view($data['page']);
        
        $this->load->view($view_name, $data);
    }

    /**
     * Récupérer les données communes à toutes les pages
     */
    private function _get_page_data($page_id)
    {
        $data = [];
        
        // Sections de contenu
        $data['sections'] = $this->Model->read('sections_contenu', 
            ['id_page' => $page_id], 
            'ordre', 
            'ASC'
        );

        // Pages enfants
        $data['children'] = $this->Model->read('pages', 
            ['menu_parent_id' => $page_id, 'est_publiee' => 1], 
            'menu_ordre', 
            'ASC'
        );

        // Frères et sœurs (pour navigation contextuelle)
        $parent_id = $this->Model->readOne('pages', ['id_page' => $page_id])['menu_parent_id'] ?? null;
        if ($parent_id) {
            $data['siblings'] = $this->Model->read('pages', 
                ['menu_parent_id' => $parent_id, 'est_publiee' => 1, 'id_page !=' => $page_id], 
                'menu_ordre', 
                'ASC'
            );
        }

        // Breadcrumb
        $data['breadcrumb'] = $this->_build_breadcrumb($page_id);

        return $data;
    }

    /**
     * Charger les données contextuelles selon le type/template de page
     */
    private function _load_contextual_data($page)
    {
        $data = [];
        $template = $page['template_specifique'] ?? 'default';
        
        switch ($template) {
            case 'blog':
            case 'actualites':
                $data['articles'] = $this->Model->read('actualites_blog', 
                    ['est_publie' => 1], 
                    'date_publication', 
                    'DESC',
                    6 // limit
                );
                break;

            case 'produits':
            case 'catalogue':
                $data['categories'] = $this->Model->read('categories_produits', 
                    ['active' => 1], 
                    'ordre', 
                    'ASC'
                );
                $data['produits'] = $this->Model->read('produits', 
                    ['est_actif' => 1], 
                    'ordre_affichage', 
                    'ASC'
                );
                break;

            case 'equipe':
                $data['membres'] = $this->Model->read('equipe', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'contact':
                $data['pays'] = $this->Model->read('pays', null, 'pays', 'ASC');
                $data['coordonnees'] = $this->Model->get_setting('contact_coordonnees');
                break;

            case 'partenaires':
                $data['partenaires'] = $this->Model->read('partenaires', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'temoignages':
                $data['temoignages'] = $this->Model->read('temoignages', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;




            case 'faq':
                $data['faq_categories'] = $this->Model->read('faq_categories', ['active' => 1]);
                $data['faq'] = $this->Model->read('faq', ['est_publie' => 1], 'ordre', 'ASC');
                break;

            case 'galerie':
                $data['medias'] = $this->Model->read('galerie_medias', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;

            case 'services':
                $data['services'] = $this->Model->read('services', 
                    ['est_actif' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'evenements':
                $data['evenements'] = $this->Model->read('evenements', 
                    ['date_evenement >=' => date('Y-m-d')], 
                    'date_evenement', 
                    'ASC'
                );
                break;

            case 'ressources':
                $data['ressources'] = $this->Model->read('ressources_telechargeables', 
                    ['est_publie' => 1], 
                    'date_creation', 
                    'DESC'
                );
                break;

            case 'chiffres_cles':
                $data['chiffres'] = $this->Model->read('chiffres_cles', 
                    ['id_page_associee' => $page['id_page']], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'investissement':
                $data['phases'] = $this->Model->read('investissement_phases', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                $data['risques'] = $this->Model->read('risques_mitigations', 
                    ['est_actif' => 1]
                );
                break;

            case 'etapes_projet':
                $data['etapes'] = $this->Model->read('etapes_projet', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'licences':
                $data['licences'] = $this->Model->read('licences_certifications', 
                    ['est_active' => 1], 
                    'ordre', 
                    'ASC'
                );
                break;

            case 'statistiques':
                $data['stats'] = $this->Model->read('statistiques_reseaux', 
                    ['est_public' => 1], 
                    'date_stat', 
                    'DESC',
                    12
                );
                break;

            default:
                // Pour les pages génériques, vérifier si des données sont liées via sections
                $data['chiffres'] = $this->Model->read('chiffres_cles', 
                    ['id_page_associee' => $page['id_page']], 
                    'ordre', 
                    'ASC'
                );
                break;
        }

        return $data;
    }

    /**
     * Déterminer quelle vue charger selon le template
     */
    private function _determine_view($page)
    {
        $template = $page['template_specifique'] ?? 'default';
        
        // Vérifier si une vue spécifique existe
        $specific_view = 'templates/' . $template;
        if (file_exists(APPPATH . 'views/' . $specific_view . '.php')) {
            return $specific_view;
        }
        
        // Sinon, utiliser la vue générique dynamique
        return 'Home_View';
    }

    /**
     * Préparer les données SEO
     */
    private function _prepare_seo_data($page)
    {
        $site_name = $this->Model->get_setting('agf_nom_complet', 'AGF Phytomed');
        $site_description = $this->Model->get_setting('site_description', 'Pionniers de la phytothérapie africaine');
        
        return [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'meta_title' => (!empty($page['meta_title']) ? $page['meta_title'] : $page['titre_page']) . ' - ' . $site_name,
            'meta_description' => !empty($page['meta_description']) ? $page['meta_description'] : $site_description,
            'meta_keywords' => $page['meta_keywords'] ?? '',
            'og_image' => !empty($page['image_social']) ? base_url($page['image_social']) : base_url('assets/images/og-default.jpg'),
            'canonical_url' => base_url($page['slug'])
        ];
    }

    /**
     * Construire le fil d'Ariane
     */
    private function _build_breadcrumb($page_id)
    {
        $breadcrumb = [];
        
        while ($page_id) {
            $page = $this->Model->readOne('pages', ['id_page' => $page_id]);
            if (!$page) break;
            
            array_unshift($breadcrumb, [
                'titre' => $page['titre_page'],
                'slug' => $page['slug'],
                'url' => $page['slug'] === 'home' ? base_url() : base_url($page['slug'])
            ]);
            
            $page_id = $page['menu_parent_id'];
        }
        
        return $breadcrumb;
    }

    /**
     * API pour charger plus de contenu (AJAX)
     */
    public function load_more()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type');
        $page = (int) $this->input->post('page', 1);
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $data = [];
        
        switch ($type) {
            case 'actualites':
                $data['items'] = $this->Model->read('actualites_blog', 
                    ['est_publie' => 1], 
                    'date_publication', 
                    'DESC',
                    $limit,
                    $offset
                );
                break;
                
            case 'produits':
                $data['items'] = $this->Model->read('produits', 
                    ['est_actif' => 1], 
                    'ordre_affichage', 
                    'ASC',
                    $limit,
                    $offset
                );
                break;
        }

        $this->output->set_content_type('application/json');
        echo json_encode($data);
    }

    /**
     * Soumission de formulaire de contact
     */
    public function submit_contact()
    {
        if ($this->input->method() !== 'post') {
            redirect('/');
        }

        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('nom', 'Nom', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('message', 'Message', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($this->input->server('HTTP_REFERER'));
        }

        // Traitement du formulaire...
        $contact_data = [
            'nom' => $this->input->post('nom'),
            'email' => $this->input->post('email'),
            'telephone' => $this->input->post('telephone'),
            'sujet' => $this->input->post('sujet'),
            'message' => $this->input->post('message'),
            'date_creation' => date('Y-m-d H:i:s'),
            'est_lu' => 0
        ];

        $this->Model->create('contacts', $contact_data);
        
        $this->session->set_flashdata('success', 'Votre message a été envoyé avec succès.');
        redirect($this->input->server('HTTP_REFERER'));
    }
























    /**
     * Génère un token visiteur unique
     */
    private function generate_visitor_token() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Récupère ou crée un panier
     */
    private function get_or_create_cart($user_id = null, $visitor_token = null) {
        $this->db->where('est_actif', 1);
        
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        } elseif ($visitor_token) {
            $this->db->where('visitor_token', $visitor_token);
        } else {
            return false;
        }

        $cart = $this->db->get('paniers')->row_array();

        if ($cart) {
            return $cart;
        }

        // Créer nouveau panier
        $data = [
            'user_id' => $user_id ?: 0,
            'visitor_token' => $visitor_token,
            'total_ht' => 0.00,
            'total_ttc' => 0.00,
            'nombre_articles' => 0,
            'est_actif' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('paniers', $data);
        $data['id'] = $this->db->insert_id();
        
        return $data;
    }

   

    public function Overview() {
        $this->load->view('Trategic_Overview_View');
    }

    public function Contact() {
        $this->load->view('Contact_View');
    }

    public function Command() {
        $this->load->view('commande_View');
    }

    public function faq() {
        $data['hero_section'] = $this->get_hero_section();
        $data['faq'] = $this->Model->read('faq', 
                    ['est_publiee' => 1], 
                    'ordre', 
                    'ASC'
                );
        $this->load->view('faq_view',$data);
    }



 private function get_hero_section()
    {
        $page = $this->Model->readOne('pages', ['slug' => 'faq', 'est_publiee' => 1]);

        if (empty($page)) {
            log_message('debug', 'Page product-categories non trouvée');
            return null;
        }

        $hero = $this->Model->readOne('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1
        ]);

        if (empty($hero)) {
            log_message('debug', 'Section hero non trouvée pour la page ' . $page['id_page']);
            return null;
        }

        if (!empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        return $hero;
    }
    
    
    /**
     * Sauvegarde un nouvel abonné (email ou téléphone)
     */
    public function Abonner() {
        // Vérifier si c'est une requête AJAX
        $is_ajax = $this->input->is_ajax_request();
        
        // Déterminer le type d'inscription
        $type = $this->input->post('email') ? 'email' : 'phone';
        
        if ($type === 'email') {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[newsletter.email]', [
                'required' => 'L\'adresse email est requise',
                'valid_email' => 'Veuillez entrer une adresse email valide',
                'is_unique' => 'Cette adresse email est déjà inscrite'
            ]);
            
            if ($this->form_validation->run() === FALSE) {
                $error = validation_errors();
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(['success' => false, 'message' => strip_tags($error)]));
                    return;
                }
                $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert">' . $error . '</div>');
                redirect(base_url('Home'));
                return;
            }
            
            $data = [
                'type' => 'email',
                'email' => strtolower(trim($this->input->post('email'))),
                'telephone' => NULL,
                'indicatif' => NULL,
                'pays_code' => NULL,
                'est_actif' => 1
            ];
            
        } else {
            // Validation téléphone
            $this->form_validation->set_rules('pays_code', 'Pays', 'required', [
                'required' => 'Veuillez sélectionner un pays'
            ]);
            $this->form_validation->set_rules('telephone', 'Téléphone', 'required|min_length[8]|is_unique[newsletter.telephone]', [
                'required' => 'Le numéro de téléphone est requis',
                'min_length' => 'Le numéro doit contenir au moins 8 chiffres',
                'is_unique' => 'Ce numéro de téléphone est déjà inscrit'
            ]);
            
            if ($this->form_validation->run() === FALSE) {
                $error = validation_errors();
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(['success' => false, 'message' => strip_tags($error)]));
                    return;
                }
                $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert">' . $error . '</div>');
                redirect(base_url('Home'));
                return;
            }
            
            // Nettoyer le numéro (garder uniquement les chiffres)
            $telephone = preg_replace('/[^0-9]/', '', $this->input->post('telephone'));
            $indicatif = $this->input->post('indicatif_complet');
            
            $data = [
                'type' => 'phone',
                'email' => NULL,
                'telephone' => $telephone,
                'indicatif' => $indicatif,
                'pays_code' => strtoupper($this->input->post('pays_code')),
                'est_actif' => 1
            ];
        }
        
        // Tentative d'insertion
        try {
            $insert_id = $this->Model->create('newsletter', $data);
            
            if ($insert_id) {
                $message = ($type === 'email') 
                    ? 'Votre email a été ajouté avec succès !' 
                    : 'Votre numéro de téléphone a été ajouté avec succès !';
                
                // Log de l'activité
                log_message('info', 'Newsletter subscription: ID ' . $insert_id . ', Type: ' . $type);
                
                if ($is_ajax) {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode([
                                     'success' => true, 
                                     'message' => $message,
                                     'id' => $insert_id
                                 ]));
                    return;
                }
                
                $this->session->set_flashdata('success', '<div class="alert alert-success fade show mt-1 message" role="alert"><i class="bi bi-check-circle-fill me-2"></i>' . $message . '</div>');
            } else {
                throw new Exception('Erreur lors de l\'insertion');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Newsletter subscription failed: ' . $e->getMessage());
            
            $error_msg = 'Une erreur est survenue. Veuillez réessayer.';
            
            if ($is_ajax) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(['success' => false, 'message' => $error_msg]));
                return;
            }
            
            $this->session->set_flashdata('error', '<div class="alert alert-danger fade show mt-1 message" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Oups!</strong> ' . $error_msg . '</div>');
        }
        
        if (!$is_ajax) {
            redirect(base_url('Home'));
        }
    }
    
    /**
     * Désinscription de la newsletter
     */
    public function unsubscribe($token = null) {
        if (!$token) {
            show_404();
            return;
        }
        
        // Décoder le token (email ou téléphone encodé en base64)
        $contact = base64_decode($token);
        
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $this->Model->update('newsletter', 
                ['email' => $contact], 
                ['est_actif' => 0, 'date_desinscription' => date('Y-m-d H:i:s')]
            );
        } else {
            $this->Model->update('newsletter', 
                ['telephone' => $contact], 
                ['est_actif' => 0, 'date_desinscription' => date('Y-m-d H:i:s')]
            );
        }
        
        $this->session->set_flashdata('success', 'Vous avez été désinscrit avec succès.');
        redirect(base_url('Home'));
    }
    
    /**
     * Liste des inscriptions (pour admin)
     */
    public function liste() {
        // Vérifier les permissions admin
        if (!$this->session->userdata('role_id') || $this->session->userdata('role_id') > 2) {
            show_error('Accès non autorisé', 403);
            return;
        }
        
        $data['subscribers'] = $this->Model->getAll('newsletter', [], 'date_inscription DESC');
        $data['total_email'] = $this->Model->count('newsletter', ['type' => 'email', 'est_actif' => 1]);
        $data['total_phone'] = $this->Model->count('newsletter', ['type' => 'phone', 'est_actif' => 1]);
        
        $this->load->view('admin/newsletter/liste', $data);
    }

}
