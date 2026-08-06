<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pages Controller - Interface Client Dynamique
 * Gère l'affichage des pages avec rendu intelligent des sections
 */
class Home extends Public_Controller {

    private $section_renderers = [];
    private $cache_enabled = TRUE;
    private $cache_duration = 3600; // 1 heure


    function __construct()
    {
        parent::__construct();
        
        // Chargement des modèles et helpers
        $this->load->model('Section_model');
        $this->load->helper(['text', 'url', 'html', 'date']);
        $this->load->library(['cache', 'user_agent']);
        
        // Initialisation des renderers de sections
        $this->initializeRenderers();
        
        // Configuration du cache
        if ($this->cache_enabled) {
            $this->load->driver('cache', ['adapter' => 'file']);
        }
    }

    /**
     * Page d'accueil
     */
    public function index()
    {
        // Récupérer la page d'accueil (par défaut: slug = 'accueil' ou première page publiée)
        $homepage = static_pages_one( [
            'slug' => 'accueil',
            'est_publiee' => 1
        ]);

        if (!$homepage) {
            $homepage = static_pages_one( [
                'est_publiee' => 1,
            ], 'menu_ordre', 'ASC');
        }

        if (!$homepage) {
            show_404();
            return;
        }

        $this->afficher($homepage['slug']);
    }

