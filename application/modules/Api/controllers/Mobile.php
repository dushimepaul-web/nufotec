<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Mobile pour React Native
 * Endpoints publics (lecture seule)
 * Version: 1.0
 */
class Mobile extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->helper('file');
        
        // Désactiver CSRF pour l'API
        $this->config->set_item('csrf_protection', FALSE);
        
        // Permettre CORS pour l'app mobile
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Répondre aux requêtes OPTIONS (pre-flight)
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Forcer JSON pour toutes les réponses
        $this->output->set_content_type('application/json');
    }



/**
 * GET /api/mobile/medias
 * Récupérer les médias selon le type
 * - all : audio + video + link uniquement
 * - video : video + link
 * - audio : audio uniquement
 * - pdf : document uniquement
 */
public function medias()
{
    $type = $this->input->get('type'); // all, video, audio, pdf
    $limit = (int)($this->input->get('limit') ?? 50);
    $offset = (int)($this->input->get('offset') ?? 0);
    $category = $this->input->get('category');
    $lang = $this->getCurrentLang();
    
    // Définir les types à récupérer selon le paramètre
    $allowedTypes = [];
    
    switch ($type) {
        case 'video':
            $allowedTypes = ['video', 'link'];
            break;
        case 'audio':
            $allowedTypes = ['audio'];
            break;
        case 'pdf':
            $allowedTypes = ['document'];
            break;
        case 'all':
        default:
            $allowedTypes = ['audio', 'video', 'link'];
            break;
    }
    
    // Construction de la clause IN
    $inClause = "'" . implode("','", $allowedTypes) . "'";
    
    $sql = "
        SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
               g.duree, g.date_media,
               g.description_{$lang} AS description,
               g.categorie_{$lang} AS categorie,
               g.credits_{$lang} AS credits,
               (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
               (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
               (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count
        FROM galerie_medias g
        WHERE g.est_actif = 1
          AND g.type IN ({$inClause})
    ";
    
    $params = [];
    
    // Filtrage par catégorie (si spécifiée)
    if (!empty($category)) {
        $sql .= " AND g.categorie_{$lang} = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY g.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $medias = $this->db->query($sql, $params)->result_array();
    
    // Compter le total (mêmes conditions)
    $countSql = "
        SELECT COUNT(*) as total FROM galerie_medias g 
        WHERE g.est_actif = 1
          AND g.type IN ({$inClause})
    ";
    
    if (!empty($category)) {
        $countSql .= " AND g.categorie_{$lang} = ?";
    }
    
    $total = $this->db->query($countSql, $params)->row()->total ?? 0;
    
    // Formater les médias
    foreach ($medias as &$media) {
        $media = $this->formatMediaForMobile($media);
    }
    
    $this->output->set_output(json_encode([
        'success' => true,
        'data' => $medias,
        'filters' => [
            'type' => $type ?: 'all',
            'allowed_types' => $allowedTypes
        ],
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total
        ]
    ]));
}






    /**
     * GET /api/mobile/media/:id
     * Détail d'un média spécifique
     */
    public function media($identifier)
    {
        $lang = $this->getCurrentLang();
        
        $sql = "
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
                   g.date_media, g.taille, g.mime_type, g.duree,
                   g.contenu_texte_{$lang} AS contenu_texte,
                   g.description_{$lang} AS description,
                   g.categorie_{$lang} AS categorie,
                   g.credits_{$lang} AS credits,
                   g.message_reseaux_{$lang} AS message_reseaux,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                   (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                   (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                   (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
            FROM galerie_medias g
            WHERE g.est_actif = 1
        ";
        
        if (is_numeric($identifier)) {
            $sql .= " AND g.id_media = ?";
            $media = $this->db->query($sql, [$identifier])->row_array();
        } else {
            $sql .= " AND g.slug = ?";
            $media = $this->db->query($sql, [$identifier])->row_array();
        }
        
        if (!$media) {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode([
                'success' => false, 
                'message' => 'Média non trouvé'
            ]));
            return;
        }
        
        // Enregistrer la vue
        $this->recordView($media['id_media']);
        
        $media = $this->formatMediaForMobile($media);
        
        // Récupérer les commentaires
        $comments = $this->db->query("
            SELECT mc.id, mc.comment, mc.created_at,
                   u.id as user_id, 
                   CONCAT(u.prenom, ' ', u.nom) as author_name,
                   u.photo as author_avatar,
                   DATE_FORMAT(mc.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
            FROM media_comments mc
            LEFT JOIN users u ON mc.user_id = u.id
            WHERE mc.id_media = ? AND mc.is_approved = 1 
            ORDER BY mc.created_at DESC 
            LIMIT 50
        ", [$media['id_media']])->result_array();
        
        // Récupérer médias similaires
        $similar = $this->getSimilarMedias($media, $lang);
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $media,
            'comments' => $comments,
            'similar' => $similar
        ]));
    }

    /**
     * GET /api/mobile/categories
     * Liste des catégories
     */
    public function categories()
    {
        $lang = $this->getCurrentLang();
        
        $categories = $this->db->query("
            SELECT g.categorie_{$lang} as nom, COUNT(*) as total
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND g.categorie_{$lang} IS NOT NULL 
            AND g.categorie_{$lang} != ''
            GROUP BY g.categorie_{$lang}
            ORDER BY total DESC
        ")->result_array();
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $categories
        ]));
    }

    /**
     * GET /api/mobile/search?q=mot
     * Recherche de médias
     */
    public function search()
    {
        $query = trim($this->input->get('q'));
        $limit = (int)($this->input->get('limit') ?? 20);
        $lang = $this->getCurrentLang();
        
        if (empty($query) || strlen($query) < 2) {
            $this->output->set_output(json_encode([
                'success' => true,
                'data' => [],
                'message' => 'Terme de recherche trop court (minimum 2 caractères)'
            ]));
            return;
        }
        
        $like = '%' . $this->db->escape_like_str($query) . '%';
        
        $medias = $this->db->query("
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature, g.duree,
                   g.description_{$lang} AS description,
                   g.categorie_{$lang} AS categorie,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND (g.titre LIKE ? 
                 OR g.credits_{$lang} LIKE ? 
                 OR g.description_{$lang} LIKE ?
                 OR g.categorie_{$lang} LIKE ?)
            ORDER BY 
                CASE 
                    WHEN g.titre LIKE ? THEN 10
                    WHEN g.description_{$lang} LIKE ? THEN 5
                    ELSE 1
                END DESC
            LIMIT ?
        ", [$like, $like, $like, $like, $like, $like, $limit])->result_array();
        
        foreach ($medias as &$media) {
            $media = $this->formatMediaForMobile($media);
        }
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $medias,
            'total' => count($medias),
            'query' => $query
        ]));
    }

    /**
     * GET /api/mobile/popular
     * Médias les plus populaires
     */
    public function popular()
    {
        $limit = (int)($this->input->get('limit') ?? 20);
        $lang = $this->getCurrentLang();
        
        $medias = $this->db->query("
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature, g.duree,
                   g.description_{$lang} AS description,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count
            FROM galerie_medias g
            WHERE g.est_actif = 1
            ORDER BY views_count DESC, likes_count DESC
            LIMIT ?
        ", [$limit])->result_array();
        
        foreach ($medias as &$media) {
            $media = $this->formatMediaForMobile($media);
        }
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $medias
        ]));
    }

    /**
     * GET /api/mobile/recent
     * Derniers médias ajoutés
     */
    public function recent()
    {
        $limit = (int)($this->input->get('limit') ?? 20);
        $lang = $this->getCurrentLang();
        
        $medias = $this->db->query("
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature, g.duree,
                   g.description_{$lang} AS description,
                   g.created_at,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
            FROM galerie_medias g
            WHERE g.est_actif = 1
            ORDER BY g.created_at DESC
            LIMIT ?
        ", [$limit])->result_array();
        
        foreach ($medias as &$media) {
            $media = $this->formatMediaForMobile($media);
            $media['added_date'] = date('d/m/Y', strtotime($media['created_at']));
            unset($media['created_at']);
        }
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $medias
        ]));
    }

    /**
     * GET /api/mobile/playlists
     * Playlists (si vous en avez)
     */
    public function playlists()
    {
        // Vérifier si table playlists existe
        if (!$this->db->table_exists('media_playlists')) {
            $this->output->set_output(json_encode([
                'success' => true,
                'data' => []
            ]));
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        if (!$user_id) {
            $this->output->set_output(json_encode([
                'success' => true,
                'data' => []
            ]));
            return;
        }
        
        $playlists = $this->db->query("
            SELECT id, nom, description, cover_image, created_at
            FROM media_playlists
            WHERE user_id = ? OR is_public = 1
            ORDER BY created_at DESC
        ", [$user_id])->result_array();
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $playlists
        ]));
    }

    /**
     * POST /api/mobile/record-view
     * Enregistrer une vue (appelé depuis l'app)
     */
    public function recordViewApi()
    {
        $id_media = $this->input->post('id_media');
        
        if (!$id_media) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'id_media requis']));
            return;
        }
        
        $session_id = session_id();
        $user_id = $this->session->userdata('user_id') ?: null;
        
        // Vérifier si déjà vu dans cette session
        $exists = $this->db->query("
            SELECT id FROM media_views 
            WHERE id_media = ? AND session_id = ? 
            LIMIT 1
        ", [$id_media, $session_id])->num_rows();
        
        if (!$exists) {
            $this->db->insert('media_views', [
                'id_media' => $id_media,
                'user_id' => $user_id,
                'session_id' => $session_id,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Compter les vues
        $total = $this->db->query("
            SELECT COUNT(*) as count FROM media_views WHERE id_media = ?
        ", [$id_media])->row()->count;
        
        $this->output->set_output(json_encode([
            'success' => true,
            'views' => (int)$total
        ]));
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Récupérer la langue actuelle
     */
    private function getCurrentLang()
    {
        $lang = $this->input->get('lang');
        if ($lang && in_array($lang, ['fr', 'en', 'sw'])) {
            return $lang;
        }
        
        // Essayer de récupérer depuis session
        if ($this->session->userdata('current_lang')) {
            return $this->session->userdata('current_lang');
        }
        
        return 'fr'; // Langue par défaut
    }

    /**
     * Formater un média pour l'API mobile
     */
    private function formatMediaForMobile($media)
    {
        if (empty($media)) return $media;
        
        $media['thumbnail_url'] = $this->getThumbnailUrl($media);
        $media['file_url'] = $this->getFileUrl($media);
        $media['duration_formatted'] = $this->formatDuration($media['duree'] ?? 0);
        $media['views_formatted'] = $this->formatNumber($media['views_count'] ?? 0);
        $media['likes_formatted'] = $this->formatNumber($media['likes_count'] ?? 0);
        
        // Détecter les vidéos externes (YouTube, etc.)
        $media['is_external'] = !empty($media['lien']) && (
            strpos($media['lien'], 'youtube.com') !== false ||
            strpos($media['lien'], 'youtu.be') !== false ||
            strpos($media['lien'], 'vimeo.com') !== false
        );
        
        $media['youtube_id'] = $this->extractYoutubeId($media['lien'] ?? '');
        
        // Pour les audios, ajouter métadonnées si disponibles
        if ($media['type'] === 'audio') {
            $media['artist'] = $media['credits'] ?? 'Artiste';
        }
        
        return $media;
    }

    /**
     * Récupérer URL du fichier média
     */
    private function getFileUrl($media)
    {
        // Si c'est un lien externe
        if (!empty($media['lien'])) {
            return $media['lien'];
        }
        
        // Si c'est un fichier local
        if (!empty($media['fichier'])) {
            // Vérifier si le chemin commence déjà par http
            if (strpos($media['fichier'], 'http') === 0) {
                return $media['fichier'];
            }
            return base_url($media['fichier']);
        }
        
        return null;
    }

    /**
     * Récupérer l'URL de la miniature
     */
    private function getThumbnailUrl($media)
    {
        // Pour YouTube
        if (!empty($media['youtube_id'])) {
            return "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
        }
        
        // Miniature personnalisée
        if (!empty($media['miniature'])) {
            if (strpos($media['miniature'], 'http') === 0) {
                return $media['miniature'];
            }
            return base_url($media['miniature']);
        }
        
        // Miniature par défaut selon le type
        $defaults = [
            'video' => base_url('assets/images/video-default.jpg'),
            'audio' => base_url('assets/images/audio-default.png'),
            'image' => base_url('assets/images/image-default.jpg'),
            'default' => base_url('assets/images/default-thumbnail.jpg')
        ];
        
        return $defaults[$media['type']] ?? $defaults['default'];
    }

    /**
     * Extraire ID YouTube
     */
    private function extractYoutubeId($url)
    {
        if (empty($url)) return null;
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Formater la durée
     */
    private function formatDuration($seconds)
    {
        if (!$seconds || $seconds <= 0) return '0:00';
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }

    /**
     * Formater les nombres (1000 → 1k)
     */
    private function formatNumber($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        }
        return (string)$number;
    }

    /**
     * Enregistrer une vue (méthode interne)
     */
    private function recordView($id_media)
    {
        $session_id = session_id();
        $viewed = $this->db->query("
            SELECT id FROM media_views 
            WHERE id_media = ? AND session_id = ? 
            LIMIT 1
        ", [$id_media, $session_id])->num_rows();
        
        if (!$viewed) {
            $this->db->insert('media_views', [
                'id_media' => $id_media,
                'user_id' => $this->session->userdata('user_id') ?: null,
                'session_id' => $session_id,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Récupérer médias similaires
     */
    private function getSimilarMedias($media, $lang)
    {
        $sql = "
            SELECT g.id_media, g.titre, g.slug, g.type, g.miniature, g.duree,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.id_media != ?
        ";
        
        $params = [$media['id_media']];
        
        if (!empty($media['categorie'])) {
            $sql .= " AND g.categorie_{$lang} = ?";
            $params[] = $media['categorie'];
        } else {
            $sql .= " AND g.type = ?";
            $params[] = $media['type'];
        }
        
        $sql .= " ORDER BY RAND() LIMIT 10";
        
        $similar = $this->db->query($sql, $params)->result_array();
        
        foreach ($similar as &$item) {
            $item['thumbnail_url'] = $this->getThumbnailUrl($item);
            $item['duration_formatted'] = $this->formatDuration($item['duree'] ?? 0);
        }
        
        return $similar;
    }























    
    /**
     * GET: Liste des produits avec pagination et filtres
     * Endpoint: /api/products?page=1&limit=10&category_id=1&search=nom
     */
    public function products()
    {
        $page = (int) $this->input->get('page') ?: 1;
        $limit = (int) $this->input->get('limit') ?: 10;
        $category_id = $this->input->get('category_id');
        $search = $this->input->get('search');
        $offset = ($page - 1) * $limit;
        
        // Requête de base
        $this->db->select("id, main_image, slug, price, category_id, created_at, title, description, in_vedette, price_request_count");
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        
        if (!empty($category_id) && $category_id != 'all') {
            $this->db->where('category_id', $category_id);
        }
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }
        
        // Compter le total
        $total_products = $this->db->count_all_results('advertise_product', FALSE);
        
        // Récupérer les produits
        $this->db->limit($limit, $offset);
        $this->db->order_by('in_vedette', 'DESC');
        $this->db->order_by('id', 'DESC');
        $products_db = $this->db->get()->result_array();
        
        $products = [];
        foreach ($products_db as $p) {
            $image_path = !empty($p['main_image']) ? base_url('attachments/Products/' . $p['main_image']) : base_url('attachments/Products/default-product.png');
            $products[] = [
                'id' => (int) $p['id'],
                'title' => $p['title'],
                'description' => strip_tags($p['description']),
                'price' => $p['price'],
                'price_request_count' => (int) ($p['price_request_count'] ?? 0),
                'image' => $image_path,
                'slug' => $p['slug'],
                'category_id' => (int) $p['category_id'],
                'in_vedette' => (int) ($p['in_vedette'] ?? 0),
                'created_at' => $p['created_at']
            ];
        }
        
        $response = [
            'success' => true,
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int) $total_products,
                'total_pages' => ceil($total_products / $limit)
            ]
        ];
        
        echo json_encode($response);
    }
    
    /**
     * GET: Détail d'un produit
     * Endpoint: /api/products/detail/{id}
     */
    public function detail($id)
    {
        // Rechercher par ID ou par slug
        $this->db->select("id, main_image, slug, price, category_id, created_at, title, description, in_vedette");
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('slug', $id);
        }
        
        $product = $this->db->get('advertise_product')->row_array();
        
        if (empty($product)) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            return;
        }
        
        $image_path = !empty($product['main_image']) ? base_url('attachments/Products/' . $product['main_image']) : base_url('attachments/Products/default-product.png');
        
        // Récupérer les produits similaires
        $similar_products = [];
        if (!empty($product['category_id'])) {
            $this->db->select("id, main_image, slug, price, title");
            $this->db->where('category_id', $product['category_id']);
            $this->db->where('is_active', 1);
            $this->db->where('id !=', $product['id']);
            $this->db->where('deleted_at IS NULL');
            $this->db->limit(4);
            $similar = $this->db->get('advertise_product')->result_array();
            
            foreach ($similar as $s) {
                $similar_products[] = [
                    'id' => (int) $s['id'],
                    'title' => $s['title'],
                    'price' => $s['price'],
                    'image' => !empty($s['main_image']) ? base_url('attachments/Products/' . $s['main_image']) : base_url('attachments/Products/default-product.png'),
                    'slug' => $s['slug']
                ];
            }
        }
        
        $response = [
            'success' => true,
            'data' => [
                'id' => (int) $product['id'],
                'title' => $product['title'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => $image_path,
                'slug' => $product['slug'],
                'category_id' => (int) $product['category_id'],
                'created_at' => $product['created_at'],
                'similar_products' => $similar_products
            ]
        ];
        
        echo json_encode($response);
    }
    
   /**
 * GET: Liste des catégories de produits
 * Endpoint: /api/products/categories
 */
public function categoriepro()
{
    $categories = $this->Model->read('product_categories', null, 'name', 'ASC');
    
    if (empty($categories)) {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    
    $data = [];
    foreach ($categories as $cat) {
        // Vérifier si c'est un objet ou un tableau
        if (is_object($cat)) {
            $cat_id = $cat->id;
            $cat_name = $cat->name;
            $cat_image = $cat->image ?? '';
        } else {
            $cat_id = $cat['id'] ?? 0;
            $cat_name = $cat['name'] ?? '';
            $cat_image = $cat['image'] ?? '';
        }
        
        // Compter les produits
        $product_count = $this->Model->count('advertise_product', [
            'category_id' => $cat_id, 
            'is_active' => 1,
            'deleted_at' => null
        ]);
        
        $data[] = [
            'id' => (int) $cat_id,
            'name' => $cat_name,
            'product_count' => $product_count,
            'image' => !empty($cat_image) ? base_url('attachments/Categories/' . $cat_image) : null
        ];
    }
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['success' => true, 'data' => $data]));
}


    
    /**
     * POST: Enregistrer une demande de commande
     * Endpoint: /api/products/save_order
     * Body: product_id, customer_name, customer_phone, customer_country, customer_city, customer_address, customer_notes
     */
    public function save_order()
    {
        // Récupérer les données POST
        $product_id = $this->input->post('product_id');
        $customer_name = trim($this->input->post('customer_name'));
        $customer_phone = trim($this->input->post('customer_phone'));
        $customer_country = trim($this->input->post('customer_country'));
        $customer_city = trim($this->input->post('customer_city'));
        $customer_address = trim($this->input->post('customer_address'));
        $customer_notes = trim($this->input->post('customer_notes'));
        
        // Validation
        if (empty($product_id) || empty($customer_name) || empty($customer_phone) || 
            empty($customer_country) || empty($customer_city) || empty($customer_address)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Tous les champs obligatoires doivent être remplis'
            ]);
            return;
        }
        
        // Vérifier si le produit existe
        $product = $this->Model->readOne('advertise_product', ['id' => $product_id, 'is_active' => 1]);
        if (empty($product)) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            return;
        }
        
        // Incrémenter le compteur de demandes
        $this->db->query("UPDATE advertise_product SET price_request_count = price_request_count + 1 WHERE id = ?", [$product_id]);
        
        // Préparer les données pour la commande
        $order_data = [
            'product_id' => $product_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_country' => $customer_country,
            'customer_city' => $customer_city,
            'customer_address' => $customer_address,
            'customer_notes' => $customer_notes,
            'product_title' => $product->title,
            'product_price' => $product->price,
            'order_status' => 'pending',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->agent->agent_string(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insérer dans la base de données
        $order_id = $this->Model->create('order_requests', $order_data);
        
        if ($order_id) {
            // Générer le message WhatsApp
            $whatsapp_message = "*NOUVELLE COMMANDE NUFOTEC*%0A%0A";
            $whatsapp_message .= "*Produit:* {$product->title}%0A";
            $whatsapp_message .= "*Prix:* {$product->price} BIF%0A";
            $whatsapp_message .= "*Client:* {$customer_name}%0A";
            $whatsapp_message .= "*Téléphone:* {$customer_phone}%0A";
            $whatsapp_message .= "*Pays:* {$customer_country}%0A";
            $whatsapp_message .= "*Ville:* {$customer_city}%0A";
            $whatsapp_message .= "*Adresse:* {$customer_address}%0A";
            if (!empty($customer_notes)) {
                $whatsapp_message .= "*Notes:* {$customer_notes}%0A";
            }
            $whatsapp_message .= "%0A📅 Date: " . date('d/m/Y H:i');
            
            echo json_encode([
                'success' => true, 
                'order_id' => $order_id,
                'whatsapp_message' => $whatsapp_message,
                'whatsapp_number' => '68862945',
                'message' => 'Demande enregistrée avec succès'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
        }
    }
    
    /**
     * POST: Incrémenter le compteur de demande de prix
     * Endpoint: /api/products/increment_price_request
     * Body: product_id
     */
    public function increment_price_request()
    {
        $product_id = $this->input->post('product_id');
        
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'ID produit requis']);
            return;
        }
        
        $this->db->query("UPDATE advertise_product SET price_request_count = price_request_count + 1 WHERE id = ?", [$product_id]);
        
        if ($this->db->affected_rows() > 0) {
            $new_count = $this->db->query("SELECT price_request_count FROM advertise_product WHERE id = ?", [$product_id])->row()->price_request_count;
            echo json_encode([
                'success' => true,
                'product_id' => $product_id,
                'new_count' => $new_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        }
    }
    
    /**
     * GET: Produits vedettes (mis en avant)
     * Endpoint: /api/products/featured
     */
    public function featured()
    {
        $limit = (int) $this->input->get('limit') ?: 6;
        
        $this->db->select("id, main_image, slug, price, title");
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at IS NULL');
        $this->db->where('in_vedette', 1);
        $this->db->limit($limit);
        $this->db->order_by('id', 'DESC');
        $products_db = $this->db->get('advertise_product')->result_array();
        
        $products = [];
        foreach ($products_db as $p) {
            $image_path = !empty($p['main_image']) ? base_url('attachments/Products/' . $p['main_image']) : base_url('attachments/Products/default-product.png');
            $products[] = [
                'id' => (int) $p['id'],
                'title' => $p['title'],
                'price' => $p['price'],
                'image' => $image_path,
                'slug' => $p['slug']
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $products]);
    }
    
    /**
     * POST: Envoyer un message WhatsApp pour le produit
     * Endpoint: /api/products/send_whatsapp
     * Body: product_id, phone_number (optionnel)
     */
    public function send_whatsapp()
    {
        $product_id = $this->input->post('product_id');
        $customer_phone = $this->input->post('phone_number');
        
        $product = $this->Model->readOne('advertise_product', ['id' => $product_id]);
        if (empty($product)) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            return;
        }
        
        $message = "*NUFOTEC BURUNDI - Information produit*%0A%0A";
        $message .= "*Produit:* {$product->title}%0A";
        $message .= "*Prix:* {$product->price} BIF%0A";
        $message .= "%0A";
        $message .= "https://nufotec.bi/produits/{$product->slug}%0A%0A";
        $message .= "Pour plus d'informations, contactez-nous!";
        
        $phone = !empty($customer_phone) ? $customer_phone : '68862945';
        
        echo json_encode([
            'success' => true,
            'whatsapp_url' => "https://wa.me/{$phone}?text={$message}",
            'message' => $message
        ]);
    }

    /**
     * GET /api/mobile/settings
     * Paramètres dynamiques de l'application mobile
     * Tous les textes statiques de l'app sont configurables ici
     */
    public function settings()
    {
        $lang = $this->getCurrentLang();

        $site_name = 'NUFOTEC';
        $site_subtitle = 'BURUNDI';

$settings = [
    'app_name' => $site_name,
    'app_subtitle' => $site_subtitle,
    'tagline' => 'Connaître • Agir • Transformer',
    'welcome_title' => 'Bienvenue sur NUFOTEC BURUNDI',
    'welcome_text' => "Votre plateforme d'information, de conseils et de solutions naturelles pour une vie meilleure.",
    'stats' => [
        ['value' => '100+', 'label' => 'Emission', 'icon' => 'play'],
        ['value' => '50+', 'label' => 'Produits', 'icon' => 'cube'],
        ['value' => '24/7', 'label' => 'Support', 'icon' => 'headset'],
    ],
    'quick_actions' => [
        ['title' => 'Emission', 'description' => 'Vidéos, audios, PDFs', 'icon' => 'play-circle', 'route' => '/medias'],
        ['title' => 'Consultation', 'description' => 'Réservez un expert', 'icon' => 'calendar', 'route' => '/consultation'],
        ['title' => 'Produits', 'description' => 'Solutions naturelles', 'icon' => 'leaf', 'route' => '/produits'],
        ['title' => 'Téléchargé', 'description' => 'Hors-ligne', 'icon' => 'cloud-download', 'route' => '/OfflineScreen'],
    ],
    'nav_items' => [
        ['name' => 'Accueil', 'icon' => 'home', 'route' => '/'],
        ['name' => 'Emission', 'icon' => 'play-circle', 'route' => '/medias'],
        ['name' => 'Consultation', 'icon' => 'calendar', 'route' => '/consultation'],
        ['name' => 'Produits', 'icon' => 'cube', 'route' => '/produits'],
        ['name' => 'Téléchargés', 'icon' => 'cloud-download', 'route' => '/OfflineScreen'],
    ],
    'banner' => [
        'title' => 'Conseils personnalisés',
        'text' => "Besoin d'aide ? Contactez-nous sur WhatsApp au (+257) 79 666 439",
        'button_text' => 'Nous écrire',
    ],
    'consultation' => [
        'title' => 'Consultation',
        'subtitle' => 'Remplissez le formulaire ci-dessous',
        'international_label' => 'International',
        'international_price' => '50 USD / 50 EUR',
        'local_label' => 'Burundi',
        'local_price' => '40 000 FBu',
        'whatsapp_number' => '79666439',
        'submit_text' => 'Envoyer via WhatsApp',
    ],
    'player' => [
        'like_text' => "J'aime",
        'save_text' => 'Sauver',
        'share_text' => 'Partager',
        'download_text' => 'Télécharger',
        'offline_text' => 'Hors-ligne',
        'downloading_text' => 'Chargement…',
        'suggestions_text' => 'Suggestions',
        'loading_text' => 'Chargement…',
        'back_text' => 'Retour',
        'already_offline' => 'Déjà disponible hors-ligne',
        'already_offline_desc' => 'Ce média est déjà téléchargé sur votre appareil.',
        'youtube_not_downloadable' => 'Les vidéos YouTube ne peuvent pas être téléchargées hors-ligne.',
        'no_file' => "Ce média n'a pas de fichier téléchargeable.",
    ],
    'library' => [
        'title' => 'Bibliothèque',
        'login_title' => 'Connectez-vous',
        'login_subtitle' => 'Accédez à vos médias, commentaires et playlists',
        'login_button' => 'Se connecter',
        'menu_items' => [
            ['icon' => 'time-outline', 'label' => 'Historique', 'subtitle' => 'Médias récemment vus', 'color' => 'gold'],
            ['icon' => 'thumbs-up-outline', 'label' => 'Médias aimés', 'subtitle' => "Vos j'aimes", 'color' => 'red'],
            ['icon' => 'bookmark-outline', 'label' => 'Enregistrés', 'subtitle' => 'Regarder plus tard', 'color' => 'green'],
            ['icon' => 'list-outline', 'label' => 'Playlists', 'subtitle' => 'Vos listes de lecture', 'color' => 'gold'],
        ],
    ],
    'products' => [
        'title' => 'Produits',
        'search_placeholder' => 'Rechercher un produit…',
        'detail_button' => 'Détails',
        'featured_text' => 'Vedette',
        'order_text' => 'Commander',
        'quantity_text' => 'Quantité',
        'details_title' => 'Détails du produit',
        'price_request_text' => 'Demander le prix réel',
        'similar_title' => 'Produits similaires',
    ],
    'search' => [
        'placeholder' => 'Rechercher des médias...',
        'hint' => 'Tapez pour chercher…',
    ],
    'media' => [
        'no_media' => 'Aucun média disponible',
        'trending' => 'EMISSION SUR LA SANTÉ',
    ],
    'offline' => [
        'title' => 'Téléchargements',
        'empty_title' => 'Aucun média hors-ligne',
        'empty_text' => 'Téléchargez des vidéos, audios ou PDFs depuis l\'écran de lecture pour y accéder sans connexion.',
        'empty_button' => 'Parcourir les médias',
        'clear_all' => 'Tout effacer',
        'delete_title' => 'Supprimer le téléchargement',
    ],
    'contact' => [
        'whatsapp' => '79666439',
        'phone' => '(+257) 79 666 439',
        'email' => 'nufotecburundi2026@gmail.com',
    ],
];

        // Vérifier si une table de paramètres existe en DB pour surcharge
        if ($this->db->table_exists('app_settings')) {
            $db_settings = $this->db->query("
                SELECT setting_key, setting_value 
                FROM app_settings 
                WHERE is_active = 1
            ")->result_array();

            foreach ($db_settings as $row) {
                $key = $row['setting_key'];
                $value = json_decode($row['setting_value'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($value)) {
                    // Merge récursif pour les objets imbriqués
                    $this->arraySetNested($settings, $key, $value);
                } else {
                    $this->arraySetNested($settings, $key, $row['setting_value']);
                }
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $settings
            ]));
    }


    

    /**
     * Définir une valeur dans un tableau multidimensionnel via une clé pointée (ex: "home.stats.0.label")
     */
    private function arraySetNested(&$array, $key, $value)
    {
        $keys = explode('.', $key);
        $current = &$array;
        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        $current = $value;
    }

}
?>