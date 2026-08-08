<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Blog Controller - Frontend
 * Gestion des actualités/blog côté utilisateur avec compteur de vues
 */
class Blog extends MX_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('media/Model_media', 'Model');
        $this->load->helper('url');
        $this->load->helper('text');
        $this->load->library('session');
    }

    /**
     * Page liste des articles (blog)
     */
    public function index()
    {
        // Pagination configuration
        $this->load->library('pagination');
        
        $config['base_url'] = base_url('blog/index');
        $config['total_rows'] = $this->Model->count('actualites_blog', ['deleted_at' => null]);
        $config['per_page'] = 9;
        $config['uri_segment'] = 3;
        
        // Configuration Bootstrap 5
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        
        // Récupérer les articles publiés (non supprimés)
        $articles = $this->Model->read(
            'actualites_blog', 
            ['deleted_at' => null], 
            'date_publication', 
            'DESC',
            $config['per_page'],
            $page
        );
         $data['hero_section'] = $this->get_hero_section();
        
// Formater les données pour la vue
    $data['articles'] = $this->formatArticles($articles);
    $data['pagination'] = $this->pagination->create_links();
    $data['total_articles'] = $config['total_rows'];
    
    // Articles populaires (sidebar)
    $data['popular_articles'] = $this->getPopularArticles();
    
    // Catégories pour filtre
    $data['categories'] = $this->getCategories();
    
    // Réseaux sociaux (sidebar)
    $data['social_links'] = $this->getActiveSocialLinks();
        
        $this->load->view('Blog_View', $data);
    }

    /**
     * Afficher un article détaillé
     */
