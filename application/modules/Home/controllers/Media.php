<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Media Controller - Interface Visiteur
 * Interface moderne inspirée de YouTube & Spotify
 */
class Media extends Public_Controller {

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
    }

    /**
     * Page d'accueil - Découverte des médias
     */
    public function index()
    {
        // Récupérer les médias actifs
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
        ")->result_array();
        
        // Formater les données
        $medias = $this->formatMedias($medias);
        
        // Récupérer les catégories
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'medias' => $medias,
            'categories' => $categories,
            'current_type' => null,
            'search_query' => null,
            'results_count' => count($medias)
        ];
        
        $this->load->view('Media_View', $data);
    }

    /**
     * Vue filtrée par type de média
     */
    public function type($type)
    {
        $valid_types = ['video', 'audio', 'image', 'document', 'link', 'autre'];
        
        if (!in_array($type, $valid_types)) {
            show_404();
            return;
        }
        
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
            WHERE g.est_actif = 1 AND g.type = ?
            ORDER BY g.created_at DESC
        ", [$type])->result_array();
        
        $medias = $this->formatMedias($medias);
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'medias' => $medias,
            'categories' => $categories,
            'current_type' => $type,
            'search_query' => null,
            'results_count' => count($medias)
        ];
        
        $this->load->view('Media_View', $data);
    }

    /**
     * Vue filtrée par catégorie
     */
    public function category($category)
    {
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
            WHERE g.est_actif = 1 AND g.categorie = ?
            ORDER BY g.created_at DESC
        ", [$category])->result_array();
        
        $medias = $this->formatMedias($medias);
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'medias' => $medias,
            'categories' => $categories,
            'current_type' => null,
            'current_category' => $category,
            'search_query' => null,
            'results_count' => count($medias)
        ];
        
        $this->load->view('Media_View', $data);
    }

    /**
     * Recherche de médias
     */
    public function search()
    {
        $query = $this->input->get('q');
        
        if (empty($query)) {
            redirect('media');
            return;
        }
        
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
            AND (g.titre LIKE ? OR g.description LIKE ? OR g.credits LIKE ?)
            ORDER BY g.created_at DESC
        ", ["%$query%", "%$query%", "%$query%"])->result_array();
        
        $medias = $this->formatMedias($medias);
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'medias' => $medias,
            'categories' => $categories,
            'current_type' => null,
            'search_query' => $query,
            'results_count' => count($medias)
        ];
        
        $this->load->view('Media_View', $data);
    }

    /**
     * Détail d'un média - Supporte à la fois l'ID et le slug
     */
    public function detail($identifier)
    {
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
                       (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
                FROM galerie_medias g
                WHERE g.id_media = ? AND g.est_actif = 1
            ", [$identifier])->row_array();
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
                       (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
                FROM galerie_medias g
                WHERE g.slug = ? AND g.est_actif = 1
            ", [$identifier])->row_array();
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
        $this->recordView($media['id_media']);
        
        // Récupérer les commentaires
        $comments = $this->db->query("
            SELECT * FROM media_comments 
            WHERE id_media = ? AND is_approved = 1 
            ORDER BY created_at DESC 
            LIMIT 50
        ", [$media['id_media']])->result_array();
        
        foreach ($comments as &$comment) {
            $comment['created_at_formatted'] = date('d/m/Y H:i', strtotime($comment['created_at']));
        }
        
        // Récupérer les médias recommandés
        $recommended = $this->getRecommendedMedias($media);
        $recommended = $this->formatMedias($recommended);
        
        // Récupérer les catégories
        $categories = $this->getCategoriesWithCount();
        
        $data = [
            'media' => $media,
            'comments' => $comments,
            'recommended' => $recommended,
            'categories' => $categories
        ];
        
        $this->load->view('Media_Detail_View', $data);
    }

    /**
     * Générer un slug unique pour un média
     */
    public function generateSlug($title, $id = null)
    {
        // Nettoyer le titre
        $slug = strtolower(trim($title));
        if (empty($slug)) {
            $slug = 'media';
        }
        
        // Remplacer les caractères spéciaux
        $replacements = [
            ' ' => '-',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
            'œ' => 'oe',
            '/' => '-',
            '\\' => '-',
            '&' => 'et',
            "'" => '-',
            '"' => '-',
            '?' => '',
            '!' => '',
            '.' => '-',
            ',' => '-',
            ';' => '-',
            ':' => '-'
        ];
        
        foreach ($replacements as $search => $replace) {
            $slug = str_replace($search, $replace, $slug);
        }
        
        // Supprimer les caractères non alphanumériques restants
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // Supprimer les tirets multiples
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Supprimer les tirets au début et à la fin
        $slug = trim($slug, '-');
        
        // Ajouter l'ID pour garantir l'unicité
        if ($id) {
            $slug = $slug . '-' . $id;
        }
        
        return $slug;
    }

    /**
     * Mettre à jour tous les slugs existants
     */
    public function updateAllSlugs()
    {
        // Vérifier si l'utilisateur est admin (à adapter selon votre système)
        if (!$this->session->userdata('is_admin')) {
            show_404();
            return;
        }
        
        $medias = $this->db->query("
            SELECT id_media, titre FROM galerie_medias 
            WHERE est_actif = 1
        ")->result_array();
        
        $updated = 0;
        foreach ($medias as $media) {
            $slug = $this->generateSlug($media['titre'], $media['id_media']);
            
            $this->db->where('id_media', $media['id_media']);
            $this->db->update('galerie_medias', ['slug' => $slug]);
            $updated++;
        }
        
        echo "{$updated} slugs mis à jour avec succès.";
    }

    // ==================== API ENDPOINTS ====================

    /**
     * API: Récupérer un média (par ID ou slug)
     */
    public function apiGetMedia($identifier)
    {
        if (is_numeric($identifier)) {
            $media = $this->db->query("
                SELECT g.*,
                       (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                       (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                       (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                       (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                       (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                       (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                       (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
                FROM galerie_medias g
                WHERE g.id_media = ? AND g.est_actif = 1
            ", [$identifier])->row_array();
        } else {
            $media = $this->db->query("
                SELECT g.*,
                       (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                       (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count,
                       (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'dislike') as dislikes_count,
                       (SELECT COUNT(*) FROM media_plays WHERE id_media = g.id_media) as plays_count,
                       (SELECT COUNT(*) FROM media_comments WHERE id_media = g.id_media AND is_approved = 1) as comments_count,
                       (SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media) as rating_avg,
                       (SELECT COUNT(*) FROM media_ratings WHERE id_media = g.id_media) as total_ratings
                FROM galerie_medias g
                WHERE g.slug = ? AND g.est_actif = 1
            ", [$identifier])->row_array();
        }
        
        if (!$media) {
            echo json_encode(['success' => false, 'message' => 'Média non trouvé']);
            return;
        }
        
        $media = $this->formatMedia($media);
        
        echo json_encode(['success' => true, 'media' => $media]);
    }

    /**
     * API: Enregistrer une vue
     */
    public function apiTrackView()
    {
        $id_media = $this->input->post('id_media');
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        $user_agent = $this->input->user_agent();
        
        // Vérifier si déjà vu dans cette session
        $viewed = $this->db->query("
            SELECT id FROM media_views 
            WHERE id_media = ? AND session_id = ? 
            LIMIT 1
        ", [$id_media, $session_id])->num_rows();
        
        if (!$viewed) {
            $this->db->insert('media_views', [
                'id_media' => $id_media,
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
     * API: Enregistrer une lecture
     */
    public function apiTrackPlay()
    {
        $id_media = $this->input->post('id_media');
        $duration_played = (int)$this->input->post('duration_played');
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        
        $this->db->insert('media_plays', [
            'id_media' => $id_media,
            'ip_address' => $ip_address,
            'session_id' => $session_id,
            'duration_played' => $duration_played,
            'played_at' => date('Y-m-d H:i:s')
        ]);
        
        $plays = $this->db->query("
            SELECT COUNT(*) as count FROM media_plays WHERE id_media = ?
        ", [$id_media])->row()->count;
        
        echo json_encode(['success' => true, 'plays' => $plays]);
    }

    /**
     * API: Like/Dislike
     */
    public function apiToggleLike()
    {
        $id_media = $this->input->post('id_media');
        $action = $this->input->post('action');
        $ip_address = $this->input->ip_address();
        
        $existing = $this->db->query("
            SELECT id, action FROM media_likes 
            WHERE id_media = ? AND ip_address = ? 
            LIMIT 1
        ", [$id_media, $ip_address])->row();
        
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
                    'ip_address' => $ip_address,
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
        $ip_address = $this->input->ip_address();
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Note invalide']);
            return;
        }
        
        $existing = $this->db->query("
            SELECT id FROM media_ratings 
            WHERE id_media = ? AND ip_address = ? 
            LIMIT 1
        ", [$id_media, $ip_address])->row();
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update('media_ratings', ['rating' => $rating]);
        } else {
            $this->db->insert('media_ratings', [
                'id_media' => $id_media,
                'ip_address' => $ip_address,
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

    /**
     * API: Ajouter un commentaire
     */
    public function apiAddComment()
    {
        $id_media = $this->input->post('id_media');
        $comment = trim($this->input->post('comment'));
        $author_name = trim($this->input->post('author_name')) ?: 'Visiteur';
        $ip_address = $this->input->ip_address();
        
        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
            return;
        }
        
        if (strlen($comment) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 1000 caractères)']);
            return;
        }
        
        $this->db->insert('media_comments', [
            'id_media' => $id_media,
            'author_name' => $author_name,
            'comment' => $comment,
            'ip_address' => $ip_address,
            'created_at' => date('Y-m-d H:i:s'),
            'is_approved' => 1
        ]);
        
        $comment_id = $this->db->insert_id();
        $new_comment = $this->db->query("
            SELECT * FROM media_comments WHERE id = ?
        ", [$comment_id])->row_array();
        
        $new_comment['created_at_formatted'] = date('d/m/Y H:i', strtotime($new_comment['created_at']));
        
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
            SELECT * FROM media_comments 
            WHERE id_media = ? AND is_approved = 1 
            ORDER BY created_at DESC 
            LIMIT 50
        ", [$id_media])->result_array();
        
        foreach ($comments as &$comment) {
            $comment['created_at_formatted'] = date('d/m/Y H:i', strtotime($comment['created_at']));
        }
        
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);
    }

    /**
     * API: Récupérer les favoris
     */
    public function apiGetFavorites()
    {
        $favorites = $this->input->cookie('favorites') ? json_decode($this->input->cookie('favorites'), true) : [];
        
        if (empty($favorites)) {
            echo json_encode(['success' => true, 'medias' => []]);
            return;
        }
        
        $ids = implode(',', array_fill(0, count($favorites), '?'));
        $medias = $this->db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count
            FROM galerie_medias g
            WHERE g.id_media IN ($ids) AND g.est_actif = 1
        ", $favorites)->result_array();
        
        $medias = $this->formatMedias($medias);
        
        echo json_encode([
            'success' => true,
            'medias' => $medias
        ]);
    }

    /**
     * API: Ajouter/retirer des favoris
     */
    public function apiToggleFavorite()
    {
        $id_media = $this->input->post('id_media');
        $favorites = $this->input->cookie('favorites') ? json_decode($this->input->cookie('favorites'), true) : [];
        
        $key = array_search($id_media, $favorites);
        
        if ($key !== false) {
            unset($favorites[$key]);
            $favorites = array_values($favorites);
            $is_favorite = false;
        } else {
            $favorites[] = $id_media;
            $is_favorite = true;
        }
        
        $this->input->set_cookie('favorites', json_encode($favorites), 60 * 60 * 24 * 30);
        
        echo json_encode([
            'success' => true,
            'is_favorite' => $is_favorite,
            'favorites_count' => count($favorites)
        ]);
    }

    /**
     * API: Partager un média
     */
    public function apiShare()
    {
        $id_media = $this->input->post('id_media');
        $platform = $this->input->post('platform');
        
        // Récupérer le média pour obtenir son slug
        $media = $this->db->query("
            SELECT titre, slug FROM galerie_medias WHERE id_media = ?
        ", [$id_media])->row_array();
        
        if (!$media) {
            echo json_encode(['success' => false]);
            return;
        }
        
        // Utiliser le slug si disponible, sinon l'ID
        $slug = !empty($media['slug']) ? $media['slug'] : $id_media;
        $url = base_url("media/detail/$slug");
        $title = urlencode($media['titre']);
        
        $share_urls = [
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
            'twitter' => "https://twitter.com/intent/tweet?text=$title&url=" . urlencode($url),
            'whatsapp' => "https://wa.me/?text=$title%20" . urlencode($url),
            'linkedin' => "https://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($url) . "&title=$title",
            'telegram' => "https://t.me/share/url?url=" . urlencode($url) . "&text=$title",
            'email' => "mailto:?subject=$title&body=" . urlencode($url)
        ];
        
        $share_url = $share_urls[$platform] ?? $url;
        
        echo json_encode([
            'success' => true,
            'share_url' => $share_url,
            'url' => $url
        ]);
    }

    /**
     * API: Recherche AJAX
     */
    public function apiSearch()
    {
        $query = trim($this->input->get('q'));
        $limit = (int)$this->input->get('limit') ?: 10;
        
        if (empty($query)) {
            echo json_encode(['success' => true, 'medias' => []]);
            return;
        }
        
        $medias = $this->db->query("
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count
            FROM galerie_medias g
            WHERE g.est_actif = 1 
            AND (g.titre LIKE ? OR g.description LIKE ?)
            ORDER BY g.created_at DESC
            LIMIT ?
        ", ["%$query%", "%$query%", $limit])->result_array();
        
        $medias = $this->formatMedias($medias);
        
        echo json_encode([
            'success' => true,
            'medias' => $medias
        ]);
    }

    // ==================== PRIVATE METHODS ====================

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

    private function getRecommendedMedias($current_media, $limit = 10)
    {
        $sql = "
            SELECT g.*,
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as views_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as likes_count
            FROM galerie_medias g
            WHERE g.est_actif = 1 AND g.id_media != ?
        ";
        
        $params = [$current_media['id_media']];
        
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
}