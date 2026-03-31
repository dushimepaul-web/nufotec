<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Media Controller - Interface Visiteur
 * Interface moderne inspirée de YouTube & Spotify
 */
class Media extends MY_Controller{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('cookie');
        $this->load->library('user_agent');
        $this->load->helper('string');
       /** 
        if (!is_logged_in()) {
            redirect(base_url('Auth'));
            exit;
        }*/
        
        // Désactiver CSRF pour les requêtes AJAX
        if ($this->input->is_ajax_request()) {
            $this->config->set_item('csrf_protection', FALSE);
        }
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    private function getCurrentUser()
    {
        if ($this->session->userdata('user_id')) {
            return $this->db->query("
                SELECT id, uuid, email, nom, prenom, photo, type_utilisateur 
                FROM users 
                WHERE id = ? AND is_active = 1
            ", [$this->session->userdata('user_id')])->row_array();
        }
        return null;
    }

    /**
     * Page d'accueil - Découverte des médias
     */
    public function index()
{
    $user = $this->getCurrentUser();
    
    // CORRECTION : Pas de paramètre dans la requête
    $medias = $this->db->query("
        SELECT g.*,
               (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
               (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
               (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
               (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
               (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
               (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
               (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
        FROM galerie_medias g
        WHERE g.est_actif = 1
        ORDER BY g.created_at DESC
    ")->result_array();  // PAS de paramètre
    
    $medias = $this->formatMedias($medias);
    $categories = $this->getCategoriesWithCount();
    
    $data = [
        'medias' => $medias,
        'categories' => $categories,
        'current_type' => null,
        'search_query' => null,
        'results_count' => count($medias),
        'user' => $user
    ];
    
    $this->load->view('Media_View', $data);
}

    /**
     * Vue filtrée par type de média
     */
    /**
 * Vue filtrée par type de média
 */
/**
 * Vue filtrée par type de média
 * Pour les vidéos, on affiche aussi les liens vidéo (type 'link')
 */
public function type($type)
{
    $valid_types = ['video', 'audio', 'image', 'document', 'link', 'autre'];
    
    if (!in_array($type, $valid_types)) {
        show_404();
        return;
    }
    
    $user = $this->getCurrentUser();
    
    // Récupérer les médias selon le type
    $medias = $this->getMediasByType($type);
    
    $medias = $this->formatMedias($medias);
    
    // Récupérer les statistiques
    $stats = $this->getTypeStats($type);
    
    $categories = $this->getCategoriesWithCount();
    
    $data = [
        'medias' => $medias,
        'categories' => $categories,
        'current_type' => $type,
        'search_query' => null,
        'results_count' => count($medias),
        'user' => $user,
        'stats' => $stats
    ];
    
    $this->load->view('Media_View', $data);
}

/**
 * Récupérer les médias par type avec gestion spéciale pour les vidéos
 */
private function getMediasByType($type)
{
    if ($type === 'video') {
        // Inclure les vidéos locales et les liens vidéo
        $sql = "
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                   (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                   (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                   (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                   (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings,
                   1 as is_video_content
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND (
                g.type = 'video' 
                OR (g.type = 'link' AND g.lien IS NOT NULL AND (
                    g.lien LIKE '%youtube%' OR 
                    g.lien LIKE '%youtu.be%' OR 
                    g.lien LIKE '%vimeo%' OR 
                    g.lien LIKE '%dailymotion%' OR
                    g.lien LIKE '%facebook.com/watch%' OR
                    g.lien LIKE '%twitch.tv%'
                ))
            )
            ORDER BY 
                CASE 
                    WHEN g.type = 'video' THEN 0
                    ELSE 1
                END,
                g.created_at DESC
        ";
        
        return $this->db->query($sql)->result_array();
        
    } else {
        // Pour les autres types, requête simple
        $sql = "
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                   (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                   (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                   (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                   (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings,
                   0 as is_video_content
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.type = ?
            ORDER BY g.created_at DESC
        ";
        
        return $this->db->query($sql, [$type])->result_array();
    }
}

/**
 * Obtenir les statistiques par type
 */
private function getTypeStats($type)
{
    if ($type === 'video') {
        $result = $this->db->query("
            SELECT 
                COUNT(CASE WHEN g.type = 'video' THEN 1 END) as local_videos,
                COUNT(CASE WHEN g.type = 'link' AND (
                    g.lien LIKE '%youtube%' OR 
                    g.lien LIKE '%youtu.be%' OR 
                    g.lien LIKE '%vimeo%' OR 
                    g.lien LIKE '%dailymotion%' OR
                    g.lien LIKE '%facebook.com/watch%' OR
                    g.lien LIKE '%twitch.tv%'
                ) THEN 1 END) as external_videos,
                COUNT(*) as total
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND (
                g.type = 'video' 
                OR (g.type = 'link' AND g.lien IS NOT NULL AND (
                    g.lien LIKE '%youtube%' OR 
                    g.lien LIKE '%youtu.be%' OR 
                    g.lien LIKE '%vimeo%' OR 
                    g.lien LIKE '%dailymotion%' OR
                    g.lien LIKE '%facebook.com/watch%' OR
                    g.lien LIKE '%twitch.tv%'
                ))
            )
        ")->row_array();
        
        return [
            'videos_locales' => (int)($result['local_videos'] ?? 0),
            'videos_externes' => (int)($result['external_videos'] ?? 0),
            'total' => (int)($result['total'] ?? 0)
        ];
    }
    
    return null;
}

    /**
     * Détail d'un média - Supporte à la fois l'ID et le slug
     */
    /**
 * Détail d'un média - Supporte à la fois l'ID et le slug
 */
public function detail($identifier)
{
    $user = $this->getCurrentUser();
    $user_id = $user ? $user['id'] : null;
    
    // Déterminer si c'est un ID numérique ou un slug
    if (is_numeric($identifier)) {
        // Recherche par ID
        $media = $this->db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                   (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                   (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                   (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                   (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings,
                   (SELECT rating FROM media_ratings WHERE id_media = g.id_media AND user_id = ?) as user_rating,
                   (SELECT action FROM media_likes WHERE id_media = g.id_media AND user_id = ?) as user_like_action
            FROM galerie_medias g
            WHERE g.id_media = ? AND g.est_actif = 1
        ", [$user_id, $user_id, $identifier])->row_array();  // 3 paramètres : user_id (x2) + id_media
    } else {
        // Recherche par slug
        $media = $this->db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                   (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                   (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                   (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                   (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings,
                   (SELECT rating FROM media_ratings WHERE id_media = g.id_media AND user_id = ?) as user_rating,
                   (SELECT action FROM media_likes WHERE id_media = g.id_media AND user_id = ?) as user_like_action
            FROM galerie_medias g
            WHERE g.slug = ? AND g.est_actif = 1
        ", [$user_id, $user_id, $identifier])->row_array();  // 3 paramètres : user_id (x2) + slug
    }
    
    if (!$media) {
        show_404();
        return;
    }
    
    $media = $this->formatMedia($media);
    
    // Rediriger vers l'URL avec slug si l'identifiant était un ID (SEO friendly)
    if (is_numeric($identifier) && !empty($media['slug'])) {
        redirect("media/detail/{$media['slug']}", 'location', 301);
        return;
    }
    
    // Enregistrer la vue
    $this->recordView($media['id_media'], $user_id);
    
    // Récupérer les commentaires avec les informations utilisateur
    $comments = $this->db->query("
        SELECT mc.*,
               u.nom, u.prenom, u.photo, u.email,
               DATE_FORMAT(mc.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
        FROM media_comments mc
        LEFT JOIN users u ON mc.user_id = u.id
        WHERE mc.id_media = ? AND mc.is_approved = 1 
        ORDER BY mc.created_at DESC 
        LIMIT 50
    ", [$media['id_media']])->result_array();
    
    // Récupérer les médias recommandés
    $recommended = $this->getRecommendedMedias($media);
    $recommended = $this->formatMedias($recommended);
    
    // Récupérer les catégories
    $categories = $this->getCategoriesWithCount();
    
    $data = [
        'media' => $media,
        'comments' => $comments,
        'recommended' => $recommended,
        'categories' => $categories,
        'user' => $user
    ];
    
    $this->load->view('Media_Detail_View', $data);
}
    // ==================== API ENDPOINTS ====================

    /**
     * API: Enregistrer une vue
     */
    public function apiTrackView()
    {
        $id_media = $this->input->post('id_media');
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        $user_agent = $this->input->user_agent();
        $user_id = $this->session->userdata('user_id') ?: null;
        
        // Vérifier si déjà vu dans cette session
        $viewed = $this->db->query("
            SELECT id FROM media_views 
            WHERE id_media = ? AND session_id = ? 
            LIMIT 1
        ", [$id_media, $session_id])->num_rows();
        
        if (!$viewed) {
            $this->db->insert('media_views', [
                'id_media' => $id_media,
                'user_id' => $user_id,
                'session_id' => $session_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $views = $this->db->query("
            SELECT COUNT(*) as count FROM media_views WHERE id_media = ?
        ", [$id_media])->row()->count;
        
        echo json_encode(['success' => true, 'views' => $views]);
    }

    /**
     * API: Like/Dislike
     */
    public function apiToggleLike()
    {
        $id_media = $this->input->post('id_media');
        $action = $this->input->post('action');
        $user_id = $this->session->userdata('user_id');
        $ip_address = $this->input->ip_address();
        
        // Si utilisateur connecté, utiliser user_id, sinon utiliser ip_address
        if ($user_id) {
            $existing = $this->db->query("
                SELECT id, action FROM media_likes 
                WHERE id_media = ? AND user_id = ? 
                LIMIT 1
            ", [$id_media, $user_id])->row();
        } else {
            $existing = $this->db->query("
                SELECT id, action FROM media_likes 
                WHERE id_media = ? AND ip_address = ? AND user_id IS NULL
                LIMIT 1
            ", [$id_media, $ip_address])->row();
        }
        
        if ($action === 'remove') {
            if ($existing) {
                $this->db->where('id', $existing->id)->delete('media_likes');
            }
        } else {
            if ($existing) {
                if ($existing->action === $action) {
                    $this->db->where('id', $existing->id)->delete('media_likes');
                } else {
                    $this->db->where('id', $existing->id)->update('media_likes', ['action' => $action]);
                }
            } else {
                $this->db->insert('media_likes', [
                    'id_media' => $id_media,
                    'user_id' => $user_id,
                    'ip_address' => $user_id ? null : $ip_address,
                    'action' => $action,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        $stats = $this->db->query("
            SELECT 
                COUNT(CASE WHEN action = 'like' THEN 1 END) as likes,
                COUNT(CASE WHEN action = 'dislike' THEN 1 END) as dislikes
            FROM media_likes 
            WHERE id_media = ?
        ", [$id_media])->row();
        
        echo json_encode([
            'success' => true,
            'likes' => (int)$stats->likes,
            'dislikes' => (int)$stats->dislikes
        ]);
    }

    /**
     * API: Noter un média
     */
    public function apiRateMedia()
    {
        $id_media = $this->input->post('id_media');
        $rating = (int)$this->input->post('rating');
        $user_id = $this->session->userdata('user_id');
        $ip_address = $this->input->ip_address();
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Note invalide']);
            return;
        }
        
        if ($user_id) {
            $existing = $this->db->query("
                SELECT id FROM media_ratings 
                WHERE id_media = ? AND user_id = ? 
                LIMIT 1
            ", [$id_media, $user_id])->row();
        } else {
            $existing = $this->db->query("
                SELECT id FROM media_ratings 
                WHERE id_media = ? AND ip_address = ? AND user_id IS NULL
                LIMIT 1
            ", [$id_media, $ip_address])->row();
        }
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update('media_ratings', ['rating' => $rating]);
        } else {
            $this->db->insert('media_ratings', [
                'id_media' => $id_media,
                'user_id' => $user_id,
                'ip_address' => $user_id ? null : $ip_address,
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $stats = $this->db->query("
            SELECT 
                AVG(rating) as avg,
                COUNT(*) as total
            FROM media_ratings 
            WHERE id_media = ?
        ", [$id_media])->row();
        
        echo json_encode([
            'success' => true,
            'average' => round($stats->avg, 1),
            'total' => (int)$stats->total
        ]);
    }

public function apiSearch()
{
    $query = $this->input->get('q');
    $limit = (int)($this->input->get('limit') ?? 10);
    
    if (empty($query) || strlen($query) < 2) {
        redirect('media');
    }
    
    $like = '%' . $this->db->escape_like_str($query) . '%';
    
    $medias = $this->db->query("
        SELECT g.id_media, g.titre, g.type, g.slug, g.miniature,
               (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
        FROM galerie_medias g
        WHERE g.est_actif = 1 
        AND (g.titre LIKE ? OR g.credits LIKE ?)
        ORDER BY 
            CASE 
                WHEN g.titre LIKE ? THEN 10
                ELSE 1
            END DESC
        LIMIT ?
    ", [
        $like, $like,
        $like,
        $limit
    ])->result_array();

    // 🔥 IMPORTANT : envoyer les données à la vue
    $data['search_query'] = $query;
    $data['results_count'] = count($medias);
    $data['medias'] = $medias;

    // Charger ta vue media_view
    $this->load->view('Media_View', $data);
}

    /**
     * API: Ajouter un commentaire
     */
    public function apiAddComment()
    {
        $id_media = $this->input->post('id_media');
        $comment = trim($this->input->post('comment'));
        $user = $this->getCurrentUser();
        $ip_address = $this->input->ip_address();
        
        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
            return;
        }
        
        if (strlen($comment) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 1000 caractères)']);
            return;
        }
        
        // Si utilisateur connecté, utiliser ses informations
        if ($user) {
            $author_name = $user['prenom'] . ' ' . $user['nom'];
            $author_email = $user['email'];
            $user_id = $user['id'];
        } else {
            $author_name = trim($this->input->post('author_name')) ?: 'Visiteur';
            $author_email = null;
            $user_id = null;
        }
        
        $this->db->insert('media_comments', [
            'id_media' => $id_media,
            'user_id' => $user_id,
            'author_name' => $author_name,
            'author_email' => $author_email,
            'comment' => $comment,
            'ip_address' => $ip_address,
            'created_at' => date('Y-m-d H:i:s'),
            'is_approved' => 1
        ]);
        
        $comment_id = $this->db->insert_id();
        $new_comment = $this->db->query("
            SELECT mc.*,
                   u.nom, u.prenom, u.photo, u.email,
                   DATE_FORMAT(mc.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
            FROM media_comments mc
            LEFT JOIN users u ON mc.user_id = u.id
            WHERE mc.id = ?
        ", [$comment_id])->row_array();
        
        $total_comments = $this->db->query("
            SELECT COUNT(*) as count FROM media_comments 
            WHERE id_media = ? AND is_approved = 1
        ", [$id_media])->row()->count;
        
        echo json_encode([
            'success' => true,
            'comment' => $new_comment,
            'comments_count' => (int)$total_comments
        ]);
    }

    /**
     * API: Récupérer les commentaires
     */
    public function apiGetComments($id_media)
    {
        $comments = $this->db->query("
            SELECT mc.*,
                   u.nom, u.prenom, u.photo, u.email,
                   DATE_FORMAT(mc.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
            FROM media_comments mc
            LEFT JOIN users u ON mc.user_id = u.id
            WHERE mc.id_media = ? AND mc.is_approved = 1 
            ORDER BY mc.created_at DESC 
            LIMIT 50
        ", [$id_media])->result_array();
        
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);
    }

    /**
     * API: Ajouter/retirer des favoris
     */
    public function apiToggleFavorite()
    {
        $id_media = $this->input->post('id_media');
        $user = $this->getCurrentUser();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            echo json_encode([
                'success' => false, 
                'message' => 'Vous devez être connecté pour ajouter aux favoris',
                'need_login' => true
            ]);
            return;
        }
        
        $user_id = $user['id'];
        
        // Vérifier si déjà en favori
        $existing = $this->db->query("
            SELECT id FROM media_favorites 
            WHERE id_media = ? AND user_id = ?
        ", [$id_media, $user_id])->row();
        
        if ($existing) {
            $this->db->where('id', $existing->id)->delete('media_favorites');
            $is_favorite = false;
        } else {
            $this->db->insert('media_favorites', [
                'id_media' => $id_media,
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $is_favorite = true;
        }
        
        echo json_encode([
            'success' => true,
            'is_favorite' => $is_favorite,
            'message' => $is_favorite ? 'Ajouté aux favoris' : 'Retiré des favoris'
        ]);
    }

    /**
     * API: Récupérer les favoris de l'utilisateur
     */
    public function apiGetFavorites()
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté', 'medias' => []]);
            return;
        }
        
        $medias = $this->db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count
            FROM galerie_medias g
            INNER JOIN media_favorites mf ON mf.id_media = g.id_media
            WHERE mf.user_id = ? AND g.est_actif = 1
            ORDER BY mf.created_at DESC
        ", [$user['id']])->result_array();
        
        $medias = $this->formatMedias($medias);
        
        echo json_encode([
            'success' => true,
            'medias' => $medias
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    private function recordView($id_media, $user_id = null)
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
                'user_id' => $user_id,
                'session_id' => $session_id,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function formatMedias($medias)
    {
        return array_map([$this, 'formatMedia'], $medias);
    }

    private function formatMedia($media)
    {
        // Durée formatée
        $media['duration_formatted'] = $this->formatDuration($media['duree'] ?? 0);
        
        // Youtube ID
        $media['youtube_id'] = $this->extractYoutubeId($media['lien'] ?? '');
        
        // URLs des fichiers
        if (!empty($media['fichier'])) {
            $media['fichier_url'] = base_url($media['fichier']);
        } else {
            $media['fichier_url'] = '';
        }
        
        // Thumbnail URL
        $media['thumbnail_url'] = $this->getThumbnailUrl($media);
        
        // Cover URL pour audio
        $media['cover_url'] = $this->getCoverUrl($media);
        
        // Métadonnées audio
        if ($media['type'] === 'audio') {
            $metadata = !empty($media['metadata_id3']) ? json_decode($media['metadata_id3'], true) : [];
            $media['artist'] = $metadata['artist'] ?? ($media['credits'] ?? 'Artiste inconnu');
            $media['album'] = $metadata['album'] ?? '';
        }
        
        // Stats formatées
        $media['views_formatted'] = $this->formatNumber($media['views_count'] ?? 0);
        $media['likes_formatted'] = $this->formatNumber($media['likes_count'] ?? 0);
        
        return $media;
    }

    private function getThumbnailUrl($media)
    {
        // Pour les liens YouTube
        if (!empty($media['youtube_id'])) {
            return "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
        }
        
        // Pour les miniatures personnalisées
        if (!empty($media['miniature']) && filter_var($media['miniature'], FILTER_VALIDATE_URL) === false) {
            return base_url($media['miniature']);
        } elseif (!empty($media['miniature'])) {
            return $media['miniature'];
        }
        
        // Pour les images
        if ($media['type'] === 'image' && !empty($media['fichier'])) {
            return base_url($media['fichier']);
        }
        
        // Pour les vidéos
        if ($media['type'] === 'video' && !empty($media['fichier'])) {
            $thumb_path = FCPATH . 'attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg';
            if (file_exists($thumb_path)) {
                return base_url('attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg');
            }
        }
        
        // Pour les audios
        if ($media['type'] === 'audio' && !empty($media['fichier'])) {
            $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
            if (file_exists($cover_path)) {
                return base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg');
            }
        }
        
        // Default par type
        $defaults = [
            'audio' => 'assets/images/audio-default.png',
            'video' => 'assets/images/video-default.jpg',
            'image' => 'assets/images/image-default.jpg',
            'document' => 'assets/images/document-default.jpg',
            'link' => 'assets/images/link-default.jpg',
            'autre' => 'assets/images/default-thumbnail.jpg'
        ];
        
        return base_url($defaults[$media['type']] ?? 'assets/images/default-thumbnail.jpg');
    }

    private function getCoverUrl($media)
    {
        if ($media['type'] !== 'audio') {
            return null;
        }
        
        if (!empty($media['fichier'])) {
            $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
            if (file_exists($cover_path)) {
                return base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg');
            }
        }
        
        if (!empty($media['miniature'])) {
            return base_url($media['miniature']);
        }
        
        return base_url('assets/images/audio-default.png');
    }

    private function getCategoriesWithCount()
    {
        return $this->db->query("
            SELECT categorie, COUNT(*) as count 
            FROM galerie_medias 
            WHERE est_actif = 1 AND categorie IS NOT NULL AND categorie != ''
            GROUP BY categorie 
            ORDER BY count DESC
        ")->result_array();
    }

    private function getRecommendedMedias($current_media, $user_id = null, $limit = 10)
    {
        $sql = "
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_favorites WHERE id_media = g.id_media AND user_id = ?) as is_favorite
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.id_media != ?
        ";
        
        $params = [$user_id, $current_media['id_media']];
        
        if (!empty($current_media['categorie'])) {
            $sql .= " AND g.categorie = ?";
            $params[] = $current_media['categorie'];
        } else {
            $sql .= " AND g.type = ?";
            $params[] = $current_media['type'];
        }
        
        $sql .= " ORDER BY RAND() LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->result_array();
    }

    private function extractYoutubeId($url)
    {
        if (empty($url)) return null;
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
        return $matches[1] ?? null;
    }

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
     * Télécharger un fichier média - VERSION CORRIGÉE
     * Supporte à la fois les paramètres GET et les segments d'URL
     */
    public function downloader($identifier = null)
    {
        // Récupérer l'identifiant depuis les différentes sources possibles
        if (empty($identifier)) {
            $identifier = $this->input->get('slug') ?? $this->input->get('id');
        }
        
        // Vérifier qu'un identifiant est fourni
        if (empty($identifier)) {
            log_message('error', 'Aucun identifiant fourni pour le téléchargement');
            show_404();
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Déterminer si c'est un ID numérique ou un slug
        if (is_numeric($identifier)) {
            // Recherche par ID
            $media = $this->db->query("
                SELECT id_media, fichier, titre, type, sous_type, taille, slug
                FROM galerie_medias 
                WHERE id_media = ? AND est_actif = 1
            ", [$identifier])->row_array();
        } else {
            // Recherche par slug
            $media = $this->db->query("
                SELECT id_media, fichier, titre, type, sous_type, taille, slug
                FROM galerie_medias 
                WHERE slug = ? AND est_actif = 1
            ", [$identifier])->row_array();
        }
        
        if (!$media || empty($media['fichier'])) {
            log_message('error', 'Média non trouvé: ' . $identifier);
            show_404();
            return;
        }
        
        // Construire le chemin complet selon le type
        $base = FCPATH;
        $file_path = '';
        
        switch($media['type']) {
            case 'video':
                $file_path = $base . 'attachments/Video/Originals/' . $media['fichier'];
                if (!file_exists($file_path)) {
                    $file_path = $base . 'attachments/Video/Encoded/' . $media['fichier'];
                }
                break;
                
            case 'audio':
                $file_path = $base . 'attachments/Audio/Originals/' . $media['fichier'];
                if (!file_exists($file_path)) {
                    $file_path = $base . 'attachments/Audio/Converted/' . $media['fichier'];
                }
                break;
                
            case 'image':
                $file_path = $base . 'attachments/Images/' . $media['fichier'];
                break;
                
            case 'document':
                $file_path = $base . 'attachments/Documents/' . $media['fichier'];
                break;
                
            default:
                $file_path = $base . $media['fichier'];
                break;
        }
        
        // Vérifier si le fichier existe
        if (!file_exists($file_path)) {
            log_message('error', 'Fichier non trouvé: ' . $file_path);
            $possible_paths = [
                $base . 'attachments/Video/Originals/' . $media['fichier'],
                $base . 'attachments/Video/Encoded/' . $media['fichier'],
                $base . 'attachments/Audio/Originals/' . $media['fichier'],
                $base . 'attachments/Audio/Converted/' . $media['fichier'],
                $base . 'attachments/Images/' . $media['fichier'],
                $base . 'attachments/Documents/' . $media['fichier'],
                $base . 'uploads/temp/video/' . $media['fichier'],
                $base . 'uploads/temp/audio/' . $media['fichier'],
            ];
            
            $found = false;
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    $file_path = $path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                show_404();
                return;
            }
        }
        
        // Log du téléchargement
        $this->db->insert('media_downloads', [
            'id_media' => $media['id_media'],
            'user_id' => $user ? $user['id'] : null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'downloaded_at' => date('Y-m-d H:i:s')
        ]);
        
        // Mettre à jour le compteur
        $this->db->query("
            UPDATE galerie_medias 
            SET telechargements = telechargements + 1 
            WHERE id_media = ?
        ", [$media['id_media']]);
        
        // Nettoyer le nom du fichier pour le téléchargement
        $extension = strtolower(pathinfo($media['fichier'], PATHINFO_EXTENSION));
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $media['titre']);
        $filename = $filename . '.' . $extension;
        
        // Forcer le téléchargement avec les bons headers pour mobile
        $this->load->helper('download');
        $this->load->helper('file');
        
        $mime = get_mime_by_extension($file_path) ?: 'application/octet-stream';
        $file_size = filesize($file_path);
        
        // Headers pour forcer le téléchargement sur tous les navigateurs y compris mobile
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Support pour les requêtes range (streaming/download progressif)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->rangeDownload($file_path);
        } else {
            readfile($file_path);
        }
        
        exit;
    }
    
    /**
     * Gestion des téléchargements par plages (pour support mobile et reprise)
     */
    private function rangeDownload($file)
    {
        $fp = @fopen($file, 'rb');
        $size = filesize($file);
        $length = $size;
        $start = 0;
        $end = $size - 1;
        
        header('Accept-Ranges: bytes');
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;
            
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            
            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            
            $c_end = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            
            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }
        
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");
        
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        
        fclose($fp);
    }

}