public function article($slug = null)
{
    if (empty($slug)) {
        redirect('blog');
        return;
    }
    
    $article = $this->Model->readOne('actualites_blog', [
        'slug' => $slug,
        'deleted_at' => null
    ]);
    
    if (!$article) {
        show_404();
        return;
    }
    
    // Incrémentation atomique côté MySQL (meilleure méthode)
    $this->db->set('vues', 'vues + 1', FALSE);
    $this->db->where('id_actualite', $article['id_actualite']);
    $this->db->update('actualites_blog');
    
    // Mettre à jour le tableau pour l'affichage
    $article['vues'] = ($article['vues'] ?? 0) + 1;
    
    $data['article'] = $this->formatArticle($article);
    $data['related_articles'] = $this->getRelatedArticles($article['id_actualite'], $article['categorie']);
    $data['popular_articles'] = $this->getPopularArticles(5, $article['id_actualite']);
    $data['navigation'] = $this->getArticleNavigation($article['date_publication']);
    $data['social_links'] = $this->db
        ->where('is_active', 1)
        ->order_by('display_order', 'ASC')
        ->get('social_links')
        ->result_array();
    
    $this->load->view('Blog_Detail_View', $data);
}

    /**
     * Filtrer par catégorie
     */
    public function categorie($categorie = null)
    {
        if (empty($categorie)) {
            redirect('blog');
            return;
        }
        
        $this->load->library('pagination');
        
        $config['base_url'] = base_url('blog/categorie/' . $categorie);
        $config['total_rows'] = $this->Model->count('actualites_blog', [
            'categorie' => urldecode($categorie),
            'deleted_at' => null
        ]);
        $config['per_page'] = 9;
        $config['uri_segment'] = 4;
        
        // Configuration pagination Bootstrap
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        
        $articles = $this->Model->read(
            'actualites_blog',
            [
                'categorie' => urldecode($categorie),
                'deleted_at' => null
            ],
            'date_publication',
            'DESC',
            $config['per_page'],
            $page
        );
        
        $data['articles'] = $this->formatArticles($articles);
        $data['pagination'] = $this->pagination->create_links();
        $data['categorie_active'] = urldecode($categorie);
        $data['total_articles'] = $config['total_rows'];
        $data['popular_articles'] = $this->getPopularArticles();
        $data['categories'] = $this->getCategories();
        $data['social_links'] = $this->getActiveSocialLinks();
        
        $this->load->view('Blog_View', $data);
    }

    /**
     * Recherche d'articles
     */
    public function recherche()
    {
        $q = $this->input->get('q');
        
        if (empty($q)) {
            redirect('blog');
            return;
        }
        
        // Recherche dans titre, résumé et contenu
        $this->db->like('titre', $q);
        $this->db->or_like('resume', $q);
        $this->db->or_like('contenu', $q);
        $this->db->where('deleted_at', null);
        $query = $this->db->get('actualites_blog');
        
        $articles = $query->result_array();
        
        $data['articles'] = $this->formatArticles($articles);
        $data['search_query'] = $q;
        $data['total_results'] = count($articles);
        $data['popular_articles'] = $this->getPopularArticles();
        $data['categories'] = $this->getCategories();
        $data['social_links'] = $this->getActiveSocialLinks();
        
        $this->load->view('Blog_View', $data);
    }

  

    /**
     * Formater les articles pour la vue
     */
    private function formatArticles($articles)
    {
        $formatted = [];
        
        foreach ($articles as $article) {
            $formatted[] = $this->formatArticle($article);
        }
        
        return $formatted;
    }

    /**
     * Formater un article individuel
     */
    private function formatArticle($article)
    {
        // Gestion de l'image
        $image_url = base_url('assets/backend/images/defaut-logo.jpeg');
        if (!empty($article['image_principale'])) {
            $image_url = (strpos($article['image_principale'], 'http') === 0) 
                ? $article['image_principale'] 
                : base_url($article['image_principale']);
        }
        
        // Tags
        $tags = [];
        if (!empty($article['tags'])) {
            $decoded = json_decode($article['tags'], true);
            if (is_array($decoded)) {
                $tags = $decoded;
            }
        }
        
        // Calculer temps de lecture (estimation: 200 mots/minute)
        $word_count = str_word_count(strip_tags($article['contenu'] ?? ''));
        $read_time = max(1, ceil($word_count / 200));
        
        return [
            'id' => $article['id_actualite'],
            'title' => $article['titre'],
            'slug' => $article['slug'],
            'resume' => $article['resume'],
            'content' => $article['contenu'],
            'image' => $image_url,
            'category' => $article['categorie'] ?? 'Actualités',
            'tags' => $tags,
            'author' => $article['auteur'] ?? 'Admin',
            'date' => $article['date_publication'],
            'date_formatted' => $this->formatDate($article['date_publication']),
            'views' => $article['vues'] ?? 0,
            'read_time' => $read_time,
            'featured' => !empty($article['est_en_avant']),
            'for_subscribers' => !empty($article['for_subscriber']),
            'url' => base_url('actualite/' . $article['slug'])
        ];
    }

    /**
     * Articles populaires (plus de vues)
     */
    private function getPopularArticles($limit = 5, $exclude_id = null)
    {
        $this->db->where('deleted_at', null);
        $this->db->order_by('vues', 'DESC');
        $this->db->limit($limit);
        
        if ($exclude_id) {
            $this->db->where('id_actualite !=', $exclude_id);
        }
        
        $query = $this->db->get('actualites_blog');
        return $this->formatArticles($query->result_array());
    }

    /**
     * Articles similaires (même catégorie)
     */
    private function getRelatedArticles($current_id, $categorie, $limit = 3)
    {
        if (empty($categorie)) {
            return [];
        }
        
        $this->db->where('categorie', $categorie);
        $this->db->where('id_actualite !=', $current_id);
        $this->db->where('deleted_at', null);
        $this->db->order_by('date_publication', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get('actualites_blog');
        return $this->formatArticles($query->result_array());
    }

    /**
     * Navigation précédent/suivant
     */
    private function getArticleNavigation($current_date)
    {
        // Article précédent (plus ancien)
        $this->db->where('date_publication <', $current_date);
        $this->db->where('deleted_at', null);
        $this->db->order_by('date_publication', 'DESC');
        $prev = $this->db->get('actualites_blog', 1)->row_array();
        
        // Article suivant (plus récent)
        $this->db->where('date_publication >', $current_date);
        $this->db->where('deleted_at', null);
        $this->db->order_by('date_publication', 'ASC');
        $next = $this->db->get('actualites_blog', 1)->row_array();
        
        return [
            'prev' => $prev ? $this->formatArticle($prev) : null,
            'next' => $next ? $this->formatArticle($next) : null
        ];
    }

    /**
     * Récupérer les catégories uniques
     */
    private function getCategories()
    {
        $this->db->select('categorie, COUNT(*) as count');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $this->db->where('deleted_at', null);
        $this->db->group_by('categorie');
        $this->db->order_by('count', 'DESC');
        
        $query = $this->db->get('actualites_blog');
        return $query->result_array();
    }

    /**
     * Formater la date en français
     */
    private function formatDate($date)
    {
        if (empty($date)) return '-';
        
        $months = [
            'January' => 'janvier',
            'February' => 'février',
            'March' => 'mars',
            'April' => 'avril',
            'May' => 'mai',
            'June' => 'juin',
            'July' => 'juillet',
            'August' => 'août',
            'September' => 'septembre',
            'October' => 'octobre',
            'November' => 'novembre',
            'December' => 'décembre'
        ];
        
        $date_obj = new DateTime($date);
        $month = $months[$date_obj->format('F')] ?? $date_obj->format('F');
        
        return $date_obj->format('d') . ' ' . $month . ' ' . $date_obj->format('Y');
    }


 private function get_hero_section()
    {
        $page = static_pages_one(['slug' => 'blog', 'est_publiee' => 1]);

        if (empty($page)) {
            log_message('debug', 'Page product-categories non trouvée');
            return null;
        }

        $hero = static_sections_one([
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
     * Récupérer les réseaux sociaux actifs pour la sidebar
     */
    private function getActiveSocialLinks()
    {
        return $this->db
            ->where('is_active', 1)
            ->order_by('display_order', 'ASC')
            ->get('social_links')
            ->result_array();
    }
    
    
}