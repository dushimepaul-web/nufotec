<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur de gestion des vidéos avec upload chunked ultra-robuste
 * Inspiré des architectures de YouTube, Vimeo et AWS S3
 */
class Video extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $chunk_size;
    private $max_file_size;
    private $allowed_extensions;
    private $session_timeout;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Configuration des chemins
        $this->upload_dir = FCPATH . 'uploads/temp/';
        $this->final_dir = FCPATH . 'attachments/Galerie/';
        
        // Configuration technique (2MB par chunk pour compatibilité maximale)
        $this->chunk_size = 2 * 1024 * 1024; // 2MB
        $this->max_file_size = 10 * 1024 * 1024 * 1024; // 10GB max
        $this->session_timeout = 3600; // 1 heure
        
        // Extensions vidéo supportées
        $this->allowed_extensions = [
            'mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 
            'flv', 'm4v', '3gp', 'wmv', 'ts', 'mts', 'm2ts'
        ];
        
        // Création des dossiers
        $this->ensureDirectoryExists($this->upload_dir);
        $this->ensureDirectoryExists($this->final_dir);
        
        // Configuration PHP dynamique
        $this->configurePHP();
        
        // Nettoyage des sessions expirées
        $this->cleanupExpiredSessions();
    }

    // ==================== CONFIGURATION ====================

    /**
     * Configure PHP pour gérer de gros uploads
     */
    private function configurePHP()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('upload_max_filesize', '10M');
        @ini_set('post_max_size', '10M');
        @ini_set('max_file_uploads', '20');
        
        // Désactiver le buffering pour les gros fichiers
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        
        @ini_set('zlib.output_compression', 'Off');
        
        // Augmenter le temps de session
        @ini_set('session.gc_maxlifetime', $this->session_timeout);
    }

    /**
     * Crée un dossier avec permissions correctes
     */
    private function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, 0777, TRUE);
            @chmod($path, 0777);
        }
    }

    /**
     * Nettoie les sessions d'upload expirées
     */
    private function cleanupExpiredSessions()
    {
        if (!is_dir($this->upload_dir)) return;
        
        $dirs = glob($this->upload_dir . 'upload_*', GLOB_ONLYDIR);
        $now = time();
        
        foreach ($dirs as $dir) {
            $metadata_file = $dir . '/metadata.json';
            if (!file_exists($metadata_file)) {
                $this->recursiveDelete($dir);
                continue;
            }
            
            $metadata = json_decode(file_get_contents($metadata_file), true);
            if (!$metadata || ($now - $metadata['created_at']) > $this->session_timeout) {
                $this->recursiveDelete($dir);
            }
        }
    }

    /**
     * Suppression récursive de dossier
     */
    private function recursiveDelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->recursiveDelete($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        @unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            @rmdir($dir);
        }
    }

    // ==================== INTERFACE PUBLIQUE ====================

    /**
     * Page principale - Liste des vidéos
     */
    public function index()
    {
        $data['videos'] = $this->Model->read('galerie_medias', 
            ['type' => 'video'], 
            'id_media', 
            'DESC'
        );
        
        // Récupérer les catégories existantes pour le datalist
        $data['categories'] = $this->getExistingCategories();
        
        $this->load->view('Video_View', $data);
    }

    /**
     * API: Diagnostic serveur
     */
    public function diagnostics()
    {
        header('Content-Type: application/json');
        
        $info = [
            'php_version' => PHP_VERSION,
            'limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'max_file_uploads' => ini_get('max_file_uploads'),
            ],
            'configured' => [
                'chunk_size' => $this->formatBytes($this->chunk_size),
                'max_file_size' => $this->formatBytes($this->max_file_size),
                'session_timeout' => $this->session_timeout . 's'
            ],
            'directories' => [
                'upload_dir' => $this->upload_dir,
                'upload_writable' => is_writable($this->upload_dir),
                'final_dir' => $this->final_dir,
                'final_writable' => is_writable($this->final_dir),
                'disk_free' => $this->formatBytes(@disk_free_space($this->final_dir))
            ],
            'extensions' => $this->allowed_extensions,
            'timestamp' => time()
        ];
        
        echo json_encode($info);
    }

    // ==================== API UPLOAD CHUNKED ====================

    /**
     * Étape 1: Initialiser une session d'upload
     */
    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash') ?: null; // Pour déduplication future

        // Validation
        $validation = $this->validateInitUpload($file_name, $file_size);
        if (!$validation['success']) {
            $this->jsonResponse(false, $validation['message']);
            return;
        }

        $upload_id = $this->generateUploadId();
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, TRUE)) {
            $this->jsonResponse(false, 'Erreur création dossier temporaire');
            return;
        }

        // Métadonnées de session
        $total_chunks = (int)ceil($file_size / $this->chunk_size);
        
        $metadata = [
            'upload_id' => $upload_id,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'file_hash' => $file_hash,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'failed_chunks' => [],
            'created_at' => time(),
            'last_activity' => time(),
            'status' => 'active'
        ];

        $this->saveMetadata($upload_id, $metadata);

        $this->jsonResponse(true, 'Session initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'max_retries' => 3
        ]);
    }

    /**
     * Étape 2: Recevoir un chunk
     */
    public function uploadChunk()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');
        $chunk_hash = $this->input->post('chunk_hash') ?: null; // Pour vérification d'intégrité

        // Validation basique
        if (empty($upload_id)) {
            $this->jsonResponse(false, 'ID upload manquant');
            return;
        }

        // Récupérer métadonnées
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) {
            $this->jsonResponse(false, 'Session invalide ou expirée');
            return;
        }

        // Vérifier status
        if ($metadata['status'] !== 'active') {
            $this->jsonResponse(false, 'Session non active: ' . $metadata['status']);
            return;
        }

        // Vérifier index
        if ($chunk_index < 0 || $chunk_index >= $metadata['total_chunks']) {
            $this->jsonResponse(false, 'Index chunk invalide');
            return;
        }

        // Vérifier si déjà présent (idempotence)
        $chunk_path = $this->getChunkPath($upload_id, $chunk_index);
        if (file_exists($chunk_path)) {
            $this->markChunkUploaded($upload_id, $chunk_index);
            $progress = $this->calculateProgress($upload_id);
            $this->jsonResponse(true, 'Chunk déjà présent', $progress);
            return;
        }

        // Traiter le fichier uploadé
        if (empty($_FILES['chunk'])) {
            $this->jsonResponse(false, 'Aucun fichier reçu');
            return;
        }

        $file = $_FILES['chunk'];
        
        // Gestion détaillée des erreurs PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_detail = $this->getDetailedUploadError($file['error']);
            $this->logError("Upload error for chunk $chunk_index", $error_detail);
            $this->jsonResponse(false, $error_detail['message'], [
                'error_code' => $file['error'],
                'error_type' => $error_detail['type']
            ]);
            return;
        }

        // Vérifier taille reçue
        if ($file['size'] === 0) {
            $this->jsonResponse(false, 'Chunk vide reçu');
            return;
        }

        // Vérifier hash si fourni (intégrité)
        if ($chunk_hash && function_exists('hash_file')) {
            $calculated_hash = hash_file('crc32b', $file['tmp_name']);
            if ($calculated_hash !== $chunk_hash) {
                $this->jsonResponse(false, 'Corruption détectée - hash mismatch');
                return;
            }
        }

        // Déplacer le chunk avec fallback
        if (!@move_uploaded_file($file['tmp_name'], $chunk_path)) {
            // Tentative avec copy
            if (!@copy($file['tmp_name'], $chunk_path)) {
                $this->jsonResponse(false, 'Erreur écriture disque - vérifiez les permissions');
                return;
            }
            @unlink($file['tmp_name']);
        }

        // Vérifier écriture
        if (!file_exists($chunk_path) || filesize($chunk_path) !== $file['size']) {
            @unlink($chunk_path);
            $this->jsonResponse(false, 'Erreur vérification écriture');
            return;
        }

        // Mettre à jour métadonnées
        $this->markChunkUploaded($upload_id, $chunk_index);
        $this->updateLastActivity($upload_id);
        
        $progress = $this->calculateProgress($upload_id);

        $this->jsonResponse(true, 'Chunk reçu', array_merge($progress, [
            'received_size' => $file['size'],
            'chunk_hash_verified' => (bool)$chunk_hash
        ]));
    }

    /**
     * Étape 3: Vérifier le statut d'un upload
     */
    public function checkStatus()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $metadata = $this->loadMetadata($upload_id);
        
        if (!$metadata) {
            $this->jsonResponse(false, 'Session non trouvée');
            return;
        }

        // Synchroniser avec fichiers réels
        $actual_chunks = $this->getActualUploadedChunks($upload_id);
        
        if ($actual_chunks !== $metadata['uploaded_chunks']) {
            $metadata['uploaded_chunks'] = $actual_chunks;
            $this->saveMetadata($upload_id, $metadata);
        }

        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $actual_chunks
        );

        $this->jsonResponse(true, 'Statut récupéré', [
            'upload_id' => $upload_id,
            'status' => $metadata['status'],
            'progress' => $this->calculateProgress($upload_id),
            'missing_chunks' => array_values($missing),
            'can_resume' => count($actual_chunks) > 0 && count($missing) > 0
        ]);
    }

    /**
     * Étape 4: Finaliser l'upload
     */
    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $metadata = $this->loadMetadata($upload_id);
        
        if (!$metadata) {
            $this->jsonResponse(false, 'Session non trouvée');
            return;
        }

        // Vérifier tous les chunks
        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $metadata['uploaded_chunks']
        );

        if (!empty($missing)) {
            $this->jsonResponse(false, 'Chunks manquants', [
                'missing_chunks' => array_values($missing),
                'total_missing' => count($missing)
            ]);
            return;
        }

        // Générer nom final unique
        $final_name = $this->generateFinalName($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Galerie/' . $final_name;

        // Assembler les chunks avec vérification d'intégrité
        $assembled = $this->assembleChunks($upload_id, $final_path, $metadata);
        
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Vérifier taille finale
        $final_size = filesize($final_path);
        if ($final_size !== $metadata['file_size']) {
            @unlink($final_path);
            $this->jsonResponse(false, 'Taille finale incorrecte', [
                'expected' => $metadata['file_size'],
                'received' => $final_size
            ]);
            return;
        }

        // Générer miniature
        $thumbnail = $this->generateVideoThumbnail($relative_path);

        // Nettoyer session
        $this->cleanupUploadSession($upload_id);

        // Mettre à jour statut
        $metadata['status'] = 'completed';
        $metadata['completed_at'] = time();
        $metadata['final_path'] = $relative_path;

        $this->jsonResponse(true, 'Upload complété', [
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => $final_size,
            'file_size_formatted' => $this->formatBytes($final_size),
            'thumbnail' => $thumbnail,
            'mime_type' => mime_content_type($final_path),
            'assembly_time_ms' => $assembled['time_ms']
        ]);
    }

    /**
     * Annuler un upload en cours
     */
    public function cancelUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        
        if ($upload_id) {
            $this->cleanupUploadSession($upload_id);
        }

        $this->jsonResponse(true, 'Upload annulé');
    }

    // ==================== CRUD OPÉRATIONS ====================

    /**
     * Créer une vidéo
     */
    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = $this->prepareVideoData($type_source);
        
        if (!$data) {
            redirect(base_url('video'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);

        $this->setFlashMessage($rsp, 'Vidéo créée avec succès.', 'Erreur lors de la création.');
        redirect(base_url('video'));
    }

    /**
     * Mettre à jour une vidéo
     */
    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = $this->prepareUpdateData($id, $type_source);
        
        if (!$data) {
            redirect(base_url('video'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        $this->setFlashMessage($rsp, 'Vidéo mise à jour avec succès.', 'Erreur lors de la mise à jour.');
        redirect(base_url('video'));
    }

    /**
     * Supprimer une vidéo (soft delete)
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp && $video) {
            $this->deleteVideoFiles($video);
            $this->session->set_flashdata('success', 'Vidéo supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        
        redirect(base_url('video'));
    }

    /**
     * Changer le statut actif/inactif
     */
    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        $this->setFlashMessage($rsp, 'Statut mis à jour avec succès.', 'Erreur lors de la mise à jour du statut.');
        redirect(base_url('video'));    
    }

    /**
     * Toggle AJAX pour WhatsApp et Site Web
     */
    public function toggleField()
    {
        $this->setJSONHeaders();
        
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website'];
        if (!in_array($field, $allowed)) {
            $this->jsonResponse(false, 'Champ non autorisé');
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->jsonResponse((bool)$rsp);
    }

    // ==================== HELPERS PRIVÉS ====================

    /**
     * Valide l'initialisation d'upload
     */
    private function validateInitUpload($file_name, $file_size)
    {
        if (empty($file_name) || $file_size <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides'];
        }

        if ($file_size > $this->max_file_size) {
            return [
                'success' => false, 
                'message' => 'Fichier trop grand (max: ' . $this->formatBytes($this->max_file_size) . ')'
            ];
        }

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            return ['success' => false, 'message' => 'Format non supporté: ' . $ext];
        }

        return ['success' => true];
    }

    /**
     * Génère un ID d'upload unique
     */
    private function generateUploadId()
    {
        return 'upload_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Génère un nom de fichier final unique
     */
    private function generateFinalName($original_name)
    {
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        return date("YmdHis") . '_' . uniqid() . '_' . substr(md5($original_name), 0, 8) . '.' . $ext;
    }

    /**
     * Sauvegarde les métadonnées
     */
    private function saveMetadata($upload_id, $metadata)
    {
        $path = $this->upload_dir . $upload_id . '/metadata.json';
        file_put_contents($path, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Charge les métadonnées
     */
    private function loadMetadata($upload_id)
    {
        $path = $this->upload_dir . $upload_id . '/metadata.json';
        if (!file_exists($path)) return null;
        
        $content = file_get_contents($path);
        return json_decode($content, true);
    }

    /**
     * Met à jour l'activité
     */
    private function updateLastActivity($upload_id)
    {
        $metadata = $this->loadMetadata($upload_id);
        if ($metadata) {
            $metadata['last_activity'] = time();
            $this->saveMetadata($upload_id, $metadata);
        }
    }

    /**
     * Marque un chunk comme uploadé
     */
    private function markChunkUploaded($upload_id, $chunk_index)
    {
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) return;
        
        if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
            $metadata['uploaded_chunks'][] = $chunk_index;
            sort($metadata['uploaded_chunks']);
            $this->saveMetadata($upload_id, $metadata);
        }
    }

    /**
     * Calcule la progression
     */
    private function calculateProgress($upload_id)
    {
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) return null;
        
        $uploaded = count($metadata['uploaded_chunks']);
        $total = $metadata['total_chunks'];
        
        return [
            'uploaded_chunks' => $uploaded,
            'total_chunks' => $total,
            'percent' => round(($uploaded / $total) * 100, 2),
            'bytes_uploaded' => $uploaded * $metadata['chunk_size']
        ];
    }

    /**
     * Récupère les chunks réellement présents
     */
    private function getActualUploadedChunks($upload_id)
    {
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $chunks = [];
        
        foreach (glob($temp_dir . 'chunk_*') as $file) {
            if (preg_match('/chunk_(\d+)$/', $file, $m)) {
                $chunks[] = (int)$m[1];
            }
        }
        
        sort($chunks);
        return $chunks;
    }

    /**
     * Assemble les chunks en fichier final
     */
    private function assembleChunks($upload_id, $final_path, $metadata)
    {
        $start_time = microtime(true);
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        $out = fopen($final_path, 'wb');
        if (!$out) {
            return ['success' => false, 'message' => 'Impossible de créer fichier final'];
        }

        try {
            for ($i = 0; $i < $metadata['total_chunks']; $i++) {
                $chunk_file = $temp_dir . 'chunk_' . $i;
                
                if (!file_exists($chunk_file)) {
                    fclose($out);
                    @unlink($final_path);
                    return ['success' => false, 'message' => "Chunk $i manquant"];
                }

                // Lecture par morceaux pour économiser la mémoire
                $handle = fopen($chunk_file, 'rb');
                while (!feof($handle)) {
                    fwrite($out, fread($handle, 8192));
                }
                fclose($handle);
                
                // Supprimer chunk après écriture réussie
                unlink($chunk_file);
            }
            
            fclose($out);
            
            $time_ms = round((microtime(true) - $start_time) * 1000);
            
            return [
                'success' => true,
                'time_ms' => $time_ms
            ];
            
        } catch (Exception $e) {
            fclose($out);
            @unlink($final_path);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Nettoie une session d'upload
     */
    private function cleanupUploadSession($upload_id)
    {
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $this->recursiveDelete($temp_dir);
    }

    /**
     * Prépare les données pour création
     */
    private function prepareVideoData($type_source)
    {
        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'video',
            'description' => $this->input->post('description') ?: null,
            'categorie' => $this->input->post('categorie') ?: null,
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits') ?: null,
            'est_actif' => 1,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($type_source == 'upload') {
            $file_path = $this->input->post('uploaded_file_path');
            
            if (empty($file_path) || !file_exists(FCPATH . $file_path)) {
                $this->session->set_flashdata('error', 'Aucun fichier vidéo uploadé.');
                return false;
            }
            
            $data['fichier'] = $file_path;
            $data['taille'] = filesize(FCPATH . $file_path);
            $data['mime_type'] = mime_content_type(FCPATH . $file_path);
            $data['miniature'] = $this->input->post('thumbnail') ?: $this->generateVideoThumbnail($file_path);
        } else {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->extractVideoThumbnail($data['lien']);
        }
        
        return $data;
    }

    /**
     * Prépare les données pour mise à jour
     */
    private function prepareUpdateData($id, $type_source)
    {
        $data = [
            'titre' => $this->input->post('titre'),
            'description' => $this->input->post('description') ?: null,
            'categorie' => $this->input->post('categorie') ?: null,
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits') ?: null,
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $old = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        if ($type_source == 'upload') {
            $new_path = $this->input->post('uploaded_file_path');
            
            if (!empty($new_path) && file_exists(FCPATH . $new_path)) {
                // Supprimer ancien fichier
                if ($old && !empty($old['fichier'])) {
                    $this->deleteVideoFiles($old);
                }
                
                $data['fichier'] = $new_path;
                $data['taille'] = filesize(FCPATH . $new_path);
                $data['mime_type'] = mime_content_type(FCPATH . $new_path);
                $data['lien'] = null;
                $data['miniature'] = $this->generateVideoThumbnail($new_path);
            }
        } elseif ($type_source == 'link') {
            $new_lien = $this->input->post('lien');
            
            if ($old && !empty($old['fichier'])) {
                $this->deleteVideoFiles($old);
            }
            
            $data['lien'] = $new_lien;
            $data['fichier'] = null;
            $data['taille'] = null;
            $data['mime_type'] = null;
            $data['miniature'] = $this->extractVideoThumbnail($new_lien);
        }

        return $data;
    }

    /**
     * Supprime les fichiers physiques d'une vidéo
     */
    private function deleteVideoFiles($video)
    {
        if (!empty($video['fichier']) && file_exists(FCPATH . $video['fichier'])) {
            @unlink(FCPATH . $video['fichier']);
        }
        if (!empty($video['miniature']) && file_exists(FCPATH . $video['miniature'])) {
            @unlink(FCPATH . $video['miniature']);
        }
    }

    /**
     * Récupère les catégories existantes
     */
    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $query = $this->db->get('galerie_medias');
        
        $categories = [];
        foreach ($query->result() as $row) {
            $categories[] = $row->cat;
        }
        return $categories;
    }

    /**
     * Génère une miniature pour vidéo locale
     */
    private function generateVideoThumbnail($video_path)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $folder = FCPATH . 'attachments/Galerie/';
        $name = pathinfo($video_path, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $folder . $name;

        // Extraire à 10% de la durée pour éviter les intros noires
        $cmd = sprintf(
            '%s -i %s -ss 00:00:01 -vframes 1 -q:v 2 -vf "scale=480:-1" -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg(FCPATH . $video_path),
            escapeshellarg($thumb_path)
        );

        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($thumb_path)) 
            ? 'attachments/Galerie/' . $name 
            : null;
    }

    /**
     * Extrait miniature depuis URL externe
     */
    private function extractVideoThumbnail($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            $vimeo_data = @file_get_contents("https://vimeo.com/api/v2/video/{$m[1]}.json");
            if ($vimeo_data) {
                $data = json_decode($vimeo_data, true);
                return $data[0]['thumbnail_medium'] ?? null;
            }
        }
        
        // Dailymotion
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $m)) {
            return "https://www.dailymotion.com/thumbnail/video/{$m[1]}";
        }
        
        return null;
    }

    /**
     * Trouve l'exécutable FFmpeg
     */
    private function findFFmpeg()
    {
        $paths = [
            'ffmpeg',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/ffmpeg/bin/ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe'
        ];
        
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        
        return null;
    }

    /**
     * Retourne le chemin d'un chunk
     */
    private function getChunkPath($upload_id, $chunk_index)
    {
        return $this->upload_dir . $upload_id . '/chunk_' . $chunk_index;
    }

    /**
     * Définit les headers JSON
     */
    private function setJSONHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    /**
     * Réponse JSON standardisée
     */
    private function jsonResponse($success, $message = '', $data = [])
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'timestamp' => time()
        ], $data));
    }

    /**
     * Message flash
     */
    private function setFlashMessage($success, $success_msg, $error_msg)
    {
        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success ? $success_msg : $error_msg
        );
    }

    /**
     * Log d'erreur
     */
    private function logError($context, $details)
    {
        $log = date('Y-m-d H:i:s') . " | $context | " . json_encode($details) . "\n";
        error_log($log, 3, FCPATH . 'logs/upload_errors.log');
    }

    /**
     * Détail des erreurs d'upload
     */
    private function getDetailedUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => ['type' => 'PHP_LIMIT', 'message' => 'Fichier trop grand pour upload_max_filesize (' . ini_get('upload_max_filesize') . ')'],
            UPLOAD_ERR_FORM_SIZE => ['type' => 'FORM_LIMIT', 'message' => 'Fichier trop grand pour MAX_FILE_SIZE'],
            UPLOAD_ERR_PARTIAL => ['type' => 'NETWORK', 'message' => 'Upload partiel - erreur réseau'],
            UPLOAD_ERR_NO_FILE => ['type' => 'NO_FILE', 'message' => 'Aucun fichier reçu'],
            UPLOAD_ERR_NO_TMP_DIR => ['type' => 'SERVER_CONFIG', 'message' => 'Dossier temporaire manquant'],
            UPLOAD_ERR_CANT_WRITE => ['type' => 'DISK', 'message' => 'Erreur écriture disque'],
            UPLOAD_ERR_EXTENSION => ['type' => 'PHP_EXT', 'message' => 'Extension PHP bloquante']
        ];
        
        return $errors[$code] ?? ['type' => 'UNKNOWN', 'message' => 'Erreur #' . $code];
    }

    /**
     * Formate une taille en bytes
     */
    private function formatBytes($bytes)
    {
        if ($bytes >= 1099511627776) return number_format($bytes / 1099511627776, 2) . ' TB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}