    /**
     * Affichage d'une page par slug
     * Route: /page/{slug}
     */
    public function afficher($slug = NULL)
    {
        if (empty($slug)) {
            redirect(base_url());
            return;
        }

        // Nettoyage du slug
        $slug = urldecode($this->security->xss_clean($slug));
        
        // Cache key
        $cache_key = 'page_' . md5($slug . $this->agent->platform() . $this->agent->mobile());
        
        // Vérifier le cache
        if ($this->cache_enabled && $cached = $this->cache->get($cache_key)) {
            $this->load->view('templates/home', $cached);
            return;
        }

        // Récupérer la page
        $page = static_pages_one( [
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (!$page) {
            show_404();
            return;
        }

        // Récupérer les sections actives de la page
        $sections = $this->Section_model->get_sections_by_page(
            $page['id_page'], 
            ['est_active' => 1]
        );

        // Rendu intelligent des sections
        $rendered_sections = $this->renderSections($sections);

        // Données pour la vue
        $data = [
            'page' => $page,
            'sections' => $rendered_sections,
            'meta' => $this->generateMeta($page),
            'breadcrumbs' => $this->generateBreadcrumbs($page),
            'navigation' => $this->getNavigation(),
            'related_pages' => $this->getRelatedPages($page),
            'csrf' => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ]
        ];

        // Mettre en cache
        if ($this->cache_enabled) {
            $this->cache->save($cache_key, $data, $this->cache_duration);
        }

        // Charger la vue avec template spécifique si défini
        $template = $page['template_specifique'] ?: 'templates/home';
        $this->load->view($template, $data);
    }

    /**
     * Rendu intelligent de toutes les sections
     */
    private function renderSections($sections)
    {
        $rendered = [];
        
        foreach ($sections as $section) {
            $type = $section['type_section'];
            $custom = $section['custom_class'];
            
            // Déterminer le renderer à utiliser
            $renderer = $this->determineRenderer($type, $custom);
            
            // Préparer les données de la section
            $section_data = $this->prepareSectionData($section);
            
            // Rendu avec fallback
            try {
                $html = $this->$renderer($section_data);
            } catch (Exception $e) {
                log_message('error', 'Erreur rendu section ' . $section['id_section'] . ': ' . $e->getMessage());
                $html = $this->renderFallback($section_data);
            }
            
            $rendered[] = [
                'id' => $section['id_section'],
                'type' => $type,
                'custom_class' => $custom,
                'html' => $html,
                'data' => $section_data,
                'wrapper_classes' => $this->generateWrapperClasses($section)
            ];
        }
        
        return $rendered;
    }

    /**
     * Détermine le renderer approprié pour une section
     */
    private function determineRenderer($type, $custom_class)
    {
        // Priorité 1: Custom class spécifique
        if (!empty($custom_class) && isset($this->section_renderers[$custom_class])) {
            return $this->section_renderers[$custom_class];
        }
        
        // Priorité 2: Type de section générique
        if (isset($this->section_renderers[$type])) {
            return $this->section_renderers[$type];
        }
        
        // Fallback: renderer par défaut
        return 'renderGeneric';
    }

    /**
     * Prépare les données d'une section pour le rendu
     */
    private function prepareSectionData($section)
    {
        $data = [
            'id' => $section['id_section'],
            'titre' => $section['titre_section'],
            'sous_titre' => $section['sous_titre'],
            'contenu' => $section['contenu_texte'],
            'image' => $section['image_url'],
            'image_position' => !empty($section['image_droite']) ? 'right' : 'left',
            'bouton' => [
                'texte' => $section['bouton_texte'],
                'lien' => $section['bouton_lien']
            ],
            'ordre' => $section['ordre'],
            'options' => [],
            'source_data' => []
        ];

        // Parser les options JSON
        if (!empty($section['options_json'])) {
            $data['options'] = json_decode($section['options_json'], true) ?: [];
        }

        // Charger les données source si définies
        if (!empty($data['options']['source'])) {
            $data['source_data'] = $this->loadSourceData(
                $data['options']['source'], 
                $data['options'] ?? []
            );
        }

        // Charger les items pour les grilles/listes
        if (!empty($data['options']['items'])) {
            $data['items'] = $data['options']['items'];
        }

        return $data;
    }

    /**
     * Charge les données depuis une table source
     */
    private function loadSourceData($source, $options = [])
    {
        $limit = $options['limit'] ?? 10;
        $order = $options['order'] ?? 'created_at';
        $direction = $options['direction'] ?? 'DESC';
        
        // Whitelist des tables autorisées
        $allowed_tables = [
            'temoignages', 'partenaires', 'equipe', 'etapes_projet',
            'faq', 'ressources_telechargeables', 'chiffres_cles',
            'actualites_blog', 'produits', 'services', 'evenements'
        ];
        
        if (!in_array($source, $allowed_tables)) {
            log_message('error', 'Table source non autorisée: ' . $source);
            return [];
        }

        // Conditions dynamiques
        $conditions = ['est_publie' => 1];
        
        if (!empty($options['categorie'])) {
            $conditions['categorie'] = $options['categorie'];
        }
        
        if (!empty($options['featured'])) {
            $conditions['en_avant'] = 1;
        }

        return $this->Model->read($source, $conditions, $order, $direction, $limit);
    }

    // =====================================================
    // RENDERERS SPÉCIFIQUES POUR CHAQUE TYPE DE SECTION
    // =====================================================

    /**
     * Renderer: Hero / Bannière principale
     */
    private function renderHero($data)
    {
        $bg_style = '';
        if (!empty($data['image'])) {
            $bg_style = 'background-image: url(\'' . base_url($data['image']) . '\'); background-size: cover; background-position: center;';
        }

        $btn_html = '';
        if (!empty($data['bouton']['texte']) && !empty($data['bouton']['lien'])) {
            $btn_html = '<a href="' . base_url($data['bouton']['lien']) . '" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">';
            $btn_html .= '<span>' . htmlspecialchars($data['bouton']['texte']) . '</span>';
            $btn_html .= '<i class="bx bx-right-arrow-alt ms-2"></i></a>';
        }

        return '
        <section class="hero-section min-vh-100 d-flex align-items-center position-relative overflow-hidden" style="' . $bg_style . '">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
            <div class="container position-relative z-index-1">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8" data-aos="fade-up">
                        <h1 class="display-2 fw-bold text-white mb-4 text-shadow">' . 
                            nl2br(htmlspecialchars($data['titre'])) . 
                        '</h1>
                        ' . ($data['sous_titre'] ? '<p class="lead text-white-75 mb-5 fs-4">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '
                        ' . $btn_html . '
                    </div>
                </div>
            </div>
            <div class="scroll-indicator position-absolute bottom-0 start-50 translate-middle-x mb-4">
                <div class="mouse"></div>
            </div>
        </section>';
    }

    /**
     * Renderer: Texte simple
     */
    private function renderTexte($data)
    {
        return '
        <section class="py-5 section-texte">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        ' . ($data['titre'] ? '<h2 class="text-center mb-4">' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                        <div class="content-text">
                            ' . $data['contenu'] . '
                        </div>
                    </div>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Image + Texte
     */
    private function renderImageTexte($data)
    {
        $image_col = $data['image_position'] === 'right' ? 'order-lg-2' : '';
        $text_col = $data['image_position'] === 'right' ? 'order-lg-1' : '';
        
        $img_html = '';
        if (!empty($data['image'])) {
            $img_html = '
            <div class="col-lg-6 ' . $image_col . ' mb-4 mb-lg-0" data-aos="' . ($data['image_position'] === 'right' ? 'fade-left' : 'fade-right') . '">
                <div class="image-wrapper rounded-4 overflow-hidden shadow-lg">
                    <img src="' . base_url($data['image']) . '" alt="' . htmlspecialchars($data['titre']) . '" class="img-fluid w-100 hover-zoom">
                </div>
            </div>';
        }

        $btn_html = '';
        if (!empty($data['bouton']['texte']) && !empty($data['bouton']['lien'])) {
            $btn_html = '<a href="' . base_url($data['bouton']['lien']) . '" class="btn btn-outline-primary mt-3">' . htmlspecialchars($data['bouton']['texte']) . '</a>';
        }

        return '
        <section class="py-5 section-image-texte">
            <div class="container">
                <div class="row align-items-center">
                    ' . $img_html . '
                    <div class="col-lg-6 ' . $text_col . '" data-aos="fade-up">
                        ' . ($data['titre'] ? '<h2 class="mb-3">' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                        ' . ($data['sous_titre'] ? '<p class="lead text-muted mb-4">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '
                        <div class="content">' . $data['contenu'] . '</div>
                        ' . $btn_html . '
                    </div>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Grille de cartes
     */
    private function renderGrille($data)
    {
        $items = $data['items'] ?? $data['source_data'] ?? [];
        $columns = $data['options']['columns'] ?? 3;
        
        $col_class = match((int)$columns) {
            2 => 'col-md-6',
            4 => 'col-md-6 col-lg-3',
            5 => 'col-md-6 col-lg-2 col-md-4',
            6 => 'col-md-4 col-lg-2',
            default => 'col-md-6 col-lg-4'
        };

        $cards_html = '';
        foreach ($items as $index => $item) {
            $cards_html .= $this->renderCard($item, $col_class, $index);
        }

        return '
        <section class="py-5 section-grille bg-light">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2>' . ($data['sous_titre'] ? '<p class="lead text-muted">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '</div>' : '') . '
                <div class="row g-4">
                    ' . $cards_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Rendu d'une carte individuelle
     */
    private function renderCard($item, $col_class, $index)
    {
        $delay = $index * 100;
        
        $image_html = '';
        if (!empty($item['image']) || !empty($item['image_url'])) {
            $img = $item['image'] ?? $item['image_url'];
            $image_html = '<div class="card-img-wrapper"><img src="' . base_url($img) . '" class="card-img-top" alt=""></div>';
        }

        $icon_html = '';
        if (!empty($item['icone'])) {
            $icon_html = '<div class="feature-icon mb-3"><i class="' . htmlspecialchars($item['icone']) . ' fs-2 text-primary"></i></div>';
        }

        return '
        <div class="' . $col_class . '" data-aos="fade-up" data-aos-delay="' . $delay . '">
            <div class="card h-100 border-0 shadow-sm hover-card">
                ' . $image_html . '
                <div class="card-body p-4">
                    ' . $icon_html . '
                    <h5 class="card-title">' . htmlspecialchars($item['titre'] ?? $item['nom'] ?? 'Sans titre') . '</h5>
                    <p class="card-text text-muted">' . character_limiter(strip_tags($item['description'] ?? $item['contenu'] ?? ''), 150) . '</p>
                </div>
            </div>
        </div>';
    }

    /**
     * Renderer: Liste (Timeline, FAQ, etc.)
     */
    private function renderListe($data)
    {
        $type = $data['options']['list_type'] ?? 'default';
        
        return match($type) {
            'timeline' => $this->renderTimeline($data),
            'faq' => $this->renderFaq($data),
            'downloads' => $this->renderDownloads($data),
            default => $this->renderDefaultList($data)
        };
    }

    /**
     * Renderer: Timeline / Frise chronologique
     */
    private function renderTimeline($data)
    {
        $items = $data['source_data'] ?? $data['items'] ?? [];
        
        $timeline_html = '';
        foreach ($items as $index => $item) {
            $side = $index % 2 === 0 ? 'left' : 'right';
            $timeline_html .= '
            <div class="timeline-item ' . $side . '" data-aos="' . ($side === 'left' ? 'fade-right' : 'fade-left') . '">
                <div class="timeline-content">
                    <span class="timeline-date">' . date('M Y', strtotime($item['date'] ?? $item['created_at'])) . '</span>
                    <h4>' . htmlspecialchars($item['titre'] ?? $item['nom_etape']) . '</h4>
                    <p>' . nl2br(htmlspecialchars($item['description'] ?? '')) . '</p>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-timeline">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2></div>' : '') . '
                <div class="timeline">
                    ' . $timeline_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: FAQ Accordéon
     */
    private function renderFaq($data)
    {
        $items = $data['source_data'] ?? [];
        
        $faq_html = '';
        foreach ($items as $index => $item) {
            $faq_html .= '
            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button ' . ($index > 0 ? 'collapsed' : '') . '" type="button" data-bs-toggle="collapse" data-bs-target="#faq' . $index . '">
                        ' . htmlspecialchars($item['question']) . '
                    </button>
                </h2>
                <div id="faq' . $index . '" class="accordion-collapse collapse ' . ($index === 0 ? 'show' : '') . '" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        ' . nl2br(htmlspecialchars($item['reponse'])) . '
                    </div>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-faq bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2>' . ($data['sous_titre'] ? '<p class="text-muted">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '</div>' : '') . '
                        <div class="accordion" id="faqAccordion">
                            ' . $faq_html . '
                        </div>
                    </div>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Témoignages / Carousel
     */
    private function renderTemoignages($data)
    {
        $items = $data['source_data'] ?? [];
        
        $carousel_items = '';
        foreach ($items as $index => $item) {
            $active = $index === 0 ? 'active' : '';
            $carousel_items .= '
            <div class="carousel-item ' . $active . '">
                <div class="testimonial-item text-center p-5">
                    <div class="testimonial-avatar mb-4">
                        <img src="' . ($item['photo'] ? base_url($item['photo']) : base_url('assets/images/avatar-default.jpg')) . '" 
                             class="rounded-circle shadow" width="80" height="80" alt="">
                    </div>
                    <div class="testimonial-content">
                        <i class="bx bxs-quote-alt-left text-primary fs-1 mb-3 opacity-25"></i>
                        <p class="lead fst-italic mb-4">"' . htmlspecialchars($item['temoignage']) . '"</p>
                        <h5 class="mb-1">' . htmlspecialchars($item['nom'] . ' ' . $item['prenom']) . '</h5>
                        <small class="text-muted">' . htmlspecialchars($item['fonction'] ?? '') . ', ' . htmlspecialchars($item['entreprise'] ?? '') . '</small>
                    </div>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-temoignages">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2></div>' : '') . '
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        ' . $carousel_items . '
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Chiffres clés / Statistiques
     */
    private function renderChiffres($data)
    {
        $items = $data['source_data'] ?? $data['items'] ?? [];
        
        $stats_html = '';
        foreach ($items as $index => $item) {
            $chiffre = $item['chiffre'] ?? $item['valeur'] ?? $item['nombre'] ?? 0;
            $suffixe = $item['suffixe'] ?? '';
            
            $stats_html .= '
            <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="' . ($index * 100) . '">
                <div class="stat-item text-center">
                    <div class="stat-number display-4 fw-bold text-primary mb-2">
                        <span class="counter" data-target="' . $chiffre . '">0</span>' . $suffixe . '
                    </div>
                    <div class="stat-label text-uppercase text-muted small">' . htmlspecialchars($item['label'] ?? $item['titre'] ?? '') . '</div>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-chiffres bg-primary text-white">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2 class="text-white">' . htmlspecialchars($data['titre']) . '</h2></div>' : '') . '
                <div class="row">
                    ' . $stats_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Partenaires / Logos
     */
    private function renderPartenaires($data)
    {
        $items = $data['source_data'] ?? [];
        
        $logos_html = '';
        foreach ($items as $item) {
            $logo = !empty($item['logo']) ? base_url($item['logo']) : base_url('assets/images/logo-placeholder.png');
            $logos_html .= '
            <div class="col-6 col-md-3 col-lg-2 mb-4">
                <div class="partner-logo p-3 bg-white rounded shadow-sm hover-lift">
                    <img src="' . $logo . '" alt="' . htmlspecialchars($item['nom']) . '" class="img-fluid grayscale hover-color">
                </div>
            </div>';
        }

        return '
        <section class="py-4 section-partenaires bg-light">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-4"><h5 class="text-muted text-uppercase ls-2">' . htmlspecialchars($data['titre']) . '</h5></div>' : '') . '
                <div class="row align-items-center justify-content-center">
                    ' . $logos_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: CTA / Appel à l'action
     */
    private function renderCta($data)
    {
        $bg = !empty($data['image']) ? 'style="background-image: url(\'' . base_url($data['image']) . '\'); background-size: cover;"' : '';
        
        return '
        <section class="py-5 section-cta position-relative" ' . $bg . '>
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75"></div>
            <div class="container position-relative z-index-1">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-3">' . htmlspecialchars($data['titre']) . '</h2>
                        ' . ($data['sous_titre'] ? '<p class="text-white-75 lead mb-4">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '
                        <a href="' . base_url($data['bouton']['lien']) . '" class="btn btn-light btn-lg px-5 rounded-pill">' . htmlspecialchars($data['bouton']['texte']) . '</a>
                    </div>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Formulaire de contact
     */
    private function renderContact($data)
    {
        return '
        <section class="py-5 section-contact">
            <div class="container">
                <div class="row g-5">
                    <div class="col-lg-5" data-aos="fade-right">
                        ' . ($data['titre'] ? '<h2 class="mb-4">' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                        ' . ($data['sous_titre'] ? '<p class="lead text-muted mb-4">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '
                        <div class="contact-info">
                            <div class="d-flex mb-3">
                                <div class="icon-box bg-primary text-white rounded-circle me-3" style="width:50px;height:50px;line-height:50px;text-align:center;">
                                    <i class="bx bx-map"></i>
                                </div>
                                <div>
                                    <h6>Adresse</h6>
                                    <p class="text-muted">123 Rue Example, Ville</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="icon-box bg-primary text-white rounded-circle me-3" style="width:50px;height:50px;line-height:50px;text-align:center;">
                                    <i class="bx bx-phone"></i>
                                </div>
                                <div>
                                    <h6>Téléphone</h6>
                                    <p class="text-muted">+33 1 23 45 67 89</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="icon-box bg-primary text-white rounded-circle me-3" style="width:50px;height:50px;line-height:50px;text-align:center;">
                                    <i class="bx bx-envelope"></i>
                                </div>
                                <div>
                                    <h6>Email</h6>
                                    <p class="text-muted">contact@example.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left">
                        <div class="card border-0 shadow-lg">
                            <div class="card-body p-4 p-md-5">
                                <form action="' . base_url('Contact/submit') . '" method="post" class="needs-validation" novalidate>
                                    <input type="hidden" name="' . $data['csrf']['name'] . '" value="' . $data['csrf']['hash'] . '">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nom</label>
                                            <input type="text" class="form-control form-control-lg" name="nom" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control form-control-lg" name="email" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Sujet</label>
                                            <input type="text" class="form-control form-control-lg" name="sujet" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Message</label>
                                            <textarea class="form-control" name="message" rows="5" required></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">Envoyer le message</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Tableau de données
     */
    private function renderTableau($data)
    {
        // Parser le contenu markdown ou HTML en tableau
        $contenu = $data['contenu'] ?? '';
        
        // Si c'est du markdown, le convertir
        if (strpos($contenu, '|') !== false) {
            $table_html = $this->parseMarkdownTable($contenu);
        } else {
            $table_html = $contenu; // Considérer comme HTML déjà formaté
        }

        return '
        <section class="py-5 section-tableau">
            <div class="container">
                ' . ($data['titre'] ? '<h2 class="mb-4">' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                <div class="table-responsive">
                    ' . $table_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Vidéo
     */
    private function renderVideo($data)
    {
        $video_url = $data['contenu'] ?? '';
        $embed_url = $this->convertToEmbedUrl($video_url);
        
        return '
        <section class="py-5 section-video">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-4"><h2>' . htmlspecialchars($data['titre']) . '</h2></div>' : '') . '
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-lg">
                    <iframe src="' . $embed_url . '" title="Vidéo" allowfullscreen></iframe>
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Produits vitrine
     */
    private function renderProduits($data)
    {
        $items = $data['source_data'] ?? [];
        
        $products_html = '';
        foreach ($items as $index => $item) {
            $image = !empty($item['image_principale']) ? base_url($item['image_principale']) : base_url('assets/images/product-placeholder.jpg');
            $prix = isset($item['prix']) ? number_format($item['prix'], 2, ',', ' ') . ' €' : 'Sur demande';
            
            $products_html .= '
            <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="' . ($index * 100) . '">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <div class="position-relative">
                        <img src="' . $image . '" class="card-img-top" alt="' . htmlspecialchars($item['nom']) . '">
                        <div class="product-overlay">
                            <a href="' . base_url('produits/' . $item['slug']) . '" class="btn btn-light btn-sm">Voir détails</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($item['nom']) . '</h5>
                        <p class="card-text text-muted small">' . character_limiter(strip_tags($item['description_courte'] ?? ''), 100) . '</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="price fw-bold text-primary">' . $prix . '</span>
                            <a href="' . base_url('produits/' . $item['slug']) . '" class="btn btn-outline-primary btn-sm">Détails</a>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-produits">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2>' . ($data['sous_titre'] ? '<p class="lead text-muted">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '</div>' : '') . '
                <div class="row">
                    ' . $products_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Téléchargements
     */
    private function renderDownloads($data)
    {
        $items = $data['source_data'] ?? [];
        
        $files_html = '';
        foreach ($items as $item) {
            $icon = $this->getFileIcon($item['fichier'] ?? '');
            $size = isset($item['taille']) ? ' (' . $this->formatFileSize($item['taille']) . ')' : '';
            
            $files_html .= '
            <div class="col-md-6 mb-3">
                <a href="' . base_url($item['fichier']) . '" class="download-item d-flex align-items-center p-3 bg-white rounded shadow-sm text-decoration-none" target="_blank">
                    <div class="icon-box bg-light rounded p-3 me-3">
                        <i class="' . $icon . ' fs-3 text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-dark">' . htmlspecialchars($item['titre']) . '</h6>
                        <small class="text-muted">' . strtoupper(pathinfo($item['fichier'], PATHINFO_EXTENSION)) . $size . '</small>
                    </div>
                    <i class="bx bx-download fs-4 text-muted"></i>
                </a>
            </div>';
        }

        return '
        <section class="py-5 section-downloads bg-light">
            <div class="container">
                ' . ($data['titre'] ? '<h2 class="mb-4">' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                <div class="row">
                    ' . $files_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer: Équipe
     */
    private function renderEquipe($data)
    {
        $items = $data['source_data'] ?? [];
        
        $team_html = '';
        foreach ($items as $index => $item) {
            $photo = !empty($item['photo']) ? base_url($item['photo']) : base_url('assets/images/avatar-team.jpg');
            
            $social_html = '';
            if (!empty($item['linkedin'])) {
                $social_html .= '<a href="' . htmlspecialchars($item['linkedin']) . '" class="text-muted hover-primary me-2"><i class="bx bxl-linkedin"></i></a>';
            }
            if (!empty($item['twitter'])) {
                $social_html .= '<a href="' . htmlspecialchars($item['twitter']) . '" class="text-muted hover-primary"><i class="bx bxl-twitter"></i></a>';
            }

            $team_html .= '
            <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="' . ($index * 100) . '">
                <div class="card team-card border-0 text-center">
                    <div class="team-avatar mb-3">
                        <img src="' . $photo . '" class="rounded-circle shadow" width="150" height="150" alt="' . htmlspecialchars($item['nom']) . '">
                    </div>
                    <h5 class="mb-1">' . htmlspecialchars($item['prenom'] . ' ' . $item['nom']) . '</h5>
                    <p class="text-primary mb-2">' . htmlspecialchars($item['poste'] ?? $item['fonction'] ?? '') . '</p>
                    <p class="text-muted small mb-3">' . character_limiter(strip_tags($item['bio'] ?? ''), 100) . '</p>
                    <div class="team-social">
                        ' . $social_html . '
                    </div>
                </div>
            </div>';
        }

        return '
        <section class="py-5 section-equipe">
            <div class="container">
                ' . ($data['titre'] ? '<div class="text-center mb-5"><h2>' . htmlspecialchars($data['titre']) . '</h2>' . ($data['sous_titre'] ? '<p class="lead text-muted">' . htmlspecialchars($data['sous_titre']) . '</p>' : '') . '</div>' : '') . '
                <div class="row">
                    ' . $team_html . '
                </div>
            </div>
        </section>';
    }

    /**
     * Renderer générique (fallback)
     */
    private function renderGeneric($data)
    {
        return '
        <section class="py-5 section-generic">
            <div class="container">
                ' . ($data['titre'] ? '<h2>' . htmlspecialchars($data['titre']) . '</h2>' : '') . '
                <div class="content">
                    ' . $data['contenu'] . '
                </div>
            </div>
        </section>';
    }

    /**
     * Fallback ultime en cas d'erreur
     */
    private function renderFallback($data)
    {
        log_message('warning', 'Utilisation du fallback pour section: ' . json_encode($data));
        return '<!-- Section non rendue correctement -->';
    }

    // =====================================================
    // HELPERS ET UTILITAIRES
    // =====================================================

    /**
     * Initialise la mapping des renderers
     */
    private function initializeRenderers()
    {
        $this->section_renderers = [
            'hero' => 'renderHero',
            'texte' => 'renderTexte',
            'image_texte' => 'renderImageTexte',
            'grille' => 'renderGrille',
            'grille_cartes' => 'renderGrille',
            'liste' => 'renderListe',
            'timeline' => 'renderListe',
            'faq_accordeon' => 'renderListe',
            'faq' => 'renderFaq',
            'temoignages' => 'renderTemoignages',
            'partenaires' => 'renderPartenaires',
            'chiffres' => 'renderChiffres',
            'chiffres_cles' => 'renderChiffres',
            'cta' => 'renderCta',
            'contact' => 'renderContact',
            'formulaire_contact' => 'renderContact',
            'tableau' => 'renderTableau',
            'tableau_donnees' => 'renderTableau',
            'video' => 'renderVideo',
            'produits' => 'renderProduits',
            'produits_vitrine' => 'renderProduits',
            'telechargements' => 'renderDownloads',
            'equipe' => 'renderEquipe'
        ];
    }

    /**
     * Génère les classes CSS pour le wrapper de section
     */
    private function generateWrapperClasses($section)
    {
        $classes = ['page-section'];
        $classes[] = 'section-' . ($section['custom_class'] ?: $section['type_section']);
        
        if (!empty($section['options']['full_width'])) {
            $classes[] = 'section-fullwidth';
        }
        
        if (!empty($section['options']['padding'])) {
            $classes[] = 'section-padding-' . $section['options']['padding'];
        }
        
        return implode(' ', $classes);
    }

    /**
     * Génère les métadonnées SEO
     */
    private function generateMeta($page)
    {
        return [
            'title' => $page['meta_title'] ?? $page['titre_page'] . ' | ' . $this->config->item('site_name'),
            'description' => $page['meta_description'] ?? character_limiter(strip_tags($page['contenu'] ?? ''), 160),
            'keywords' => $page['meta_keywords'] ?? '',
            'og_image' => !empty($page['image_partage']) ? base_url($page['image_partage']) : base_url('assets/images/og-default.jpg'),
            'canonical' => base_url('page/' . $page['slug'])
        ];
    }

    /**
     * Génère les breadcrumbs
     */
    private function generateBreadcrumbs($page)
    {
        $crumbs = [['label' => 'Accueil', 'url' => base_url()]];
        
        if (!empty($page['menu_parent_id'])) {
            $parent = static_pages_one( ['id_page' => $page['menu_parent_id']]);
            if ($parent) {
                $crumbs[] = ['label' => $parent['titre_page'], 'url' => base_url('page/' . $parent['slug'])];
            }
        }
        
        $crumbs[] = ['label' => $page['titre_page'], 'url' => null];
        
        return $crumbs;
    }

    /**
     * Récupère la navigation principale
     */
    private function getNavigation()
    {
        return static_pages_where( [
            'est_publiee' => 1,
            'deleted_at' => NULL,
            'menu_parent_id' => NULL
        ], 'menu_ordre', 'ASC');
    }

    /**
     * Récupère les pages liées
     */
    private function getRelatedPages($current_page)
    {
        return static_pages_where( [
            'est_publiee' => 1,
            'deleted_at' => NULL,
            'id_page !=' => $current_page['id_page']
        ], 'RAND()', '', 4);
    }

    /**
     * Convertit une URL vidéo en URL embed
     */
    private function convertToEmbedUrl($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0&modestbranding=1';
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }
        
        return $url;
    }

    /**
     * Parse un tableau markdown en HTML
     */
    private function parseMarkdownTable($markdown)
    {
        $lines = explode("\n", trim($markdown));
        $html = '<table class="table table-striped table-hover">';
        
        $is_header = true;
        foreach ($lines as $line) {
            if (strpos($line, '|') === false) continue;
            
            $cells = array_map('trim', explode('|', trim($line, '|')));
            
            if ($is_header) {
                $html .= '<thead><tr>';
                foreach ($cells as $cell) {
                    $html .= '<th>' . htmlspecialchars($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                $is_header = false;
            } elseif (preg_match('/^[\|\-\:\s]+$/', $line)) {
                // Ligne de séparation markdown, ignorer
                continue;
            } else {
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Retourne l'icône appropriée selon l'extension de fichier
     */
    private function getFileIcon($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match($ext) {
            'pdf' => 'bx bxs-file-pdf',
            'doc', 'docx' => 'bx bxs-file-doc',
            'xls', 'xlsx' => 'bx bxs-file-xls',
            'ppt', 'pptx' => 'bx bxs-file-ppt',
            'zip', 'rar' => 'bx bxs-file-archive',
            'jpg', 'jpeg', 'png', 'gif' => 'bx bxs-file-image',
            default => 'bx bxs-file'
        };
    }

    /**
     * Formate la taille de fichier
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Vider le cache des pages
     */
    public function clear_cache()
    {
        if ($this->session->userdata('logged_in') !== TRUE) {
            show_404();
        }
        
        if ($this->cache_enabled) {
            $this->cache->clean();
            $this->session->set_flashdata('success', 'Cache vidé avec succès.');
        }
        
        redirect($_SERVER['HTTP_REFERER'] ?? base_url('Dashboard'));
    }
}