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
     * Récupérer tous les médias (vidéos + audios)
     */
    public function medias()
    {
        $type = $this->input->get('type'); // video, audio, all
        $limit = (int)($this->input->get('limit') ?? 50);
        $offset = (int)($this->input->get('offset') ?? 0);
        $category = $this->input->get('category');
        $lang = $this->getCurrentLang();
        
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
        ";
        
        $params = [];
        
        if ($type === 'video') {
            $sql .= " AND (g.type = 'video' OR (g.type = 'link' AND g.lien IS NOT NULL AND (g.lien LIKE '%youtube%' OR g.lien LIKE '%youtu.be%')))";
        } elseif ($type === 'audio') {
            $sql .= " AND g.type = 'audio'";
        }
        
        if (!empty($category)) {
            $sql .= " AND g.categorie_{$lang} = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY g.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $medias = $this->db->query($sql, $params)->result_array();
        
        // Compter total
        $total = $this->db->query("
            SELECT COUNT(*) as total FROM galerie_medias g 
            WHERE g.est_actif = 1
            " . ($type === 'video' ? "AND (g.type = 'video' OR g.type = 'link')" : ($type === 'audio' ? "AND g.type = 'audio'" : "")) . "
        ")->row()->total ?? 0;
        
        // Formater les médias
        foreach ($medias as &$media) {
            $media = $this->formatMediaForMobile($media);
        }
        
        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $medias,
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
}
?>