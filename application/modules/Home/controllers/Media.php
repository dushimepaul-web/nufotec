<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Media Controller - Interface Visiteur
 * Interface moderne inspirée de YouTube & Spotify
 * Version MULTILINGUE (FR, EN, SW)
 */
class Media extends Public_Controller{

    // Ne pas redéclarer $current_lang, il est déjà public dans MY_Controller

   function __construct()
{
    parent::__construct();
    $this->load->model('Model');
    $this->load->helper('cookie');
    $this->load->library('user_agent');
    $this->load->helper('string');
    
    // Désactiver CSRF pour les requêtes AJAX
    if ($this->input->is_ajax_request()) {
        $this->config->set_item('csrf_protection', FALSE);
    }
    
    // ============================================
    // PROTECTION TOTALE - TOUTES LES MÉTHODES NÉCESSITENT UNE CONNEXION
    // ============================================
    
    // Récupérer l'utilisateur connecté
    $user = $this->getCurrentUser();
    
    // Si l'utilisateur n'est pas connecté
    if (!$user) {
        // Sauvegarder l'URL demandée
        $this->session->set_userdata('login_redirect', current_url());
        
        // Rediriger vers la page de connexion
        redirect('Auth');
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
     * Page d'accueil - Découverte des médias (multilingue)
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        $lang = $this->current_lang;
        
        $sql = "
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
                   g.date_media, g.taille, g.mime_type, g.duree, g.est_actif,
                   g.description_{$lang} AS description,
                   g.categorie_{$lang} AS categorie,
                   g.credits_{$lang} AS credits,
                   g.message_reseaux_{$lang} AS message_reseaux,
                   g.contenu_texte_{$lang} AS contenu_texte,
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
        ";
        $medias = $this->db->query($sql)->result_array();
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
     * Vue filtrée par type de média (multilingue)
     */
    public function type($type)
    {
        $valid_types = ['video', 'audio', 'image', 'book', 'document', 'link'];
        if (!in_array($type, $valid_types)) {
            show_404();
            return;
        }
        
        $user = $this->getCurrentUser();
        $types_autre = ['image', 'book', 'document'];
        
        if (in_array($type, $types_autre)) {
            $display_type = 'autre';
            $sub_type = $type;
            $page_title = 'Autre';
        } else {
            $display_type = $type;
            $sub_type = null;
            $page_title = ucfirst($type);
        }
        
        $medias = $this->getMediasByType($type);
        $medias = $this->formatMedias($medias);
        $stats = $this->getTypeStats($type);
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'medias' => $medias,
            'categories' => $categories,
            'current_type' => $display_type,
            'original_type' => $type,
            'sub_type' => $sub_type,
            'page_title' => $page_title,
            'search_query' => null,
            'results_count' => count($medias),
            'user' => $user,
            'stats' => $stats
        ];
        
        $this->load->view('Media_View', $data);
    }

    /**
     * Récupérer les médias par type (multilingue)
     */
    private function getMediasByType($type)
    {
        $lang = $this->current_lang;
        $types_autre = ['image', 'book', 'document'];
        
        $select = "
            g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
            g.date_media, g.taille, g.mime_type, g.duree, g.est_actif,
            g.description_{$lang} AS description,
            g.categorie_{$lang} AS categorie,
            g.credits_{$lang} AS credits,
            g.message_reseaux_{$lang} AS message_reseaux,
            g.contenu_texte_{$lang} AS contenu_texte,
            (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
            (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
            (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
            (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
            (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
            (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
        ";
        
        if ($type === 'video') {
            $sql = "
                SELECT {$select}, 1 as is_video_content
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
                ORDER BY CASE WHEN g.type = 'video' THEN 0 ELSE 1 END, g.created_at DESC
            ";
            return $this->db->query($sql)->result_array();
        } elseif (in_array($type, $types_autre)) {
            $sql = "
                SELECT {$select}, 0 as is_video_content, '{$type}' as sub_type_filter
                FROM galerie_medias g
                WHERE g.est_actif = 1 AND g.type = ?
                ORDER BY g.created_at DESC
            ";
            return $this->db->query($sql, [$type])->result_array();
        } else {
            $sql = "
                SELECT {$select}, 0 as is_video_content
                FROM galerie_medias g
                WHERE g.est_actif = 1 AND g.type = ?
                ORDER BY g.created_at DESC
            ";
            return $this->db->query($sql, [$type])->result_array();
        }
    }

    /**
     * Statistiques par type
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
     * Détail d'un média (multilingue)
     */
    public function detail($identifier)
    {
        $user = $this->getCurrentUser();
        $user_id = $user ? $user['id'] : null;
        $lang = $this->current_lang;
        
        $select = "
            g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
            g.date_media, g.taille, g.mime_type, g.duree, g.est_actif,
            g.description_{$lang} AS description,
            g.categorie_{$lang} AS categorie,
            g.credits_{$lang} AS credits,
            g.message_reseaux_{$lang} AS message_reseaux,
            g.contenu_texte_{$lang} AS contenu_texte,
            (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
            (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
            (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
            (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
            (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
            (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings,
            (SELECT rating FROM media_ratings WHERE id_media = g.id_media AND user_id = ?) as user_rating,
            (SELECT action FROM media_likes WHERE id_media = g.id_media AND user_id = ?) as user_like_action
        ";
        
        if (is_numeric($identifier)) {
            $sql = "SELECT {$select} FROM galerie_medias g WHERE g.id_media = ? AND g.est_actif = 1";
            $media = $this->db->query($sql, [$user_id, $user_id, $identifier])->row_array();
        } else {
            $sql = "SELECT {$select} FROM galerie_medias g WHERE g.slug = ? AND g.est_actif = 1";
            $media = $this->db->query($sql, [$user_id, $user_id, $identifier])->row_array();
        }
        
        if (!$media) {
            show_404();
            return;
        }
        
        $media = $this->formatMedia($media);
        
        // Redirection SEO : ID → slug avec langue
        if (is_numeric($identifier) && !empty($media['slug'])) {
            redirect($this->current_lang . '/media/detail/' . $media['slug'], 'location', 301);
            return;
        }
        
        // Enregistrer la vue
        $this->recordView($media['id_media'], $user_id);
        
        // Commentaires
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
        
        // Recommandations
        $recommended = $this->getRecommendedMedias($media, $user_id);
        $recommended = $this->formatMedias($recommended);
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

    public function apiTrackView()
    {
        $id_media = $this->input->post('id_media');
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        $user_agent = $this->input->user_agent();
        $user_id = $this->session->userdata('user_id') ?: null;
        
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

    public function apiToggleLike()
    {
        $id_media = $this->input->post('id_media');
        $action = $this->input->post('action');
        $user_id = $this->session->userdata('user_id');
        $ip_address = $this->input->ip_address();
        
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
        $lang = $this->current_lang;
        
        if (empty($query) || strlen($query) < 2) {
            redirect($this->current_lang . '/media');
            return;
        }
        
        $like = '%' . $this->db->escape_like_str($query) . '%';
        
        $sql = "
            SELECT g.id_media, g.titre, g.type, g.slug, g.miniature,
                   g.description_{$lang} as description,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND (g.titre LIKE ? OR g.credits_{$lang} LIKE ? OR g.description_{$lang} LIKE ?)
            ORDER BY 
                CASE 
                    WHEN g.titre LIKE ? THEN 10
                    ELSE 1
                END DESC
            LIMIT ?
        ";
        $medias = $this->db->query($sql, [$like, $like, $like, $like, $limit])->result_array();
        
        $data['search_query'] = $query;
        $data['results_count'] = count($medias);
        $data['medias'] = $medias;
        $data['categories'] = $this->getCategoriesWithCount();
        $data['user'] = $this->getCurrentUser();
        $data['current_type'] = null;
        
        $this->load->view('Media_View', $data);
    }

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

    public function apiToggleFavorite()
    {
        $id_media = $this->input->post('id_media');
        $user = $this->getCurrentUser();
        
        if (!$user) {
            echo json_encode([
                'success' => false, 
                'message' => 'Vous devez être connecté pour ajouter aux favoris',
                'need_login' => true
            ]);
            return;
        }
        
        $user_id = $user['id'];
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

    public function apiGetFavorites()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté', 'medias' => []]);
            return;
        }
        
        $lang = $this->current_lang;
        $sql = "
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
                   g.description_{$lang} AS description,
                   g.categorie_{$lang} AS categorie,
                   g.credits_{$lang} AS credits,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count
            FROM galerie_medias g
            INNER JOIN media_favorites mf ON mf.id_media = g.id_media
            WHERE mf.user_id = ? AND g.est_actif = 1
            ORDER BY mf.created_at DESC
        ";
        $medias = $this->db->query($sql, [$user['id']])->result_array();
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
        $media['duration_formatted'] = $this->formatDuration($media['duree'] ?? 0);
        $media['youtube_id'] = $this->extractYoutubeId($media['lien'] ?? '');
        $media['fichier_url'] = !empty($media['fichier']) ? base_url($media['fichier']) : '';
        $media['thumbnail_url'] = $this->getThumbnailUrl($media);
        $media['cover_url'] = $this->getCoverUrl($media);
        
        if ($media['type'] === 'audio') {
            $metadata = !empty($media['metadata_id3']) ? json_decode($media['metadata_id3'], true) : [];
            $media['artist'] = $metadata['artist'] ?? ($media['credits'] ?? 'Artiste inconnu');
            $media['album'] = $metadata['album'] ?? '';
        }
        
        $media['views_formatted'] = $this->formatNumber($media['views_count'] ?? 0);
        $media['likes_formatted'] = $this->formatNumber($media['likes_count'] ?? 0);
        
        return $media;
    }

    private function getThumbnailUrl($media)
    {
        if (!empty($media['youtube_id'])) {
            return "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
        }
        if (!empty($media['miniature']) && filter_var($media['miniature'], FILTER_VALIDATE_URL) === false) {
            return base_url($media['miniature']);
        } elseif (!empty($media['miniature'])) {
            return $media['miniature'];
        }
        if ($media['type'] === 'image' && !empty($media['fichier'])) {
            return base_url($media['fichier']);
        }
        if ($media['type'] === 'video' && !empty($media['fichier'])) {
            $thumb_path = FCPATH . 'attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg';
            if (file_exists($thumb_path)) {
                return base_url('attachments/Video/Thumbnails/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_thumb.jpg');
            }
        }
        if ($media['type'] === 'audio' && !empty($media['fichier'])) {
            $cover_path = FCPATH . 'attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg';
            if (file_exists($cover_path)) {
                return base_url('attachments/Audio/Covers/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_cover.jpg');
            }
        }
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
        if ($media['type'] !== 'audio') return null;
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
        $lang = $this->current_lang;
        $sql = "
            SELECT g.categorie_{$lang} as categorie, COUNT(*) as count 
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.categorie_{$lang} IS NOT NULL AND g.categorie_{$lang} != ''
            GROUP BY g.categorie_{$lang}
            ORDER BY count DESC
        ";
        return $this->db->query($sql)->result_array();
    }

    private function getRecommendedMedias($current_media, $user_id = null, $limit = 10)
    {
        $lang = $this->current_lang;
        $sql = "
            SELECT g.id_media, g.titre, g.slug, g.type, g.fichier, g.lien, g.miniature,
                   g.description_{$lang} AS description,
                   g.categorie_{$lang} AS categorie,
                   g.credits_{$lang} AS credits,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                   (SELECT COUNT(*) FROM media_favorites WHERE id_media = g.id_media AND user_id = ?) as is_favorite
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.id_media != ?
        ";
        $params = [$user_id, $current_media['id_media']];
        
        if (!empty($current_media['categorie'])) {
            $sql .= " AND g.categorie_{$lang} = ?";
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

    // ==================== DOWNLOAD ====================

    public function downloader($identifier = null)
    {
        if (empty($identifier)) {
            $identifier = $this->input->get('slug') ?? $this->input->get('id');
        }
        if (empty($identifier)) {
            log_message('error', 'Aucun identifiant fourni pour le téléchargement');
            show_404();
            return;
        }
        
        $user = $this->getCurrentUser();
        
        if (is_numeric($identifier)) {
            $media = $this->db->query("
                SELECT id_media, fichier, titre, type, sous_type, taille, slug
                FROM galerie_medias 
                WHERE id_media = ? AND est_actif = 1
            ", [$identifier])->row_array();
        } else {
            $media = $this->db->query("
                SELECT id_media, fichier, titre, type, sous_type, taille, slug
                FROM galerie_medias 
                WHERE slug = ? AND est_actif = 1
            ", [$identifier])->row_array();
        }
        
        if (!$media || empty($media['fichier'])) {
            log_message('error', 'Média non trouvé ou fichier vide: ' . $identifier);
            show_404();
            return;
        }
        
        $desired_extension = $this->getExtensionByType($media['type'], $media['sous_type']);
        $base = FCPATH;
        $file_path = '';
        $found = false;
        
        switch($media['type']) {
            case 'video':
                $possible_paths = [
                    $base . 'attachments/Video/Originals/' . $media['fichier'],
                    $base . 'attachments/Video/Encoded/' . $media['fichier'],
                    $base . 'attachments/Video/Originals/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '.mp4',
                    $base . 'attachments/Video/Encoded/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '.mp4',
                ];
                break;
            case 'audio':
                $base_name = pathinfo($media['fichier'], PATHINFO_FILENAME);
                $possible_paths = [
                    $base . 'attachments/Audio/Originals/' . $media['fichier'],
                    $base . 'attachments/Audio/Converted/' . $media['fichier'],
                    $base . 'attachments/Audio/Converted/' . $base_name . '_320k.mp3',
                    $base . 'attachments/Audio/Converted/' . $base_name . '_192k.mp3',
                    $base . 'attachments/Audio/Converted/' . $base_name . '_128k.mp3',
                    $base . 'attachments/Audio/Converted/' . $base_name . '_64k.mp3',
                    $base . 'attachments/Audio/Originals/' . $base_name . '.mp3',
                    $base . 'attachments/Audio/Originals/' . $base_name . '.m4a',
                    $base . 'attachments/Audio/Originals/' . $base_name . '.wav',
                ];
                break;
            case 'image':
                $possible_paths = [
                    $base . 'attachments/Images/' . $media['fichier'],
                    $base . 'attachments/Images/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '.jpg',
                    $base . 'attachments/Images/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '.jpeg',
                    $base . 'attachments/Images/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '.png',
                ];
                break;
            case 'document':
                $possible_paths = [$base . 'attachments/Documents/' . $media['fichier']];
                break;
            default:
                $possible_paths = [
                    $base . 'attachments/Autre/Files/' . $media['fichier'],
                    $base . 'attachments/Documents/' . $media['fichier'],
                    $base . $media['fichier'],
                ];
                break;
        }
        
        foreach ($possible_paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $file_path = $path;
                $found = true;
                break;
            }
        }
        
        if (!$found && in_array($media['type'], ['audio', 'video'])) {
            $base_name = pathinfo($media['fichier'], PATHINFO_FILENAME);
            $patterns = ($media['type'] === 'audio') 
                ? [$base . 'attachments/Audio/Originals/' . $base_name . '.*', $base . 'attachments/Audio/Converted/' . $base_name . '*']
                : [$base . 'attachments/Video/Originals/' . $base_name . '.*', $base . 'attachments/Video/Encoded/' . $base_name . '.*'];
            foreach ($patterns as $pattern) {
                $files = glob($pattern);
                if (!empty($files)) {
                    $file_path = $files[0];
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found && strpos($media['fichier'], 'attachments/') === 0) {
            $direct_path = $base . $media['fichier'];
            if (file_exists($direct_path)) {
                $file_path = $direct_path;
                $found = true;
            }
        }
        
        if (!$found) {
            log_message('error', 'Fichier physique non trouvé pour: ' . $media['fichier']);
            show_404();
            return;
        }
        
        $this->db->insert('media_downloads', [
            'id_media' => $media['id_media'],
            'user_id' => $user ? $user['id'] : null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'downloaded_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->query("UPDATE galerie_medias SET telechargements = telechargements + 1 WHERE id_media = ?", [$media['id_media']]);
        
        $original_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $base_filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $media['titre']);
        if ($desired_extension && $desired_extension !== $original_extension && $media['type'] === 'audio' && $original_extension !== 'mp3') {
            $mp3_converted = dirname($file_path) . '/../Converted/' . pathinfo($media['fichier'], PATHINFO_FILENAME) . '_320k.mp3';
            if (file_exists($mp3_converted)) {
                $file_path = $mp3_converted;
                $original_extension = 'mp3';
            }
        }
        $final_filename = $base_filename . '.' . $original_extension;
        $this->forceDownload($file_path, $final_filename);
    }

    private function getExtensionByType($type, $sous_type = null)
    {
        switch ($type) {
            case 'audio': return 'mp3';
            case 'video': return 'mp4';
            case 'image': return 'jpg';
            case 'document': return 'pdf';
            default: return null;
        }
    }

    private function forceDownload($file_path, $filename)
    {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            show_404();
            return;
        }
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_types = [
            'mp3' => 'audio/mpeg', 'mp4' => 'video/mp4', 'm4a' => 'audio/mp4',
            'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'webm' => 'audio/webm',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
            'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip'
        ];
        $mime_type = $mime_types[$extension] ?? mime_content_type($file_path) ?? 'application/octet-stream';
        $filename = str_replace('"', '', $filename);
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Accept-Ranges: bytes');
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->rangeDownload($file_path);
        } else {
            if (ob_get_level()) ob_end_clean();
            $handle = fopen($file_path, 'rb');
            if (!$handle) show_404();
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }
        exit;
    }

    private function rangeDownload($file_path)
    {
        $file_size = filesize($file_path);
        $fp = @fopen($file_path, 'rb');
        if (!$fp) {
            header('HTTP/1.1 500 Internal Server Error');
            exit;
        }
        $start = 0;
        $end = $file_size - 1;
        header('Accept-Ranges: bytes');
        if (isset($_SERVER['HTTP_RANGE'])) {
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$file_size");
                fclose($fp);
                exit;
            }
            if ($range == '-') {
                $c_start = $file_size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = intval($range[0]);
                $c_end = isset($range[1]) && is_numeric($range[1]) ? intval($range[1]) : $file_size - 1;
            }
            $c_end = min($c_end, $file_size - 1);
            if ($c_start > $c_end || $c_start < 0 || $c_end >= $file_size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$file_size");
                fclose($fp);
                exit;
            }
            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }
        $length = $end - $start + 1;
        header("Content-Range: bytes $start-$end/$file_size");
        header("Content-Length: $length");
        if (ob_get_level()) ob_end_clean();
        $buffer_size = 8192;
        $bytes_sent = 0;
        while (!feof($fp) && $bytes_sent < $length) {
            $buffer = fread($fp, min($buffer_size, $length - $bytes_sent));
            if ($buffer === false) break;
            echo $buffer;
            flush();
            $bytes_sent += strlen($buffer);
        }
        fclose($fp);
    }
}