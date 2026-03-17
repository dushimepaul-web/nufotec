<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media extends Public_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('cookie');
    }

    public function index()
    {
        // Récupérer tous les médias actifs
        $medias = $this->Model->read('galerie_medias', ['est_actif' => 1], 'created_at', 'DESC');
        
        // Préparer les données avec statistiques
        $data['medias'] = [];
        foreach ($medias as $media) {
            // S'assurer que $media est un tableau
            $media_array = (array)$media;
            
            // Récupérer les statistiques depuis les tables relationnelles
            $media_array['views_count'] = $this->getViewsCount($media_array['id_media']);
            $media_array['likes_count'] = $this->getLikesCount($media_array['id_media'], 'like');
            $media_array['dislikes_count'] = $this->getLikesCount($media_array['id_media'], 'dislike');
            $media_array['plays_count'] = $this->getPlaysCount($media_array['id_media']);
            $media_array['comments_count'] = $this->getCommentsCount($media_array['id_media']);
            $media_array['rating_avg'] = $this->getAverageRating($media_array['id_media']);
            $media_array['total_ratings'] = $this->getRatingsCount($media_array['id_media']);
            
            // YouTube ID si lien
            $media_array['youtube_id'] = $this->getYoutubeId($media_array['lien'] ?? '');
            
            // Durée formatée
            $media_array['duration'] = $this->formatDuration($media_array['duree'] ?? 0);
            
            $data['medias'][] = $media_array;
        }
        
        // Récupérer les catégories pour le filtre
        $data['categories'] = $this->getUniqueCategories();
        
        // Récupérer les médias populaires
        $data['popular_medias'] = $this->getPopularMedias(5);
        
        // Récupérer les derniers commentaires
        $data['recent_comments'] = $this->getRecentComments(5);
        
        $this->load->view('Media_View', $data);
    }
    
    /**
     * API: Enregistrer une vue
     */
    public function trackView()
    {
        $id_media = $this->input->post('id_media');
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        $user_agent = $this->input->user_agent();
        
        // Vérifier si l'utilisateur a déjà vu ce média dans cette session
        $viewed = $this->db->where('id_media', $id_media)
                           ->where('session_id', $session_id)
                           ->get('media_views')
                           ->row();
        
        if (!$viewed) {
            // Nouvelle vue
            $this->db->insert('media_views', [
                'id_media' => $id_media,
                'session_id' => $session_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Retourner le nombre total de vues
        $views = $this->getViewsCount($id_media);
        echo json_encode(['success' => true, 'views' => $views]);
    }
    
    /**
     * API: Gérer les likes/dislikes
     */
    public function toggleLike()
    {
        $id_media = $this->input->post('id_media');
        $action = $this->input->post('action'); // like, dislike, ou remove
        $ip_address = $this->input->ip_address();
        
        // Vérifier si l'utilisateur a déjà interagi
        $existing = $this->db->where('id_media', $id_media)
                             ->where('ip_address', $ip_address)
                             ->get('media_likes')
                             ->row();
        
        if ($action === 'remove') {
            // Supprimer le like/dislike
            if ($existing) {
                $this->db->where('id', $existing->id)->delete('media_likes');
            }
        } else {
            if ($existing) {
                if ($existing->action === $action) {
                    // Même action -> on supprime (toggle off)
                    $this->db->where('id', $existing->id)->delete('media_likes');
                } else {
                    // Changement d'action
                    $this->db->where('id', $existing->id)
                             ->update('media_likes', ['action' => $action]);
                }
            } else {
                // Nouveau like/dislike
                $this->db->insert('media_likes', [
                    'id_media' => $id_media,
                    'ip_address' => $ip_address,
                    'action' => $action,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Récupérer les nouveaux compteurs
        $likes = $this->getLikesCount($id_media, 'like');
        $dislikes = $this->getLikesCount($id_media, 'dislike');
        
        echo json_encode([
            'success' => true,
            'likes' => $likes,
            'dislikes' => $dislikes
        ]);
    }
    
    /**
     * API: Enregistrer une lecture (play)
     */
    public function trackPlay()
    {
        $id_media = $this->input->post('id_media');
        $duration_played = $this->input->post('duration_played') ?: 0;
        $session_id = session_id();
        $ip_address = $this->input->ip_address();
        
        $this->db->insert('media_plays', [
            'id_media' => $id_media,
            'ip_address' => $ip_address,
            'session_id' => $session_id,
            'duration_played' => $duration_played,
            'played_at' => date('Y-m-d H:i:s')
        ]);
        
        $plays = $this->getPlaysCount($id_media);
        
        echo json_encode([
            'success' => true,
            'plays' => $plays
        ]);
    }
    
    /**
     * API: Ajouter un commentaire
     */
    public function addComment()
    {
        $id_media = $this->input->post('id_media');
        $comment = $this->input->post('comment');
        $author_name = $this->input->post('author_name') ?: 'Anonyme';
        $ip_address = $this->input->ip_address();
        
        // Validation
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
        
        // Récupérer le commentaire ajouté
        $comment_id = $this->db->insert_id();
        $new_comment = $this->db->where('id', $comment_id)->get('media_comments')->row();
        
        // Formater la date
        $new_comment->created_at_formatted = date('d/m/Y H:i', strtotime($new_comment->created_at));
        
        echo json_encode([
            'success' => true,
            'comment' => $new_comment,
            'comments_count' => $this->getCommentsCount($id_media)
        ]);
    }
    
    /**
     * API: Noter un média (étoiles)
     */
    public function rateMedia()
    {
        $id_media = $this->input->post('id_media');
        $rating = (int)$this->input->post('rating');
        $ip_address = $this->input->ip_address();
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Note invalide']);
            return;
        }
        
        // Vérifier si l'utilisateur a déjà noté
        $existing = $this->db->where('id_media', $id_media)
                             ->where('ip_address', $ip_address)
                             ->get('media_ratings')
                             ->row();
        
        if ($existing) {
            $this->db->where('id', $existing->id)
                     ->update('media_ratings', ['rating' => $rating]);
        } else {
            $this->db->insert('media_ratings', [
                'id_media' => $id_media,
                'ip_address' => $ip_address,
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Nouvelle moyenne
        $avg = $this->getAverageRating($id_media);
        $total = $this->getRatingsCount($id_media);
        
        echo json_encode([
            'success' => true,
            'average' => round($avg, 1),
            'total' => $total
        ]);
    }
    
    /**
     * API: Récupérer les commentaires d'un média
     */
    public function getComments($id_media)
    {
        $comments = $this->db->where('id_media', $id_media)
                             ->where('is_approved', 1)
                             ->order_by('created_at', 'DESC')
                             ->limit(50)
                             ->get('media_comments')
                             ->result();
        
        // Formater les dates
        foreach ($comments as $comment) {
            $comment->created_at_formatted = date('d/m/Y H:i', strtotime($comment->created_at));
        }
        
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);
    }
    
    /**
     * API: Recherche en temps réel
     */
    public function search()
    {
        $query = $this->input->get('q');
        $type = $this->input->get('type');
        $category = $this->input->get('category');
        $sort = $this->input->get('sort') ?: 'recent';
        $limit = (int)$this->input->get('limit') ?: 50;
        
        $this->db->select('g.*');
        $this->db->from('galerie_medias g');
        $this->db->where('g.est_actif', 1);
        
        // Recherche par titre, description ou crédits
        if (!empty($query)) {
            $this->db->group_start();
            $this->db->like('g.titre', $query);
            $this->db->or_like('g.description', $query);
            $this->db->or_like('g.credits', $query);
            $this->db->group_end();
        }
        
        // Filtre par type
        if (!empty($type) && $type != 'all') {
            $this->db->where('g.type', $type);
        }
        
        // Filtre par catégorie
        if (!empty($category)) {
            $this->db->where('g.categorie', $category);
        }
        
        // Tri
        switch ($sort) {
            case 'views':
                $this->db->order_by('(SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media)', 'DESC');
                break;
            case 'likes':
                $this->db->order_by('(SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = "like")', 'DESC');
                break;
            case 'rating':
                $this->db->order_by('(SELECT AVG(rating) FROM media_ratings WHERE id_media = g.id_media)', 'DESC');
                break;
            case 'oldest':
                $this->db->order_by('g.created_at', 'ASC');
                break;
            case 'recent':
            default:
                $this->db->order_by('g.created_at', 'DESC');
                break;
        }
        
        $this->db->limit($limit);
        $medias = $this->db->get()->result_array();
        
        // Ajouter les statistiques
        foreach ($medias as &$media) {
            $media['views_count'] = $this->getViewsCount($media['id_media']);
            $media['likes_count'] = $this->getLikesCount($media['id_media'], 'like');
            $media['dislikes_count'] = $this->getLikesCount($media['id_media'], 'dislike');
            $media['plays_count'] = $this->getPlaysCount($media['id_media']);
            $media['comments_count'] = $this->getCommentsCount($media['id_media']);
            $media['rating_avg'] = $this->getAverageRating($media['id_media']);
            $media['total_ratings'] = $this->getRatingsCount($media['id_media']);
            $media['youtube_id'] = $this->getYoutubeId($media['lien'] ?? '');
            $media['duration'] = $this->formatDuration($media['duree'] ?? 0);
            $media['thumbnail_url'] = $this->getThumbnailUrl($media);
        }
        
        echo json_encode([
            'success' => true,
            'medias' => $medias,
            'total' => count($medias)
        ]);
    }
    
    /**
     * API: Médias recommandés
     */
    public function getRecommended($id_media)
    {
        // Récupérer le média actuel
        $current = $this->Model->readOne('galerie_medias', ['id_media' => $id_media]);
        
        if (!$current) {
            echo json_encode(['success' => false, 'medias' => []]);
            return;
        }
        
        $current = (array)$current;
        
        // Recommandations basées sur la même catégorie ou type
        $this->db->select('g.*');
        $this->db->from('galerie_medias g');
        $this->db->where('g.est_actif', 1);
        $this->db->where('g.id_media !=', $id_media);
        
        $this->db->group_start();
        if (!empty($current['categorie'])) {
            $this->db->where('g.categorie', $current['categorie']);
        }
        $this->db->or_where('g.type', $current['type']);
        $this->db->group_end();
        
        $this->db->order_by('(SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media)', 'DESC');
        $this->db->limit(10);
        
        $medias = $this->db->get()->result_array();
        
        // Ajouter les statistiques
        foreach ($medias as &$media) {
            $media['views_count'] = $this->getViewsCount($media['id_media']);
            $media['likes_count'] = $this->getLikesCount($media['id_media'], 'like');
            $media['thumbnail_url'] = $this->getThumbnailUrl($media);
            $media['youtube_id'] = $this->getYoutubeId($media['lien'] ?? '');
            $media['duration'] = $this->formatDuration($media['duree'] ?? 0);
        }
        
        echo json_encode([
            'success' => true,
            'medias' => $medias
        ]);
    }
    
    /**
     * API: Récupérer les catégories
     */
    public function getCategories()
    {
        $categories = $this->getUniqueCategories();
        echo json_encode(['success' => true, 'categories' => $categories]);
    }
    
    /**
     * API: Statistiques globales
     */
    public function getStats()
    {
        $total_views = $this->db->count_all_results('media_views');
        $total_likes = $this->db->where('action', 'like')->count_all_results('media_likes');
        $total_plays = $this->db->count_all_results('media_plays');
        $total_comments = $this->db->where('is_approved', 1)->count_all_results('media_comments');
        $total_medias = $this->db->where('est_actif', 1)->count_all_results('galerie_medias');
        
        // Répartition par type
        $types = $this->db->select('type, COUNT(*) as count')
                          ->where('est_actif', 1)
                          ->group_by('type')
                          ->get('galerie_medias')
                          ->result();
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_views' => $total_views,
                'total_likes' => $total_likes,
                'total_plays' => $total_plays,
                'total_comments' => $total_comments,
                'total_medias' => $total_medias,
                'by_type' => $types
            ]
        ]);
    }
    
    // ==================== HELPERS PRIVÉS ====================
    
    /**
     * Récupérer le nombre de vues
     */
    private function getViewsCount($id_media)
    {
        return (int)$this->db->where('id_media', $id_media)
                             ->count_all_results('media_views');
    }
    
    /**
     * Récupérer le nombre de likes/dislikes
     */
    private function getLikesCount($id_media, $action = 'like')
    {
        return (int)$this->db->where('id_media', $id_media)
                             ->where('action', $action)
                             ->count_all_results('media_likes');
    }
    
    /**
     * Récupérer le nombre de lectures
     */
    private function getPlaysCount($id_media)
    {
        return (int)$this->db->where('id_media', $id_media)
                             ->count_all_results('media_plays');
    }
    
    /**
     * Récupérer le nombre de commentaires
     */
    private function getCommentsCount($id_media)
    {
        return (int)$this->db->where('id_media', $id_media)
                             ->where('is_approved', 1)
                             ->count_all_results('media_comments');
    }
    
    /**
     * Récupérer la note moyenne
     */
    private function getAverageRating($id_media)
    {
        $result = $this->db->select_avg('rating')
                           ->where('id_media', $id_media)
                           ->get('media_ratings')
                           ->row();
        return $result->rating ? round($result->rating, 1) : 0;
    }
    
    /**
     * Récupérer le nombre de notations
     */
    private function getRatingsCount($id_media)
    {
        return (int)$this->db->where('id_media', $id_media)
                             ->count_all_results('media_ratings');
    }
    
    /**
     * Récupérer les catégories uniques
     */
    private function getUniqueCategories()
    {
        return $this->db->distinct()
                        ->select('categorie')
                        ->where('categorie IS NOT NULL')
                        ->where('categorie !=', '')
                        ->get('galerie_medias')
                        ->result();
    }
    
    /**
     * Récupérer les médias populaires
     */
    private function getPopularMedias($limit = 5)
    {
        return $this->db->query("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM media_views WHERE id_media = g.id_media) as view_count,
                   (SELECT COUNT(*) FROM media_likes WHERE id_media = g.id_media AND action = 'like') as like_count
            FROM galerie_medias g
            WHERE g.est_actif = 1
            ORDER BY view_count DESC, like_count DESC
            LIMIT ?
        ", [$limit])->result_array();
    }
    
    /**
     * Récupérer les commentaires récents
     */
    private function getRecentComments($limit = 5)
    {
        return $this->db->select('c.*, g.titre as media_title, g.type as media_type, g.miniature as media_thumbnail')
                        ->from('media_comments c')
                        ->join('galerie_medias g', 'g.id_media = c.id_media')
                        ->where('c.is_approved', 1)
                        ->order_by('c.created_at', 'DESC')
                        ->limit($limit)
                        ->get()
                        ->result();
    }
    
    /**
     * Extraire l'ID YouTube d'une URL
     */
    private function getYoutubeId($url)
    {
        if (empty($url)) return null;
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
        return $matches[1] ?? null;
    }
    
    /**
     * Obtenir l'URL de la miniature
     */
    private function getThumbnailUrl($media)
    {
        if (!empty($media['youtube_id'])) {
            return "https://img.youtube.com/vi/{$media['youtube_id']}/hqdefault.jpg";
        } elseif (!empty($media['miniature'])) {
            return base_url($media['miniature']);
        } elseif ($media['type'] === 'image' && !empty($media['fichier'])) {
            return base_url($media['fichier']);
        } else {
            return base_url('assets/images/default_thumbnail.jpg');
        }
    }
    
    /**
     * Formater la durée
     */
    private function formatDuration($seconds)
    {
        if (!$seconds || $seconds <= 0) return '00:00';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%02d:%02d', $minutes, $secs);
    }
    
    /**
     * Vérifier si l'utilisateur a déjà liké
     */
    public function checkUserLike($id_media)
    {
        $ip_address = $this->input->ip_address();
        $like = $this->db->where('id_media', $id_media)
                         ->where('ip_address', $ip_address)
                         ->get('media_likes')
                         ->row();
        
        echo json_encode([
            'success' => true,
            'liked' => $like ? $like->action : null
        ]);
    }
}