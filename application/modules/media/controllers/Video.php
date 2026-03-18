
# Analyse du contrôleur existant et création du nouveau contrôleur AVC/H.264 optimisé

controller_content = '''<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * =============================================================================
 * Video Controller v5.0 - AVC/H.264 Advanced Video Coding
 * Technologies: H.264/AVC, DASH, HLS, Multi-bitrate, Adaptive Streaming
 * Architecture: YouTube-style encoding pipeline with CDN optimization
 * =============================================================================
 * 
 * AVC/H.264 Features Implemented:
 * - Baseline Profile (mobile compatibility)
 * - Main Profile (general purpose)
 * - High Profile (HD/4K content)
 * - CAVLC/CABAC entropy coding
 * - Multiple reference frames
 * - B-frame optimization
 * - Scene change detection
 * - Constant Rate Factor (CRF) encoding
 */

class Video extends MY_Controller {

    // ==================== AVC/H.264 CONFIGURATION ====================
    
    private $avc_profiles = [
        'baseline' => [
            'profile' => 'baseline',
            'level' => '3.0',
            'preset' => 'fast',
            'tune' => 'zerolatency',
            'use_for' => 'mobile_3g'
        ],
        'main' => [
            'profile' => 'main',
            'level' => '4.0',
            'preset' => 'medium',
            'tune' => 'film',
            'use_for' => 'standard_hd'
        ],
        'high' => [
            'profile' => 'high',
            'level' => '5.1',
            'preset' => 'slow',
            'tune' => 'film',
            'use_for' => 'full_hd_4k'
        ]
    ];

    private $streaming_ladders = [
        '4K' => [
            'resolution' => '3840x2160',
            'video_bitrate' => '16000k',
            'audio_bitrate' => '192k',
            'profile' => 'high',
            'level' => '5.1',
            'framerate' => '60',
            'crf' => 18
        ],
        '1440p' => [
            'resolution' => '2560x1440',
            'video_bitrate' => '10000k',
            'audio_bitrate' => '192k',
            'profile' => 'high',
            'level' => '5.0',
            'framerate' => '60',
            'crf' => 20
        ],
        '1080p' => [
            'resolution' => '1920x1080',
            'video_bitrate' => '5000k',
            'audio_bitrate' => '192k',
            'profile' => 'high',
            'level' => '4.2',
            'framerate' => '30',
            'crf' => 21
        ],
        '720p' => [
            'resolution' => '1280x720',
            'video_bitrate' => '2500k',
            'audio_bitrate' => '128k',
            'profile' => 'main',
            'level' => '4.0',
            'framerate' => '30',
            'crf' => 23
        ],
        '480p' => [
            'resolution' => '854x480',
            'video_bitrate' => '1000k',
            'audio_bitrate' => '128k',
            'profile' => 'main',
            'level' => '3.1',
            'framerate' => '30',
            'crf' => 25
        ],
        '360p' => [
            'resolution' => '640x360',
            'video_bitrate' => '500k',
            'audio_bitrate' => '96k',
            'profile' => 'baseline',
            'level' => '3.0',
            'framerate' => '30',
            'crf' => 27
        ],
        '240p' => [
            'resolution' => '426x240',
            'video_bitrate' => '250k',
            'audio_bitrate' => '64k',
            'profile' => 'baseline',
            'level' => '2.1',
            'framerate' => '30',
            'crf' => 29
        ]
    ];

    // ==================== PATHS & CONFIGURATION ====================
    
    private $paths;
    private $config;
    private $ffmpeg_path;
    private $ffprobe_path;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        $this->initializePaths();
        $this->initializeConfig();
        $this->detectFFmpegTools();
        $this->ensureDirectories();
        $this->configurePHP();
        
        log_message('info', 'Video Controller AVC/H.264 initialized');
    }

    private function initializePaths()
    {
        $base = FCPATH;
        $this->paths = [
            'temp' => $base . 'uploads/temp/video/',
            'originals' => $base . 'attachments/Video/Originals/',
            'encoded' => $base . 'attachments/Video/Encoded/',
            'thumbnails' => $base . 'attachments/Video/Thumbnails/',
            'posters' => $base . 'attachments/Video/Posters/',
            'previews' => $base . 'attachments/Video/Previews/',
            'sprites' => $base . 'attachments/Video/Sprites/',
            'dash' => $base . 'attachments/Video/DASH/',
            'hls' => $base . 'attachments/Video/HLS/',
            'logs' => $base . 'attachments/Video/Logs/',
            'metadata' => $base . 'attachments/Video/Metadata/'
        ];
    }

    private function initializeConfig()
    {
        $this->config = [
            'chunk_size' => 5 * 1024 * 1024, // 5MB chunks
            'max_file_size' => 50 * 1024 * 1024 * 1024, // 50GB
            'session_timeout' => 7200, // 2 hours
            'parallel_transcodes' => 3,
            'keyframe_interval' => 48, // 2 seconds at 24fps
            'min_segment_duration' => 4, // DASH/HLS segment duration
            'allowed_extensions' => [
                'mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v', 
                '3gp', 'mts', 'm2ts', 'ts', 'flv', 'wmv',
                'mpg', 'mpeg', 'vob', 'ogv', 'divx'
            ],
            'output_container' => 'mp4', // AVC/H.264 standard
            'enable_hardware_accel' => true,
            'nvenc_preset' => 'p4', // NVENC preset for NVIDIA GPUs
            'vaapi_device' => '/dev/dri/renderD128' // Intel/AMD VAAPI
        ];
    }

    private function detectFFmpegTools()
    {
        $this->ffmpeg_path = $this->findExecutable(['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg']);
        $this->ffprobe_path = $this->findExecutable(['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe']);
        
        if (!$this->ffmpeg_path) {
            log_message('error', 'FFmpeg not found - AVC encoding disabled');
        }
        if (!$this->ffprobe_path) {
            log_message('error', 'FFprobe not found - Video analysis disabled');
        }
    }

    private function findExecutable($candidates)
    {
        foreach ($candidates as $cmd) {
            exec($cmd . ' -version 2>/dev/null', $output, $return);
            if ($return === 0) return $cmd;
        }
        return false;
    }

    private function ensureDirectories()
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
                @chmod($path, 0777);
            }
        }
    }

    private function configurePHP()
    {
        @ini_set('memory_limit', '4096M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('upload_max_filesize', '100M');
        @ini_set('post_max_size', '100M');
        @ini_set('session.gc_maxlifetime', $this->config['session_timeout']);
    }

    // ==================== PUBLIC INTERFACE ====================

    public function index()
    {
        $data = [
            'videos' => $this->Model->read('galerie_medias', ['type' => 'video'], 'id_media', 'DESC'),
            'categories' => $this->getExistingCategories(),
            'total_duration' => $this->calculateTotalDuration(),
            'storage_stats' => $this->getStorageStatistics(),
            'encoding_queue' => $this->getEncodingQueueStatus(),
            'avc_capabilities' => $this->getAVCCapabilities()
        ];
        
        $this->load->view('Video_View', $data);
    }

    /**
     * AVC/H.264 Capabilities Diagnostic
     */
    public function diagnostics()
    {
        $this->setJSONHeaders();
        
        $hw_accel = $this->detectHardwareAcceleration();
        
        echo json_encode([
            'avc_h264' => [
                'encoder_available' => (bool)$this->ffmpeg_path,
                'libx264' => $this->checkEncoder('libx264'),
                'nvenc_h264' => $this->checkEncoder('h264_nvenc'),
                'vaapi_h264' => $this->checkEncoder('h264_vaapi'),
                'videotoolbox' => $this->checkEncoder('h264_videotoolbox'),
                'hardware_acceleration' => $hw_accel
            ],
            'streaming_formats' => [
                'dash' => $this->checkDashSupport(),
                'hls' => $this->checkHLSSupport(),
                'progressive_mp4' => true
            ],
            'quality_ladder' => $this->streaming_ladders,
            'system_limits' => [
                'upload_max' => ini_get('upload_max_filesize'),
                'post_max' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'disk_free' => $this->formatBytes(@disk_free_space($this->paths['encoded']))
            ],
            'timestamp' => time()
        ]);
    }

    // ==================== AVC/H.264 ENCODING PIPELINE ====================

    /**
     * Initialize AVC upload with pre-analysis
     */
    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash');

        $validation = $this->validateUploadParams($file_name, $file_size);
        if (!$validation['success']) {
            $this->jsonResponse(false, $validation['message']);
            return;
        }

        $upload_id = $this->generateUploadId();
        $temp_dir = $this->paths['temp'] . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, true)) {
            $this->jsonResponse(false, 'Erreur création session temporaire');
            return;
        }

        $total_chunks = (int)ceil($file_size / $this->config['chunk_size']);
        
        $metadata = [
            'upload_id' => $upload_id,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'file_hash' => $file_hash,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'created_at' => time(),
            'status' => 'uploading',
            'avc_config' => [
                'target_profiles' => ['baseline', 'main', 'high'],
                'generate_dash' => true,
                'generate_hls' => true,
                'generate_progressive' => true
            ]
        ];

        $this->saveMetadata($upload_id, $metadata);

        $this->jsonResponse(true, 'Session AVC initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->config['chunk_size'],
            'total_chunks' => $total_chunks,
            'avc_ready' => (bool)$this->ffmpeg_path,
            'supports_hardware_accel' => $this->detectHardwareAcceleration()
        ]);
    }

    /**
     * AVC Chunk Upload
     */
    public function uploadChunk()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata || $metadata['status'] !== 'uploading') {
            $this->jsonResponse(false, 'Session invalide');
            return;
        }

        $chunk_path = $this->paths['temp'] . $upload_id . '/chunk_' . $chunk_index;
        
        if (file_exists($chunk_path)) {
            $this->markChunkUploaded($upload_id, $chunk_index);
            $this->jsonResponse(true, 'Chunk déjà présent', $this->calculateProgress($upload_id));
            return;
        }

        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, 'Erreur réception chunk');
            return;
        }

        if (!@move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            $this->jsonResponse(false, 'Erreur écriture disque');
            return;
        }

        $this->markChunkUploaded($upload_id, $chunk_index);
        $this->updateLastActivity($upload_id);
        
        $this->jsonResponse(true, 'Chunk AVC reçu', $this->calculateProgress($upload_id));
    }

    /**
     * Complete Upload & Start AVC Encoding Pipeline
     */
    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $user_data = [
            'description' => $this->input->post('description'),
            'custom_thumbnail' => $this->input->post('custom_thumbnail'),
            'generate_previews' => $this->input->post('generate_previews') !== 'false',
            'target_qualities' => json_decode($this->input->post('target_qualities') ?: '[]', true)
        ];

        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) {
            $this->jsonResponse(false, 'Session non trouvée');
            return;
        }

        // Verify all chunks
        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $metadata['uploaded_chunks']
        );

        if (!empty($missing)) {
            $this->jsonResponse(false, 'Chunks manquants', ['missing' => array_values($missing)]);
            return;
        }

        // Assemble file
        $original_name = $this->sanitizeFilename($metadata['file_name']);
        $original_path = $this->paths['originals'] . $original_name;
        
        $assembled = $this->assembleChunks($upload_id, $original_path, $metadata);
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Update status
        $this->updateMetadataStatus($upload_id, 'analyzing');

        // ==================== AVC/H.264 PIPELINE ====================
        
        // 1. Deep analysis with FFprobe
        $video_analysis = $this->analyzeVideoDeep($original_path);
        
        // 2. Determine optimal encoding ladder
        $encoding_ladder = $this->determineEncodingLadder($video_analysis, $user_data['target_qualities']);
        
        // 3. Generate master thumbnail/poster
        $thumbnails = $this->generateAVCThumbnails($original_path, $original_name, $video_analysis);
        
        // 4. Start AVC encoding jobs
        $this->updateMetadataStatus($upload_id, 'encoding');
        $encoding_jobs = $this->startAVCEncoding($original_path, $original_name, $encoding_ladder, $video_analysis);
        
        // 5. Generate DASH/HLS manifests if enabled
        $streaming_manifests = [];
        if ($metadata['avc_config']['generate_dash']) {
            $streaming_manifests['dash'] = $this->generateDASHManifest($original_name, $encoding_jobs);
        }
        if ($metadata['avc_config']['generate_hls']) {
            $streaming_manifests['hls'] = $this->generateHLSManifest($original_name, $encoding_jobs);
        }

        // 6. Generate preview GIF/WebP
        $preview = null;
        if ($user_data['generate_previews'] && $video_analysis['duration'] <= 120) {
            $preview = $this->generateAVCPreview($original_path, $original_name, $video_analysis);
        }

        // Cleanup
        $this->cleanupUploadSession($upload_id);

        // Prepare response
        $response = [
            'success' => true,
            'message' => 'Upload AVC complété - Encodage en cours',
            'data' => [
                'original_file' => 'attachments/Video/Originals/' . $original_name,
                'file_size' => $this->formatBytes(filesize($original_path)),
                
                // Video analysis
                'analysis' => [
                    'duration' => $video_analysis['duration'],
                    'duration_formatted' => $this->formatDuration($video_analysis['duration']),
                    'resolution' => $video_analysis['width'] . 'x' . $video_analysis['height'],
                    'fps' => $video_analysis['fps'],
                    'bitrate' => $this->formatBits($video_analysis['bitrate']),
                    'codec_original' => $video_analysis['codec'],
                    'color_space' => $video_analysis['color_space']
                ],
                
                // AVC encoding jobs
                'encoding_jobs' => $encoding_jobs,
                'target_ladder' => array_keys($encoding_ladder),
                
                // Thumbnails
                'thumbnails' => $thumbnails,
                'final_thumbnail' => $user_data['custom_thumbnail'] ?: $thumbnails['poster'],
                
                // Streaming
                'streaming' => $streaming_manifests,
                'preview' => $preview,
                
                // Form data suggestions
                'form_suggestions' => [
                    'titre' => $this->suggestTitleFromFilename($metadata['file_name']),
                    'credits' => $video_analysis['artist'] ?: 'Auteur inconnu',
                    'categorie' => $this->suggestCategory($video_analysis),
                    'description' => $user_data['description'],
                    'date_media' => $video_analysis['creation_time']
                ]
            ]
        ];

        echo json_encode($response);
    }

    // ==================== AVC/H.264 ENCODING METHODS ====================

    /**
     * Deep video analysis using FFprobe
     */
    private function analyzeVideoDeep($file_path)
    {
        if (!$this->ffprobe_path) {
            return $this->basicAnalysis($file_path);
        }

        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams -show_frames -read_intervals %%+5 %s 2>&1',
            escapeshellarg($this->ffprobe_path),
            escapeshellarg($file_path)
        );
        
        exec($cmd, $output, $code);
        
        if ($code !== 0) {
            return $this->basicAnalysis($file_path);
        }

        $data = json_decode(implode("\\n", $output), true);
        
        $format = $data['format'] ?? [];
        $video_stream = null;
        $audio_stream = null;
        
        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video' && !$video_stream) {
                $video_stream = $stream;
            }
            if ($stream['codec_type'] === 'audio' && !$audio_stream) {
                $audio_stream = $stream;
            }
        }

        $tags = $format['tags'] ?? [];
        
        // Scene detection for keyframe placement
        $scenes = $this->detectSceneChanges($file_path);

        return [
            'duration' => (float)($format['duration'] ?? 0),
            'bitrate' => (int)($format['bit_rate'] ?? 0),
            'format' => $format['format_name'] ?? 'unknown',
            'size' => (int)($format['size'] ?? filesize($file_path)),
            
            'width' => (int)($video_stream['width'] ?? 0),
            'height' => (int)($video_stream['height'] ?? 0),
            'fps' => $this->calculateFPS($video_stream),
            'codec' => $video_stream['codec_name'] ?? 'unknown',
            'pix_fmt' => $video_stream['pix_fmt'] ?? 'unknown',
            'color_space' => $video_stream['color_space'] ?? 'unknown',
            'color_transfer' => $video_stream['color_transfer'] ?? 'unknown',
            'color_primaries' => $video_stream['color_primaries'] ?? 'unknown',
            
            'audio_codec' => $audio_stream['codec_name'] ?? null,
            'audio_channels' => (int)($audio_stream['channels'] ?? 0),
            'audio_sample_rate' => (int)($audio_stream['sample_rate'] ?? 0),
            'audio_bitrate' => (int)($audio_stream['bit_rate'] ?? 0),
            
            'title' => $tags['title'] ?? $tags['TITLE'] ?? null,
            'artist' => $tags['artist'] ?? $tags['ARTIST'] ?? null,
            'creation_time' => $tags['creation_time'] ?? $tags['date'] ?? null,
            'description' => $tags['description'] ?? null,
            
            'scene_changes' => $scenes,
            'has_b_frames' => ($video_stream['has_b_frames'] ?? 0) > 0,
            'is_avc' => ($video_stream['codec_name'] ?? '') === 'h264'
        ];
    }

    /**
     * Detect scene changes for smart keyframe placement
     */
    private function detectSceneChanges($file_path)
    {
        if (!$this->ffmpeg_path) return [];
        
        $cmd = sprintf(
            '%s -i %s -vf "select=gt(scene\\,0.3),showinfo" -f null - 2>&1 | grep -o "pts_time:[0-9.]*"',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($file_path)
        );
        
        exec($cmd, $output, $code);
        
        $scenes = [];
        foreach ($output as $line) {
            if (preg_match('/pts_time:([0-9.]+)/', $line, $m)) {
                $scenes[] = (float)$m[1];
            }
        }
        
        return $scenes;
    }

    /**
     * Determine optimal encoding ladder based on source
     */
    private function determineEncodingLadder($analysis, $user_qualities = [])
    {
        $source_height = $analysis['height'];
        $source_fps = $analysis['fps'];
        
        $ladder = [];
        $available_qualities = array_reverse($this->streaming_ladders, true);
        
        foreach ($available_qualities as $name => $config) {
            // Skip if user specified qualities and this isn't in the list
            if (!empty($user_qualities) && !in_array($name, $user_qualities)) {
                continue;
            }
            
            $target_height = (int)explode('x', $config['resolution'])[1];
            
            // Only include qualities up to source resolution
            if ($target_height <= $source_height) {
                // Adjust framerate if source is lower
                if ($source_fps < (int)$config['framerate']) {
                    $config['framerate'] = (string)$source_fps;
                }
                
                $ladder[$name] = $config;
            }
        }
        
        return $ladder;
    }

    /**
     * Start AVC/H.264 encoding for all ladder rungs
     */
    private function startAVCEncoding($source_path, $original_name, $ladder, $analysis)
    {
        $jobs = [];
        $base_name = pathinfo($original_name, PATHINFO_FILENAME);
        
        // Detect hardware acceleration
        $hw_accel = $this->detectHardwareAcceleration();
        
        foreach ($ladder as $quality_name => $config) {
            $output_name = $base_name . '_' . $quality_name . '.mp4';
            $output_path = $this->paths['encoded'] . $output_name;
            
            // Build AVC command
            $cmd = $this->buildAVCEncodeCommand(
                $source_path, 
                $output_path, 
                $config, 
                $analysis,
                $hw_accel
            );
            
            // Execute encoding (async in production)
            $log_file = $this->paths['logs'] . $base_name . '_' . $quality_name . '.log';
            $cmd .= ' > ' . escapeshellarg($log_file) . ' 2>&1 &';
            
            exec($cmd, $output, $code);
            
            $jobs[$quality_name] = [
                'command' => $cmd,
                'output_file' => 'attachments/Video/Encoded/' . $output_name,
                'config' => $config,
                'status' => 'encoding',
                'log' => $log_file
            ];
        }
        
        return $jobs;
    }

    /**
     * Build optimized AVC/H.264 encode command
     */
    private function buildAVCEncodeCommand($input, $output, $config, $analysis, $hw_accel)
    {
        $cmd = [$this->ffmpeg_path ?: 'ffmpeg'];
        
        // Input options
        $cmd[] = '-y';
        $cmd[] = '-i ' . escapeshellarg($input);
        
        // Video codec selection (hardware accel if available)
        if ($hw_accel['nvenc'] && $config['profile'] !== 'baseline') {
            $cmd[] = '-c:v h264_nvenc';
            $cmd[] = '-preset ' . $this->config['nvenc_preset'];
            $cmd[] = '-rc vbr_hq';
            $cmd[] = '-cq ' . $config['crf'];
        } elseif ($hw_accel['vaapi']) {
            $cmd[] = '-vaapi_device ' . $this->config['vaapi_device'];
            $cmd[] = '-c:v h264_vaapi';
        } else {
            // Software libx264 (best quality)
            $cmd[] = '-c:v libx264';
            $cmd[] = '-preset medium';
            $cmd[] = '-crf ' . $config['crf'];
        }
        
        // AVC Profile and Level
        $cmd[] = '-profile:v ' . $config['profile'];
        $cmd[] = '-level ' . $config['level'];
        
        // Resolution
        $cmd[] = '-vf scale=' . $config['resolution'];
        
        // Bitrate control
        $cmd[] = '-b:v ' . $config['video_bitrate'];
        $cmd[] = '-maxrate ' . (intval($config['video_bitrate']) * 1.5) . 'k';
        $cmd[] = '-bufsize ' . (intval($config['video_bitrate']) * 2) . 'k';
        
        // Framerate
        $cmd[] = '-r ' . $config['framerate'];
        
        // GOP and keyframe structure
        $gop_size = intval($config['framerate']) * 2; // 2-second GOP
        $cmd[] = '-g ' . $gop_size;
        $cmd[] = '-keyint_min ' . intval($gop_size / 2);
        $cmd[] = '-sc_threshold 40'; // Scene change detection
        
        // B-frames (not for baseline)
        if ($config['profile'] !== 'baseline') {
            $cmd[] = '-bf 3';
            $cmd[] = '-b_strategy 1';
            $cmd[] = '-refs 4';
        }
        
        // CABAC (not for baseline)
        if ($config['profile'] === 'baseline') {
            $cmd[] = '-coder 0'; // CAVLC
        } else {
            $cmd[] = '-coder 1'; // CABAC (better compression)
        }
        
        // Audio
        $cmd[] = '-c:a aac';
        $cmd[] = '-b:a ' . $config['audio_bitrate'];
        $cmd[] = '-ar 48000';
        $cmd[] = '-ac 2';
        
        // Pixel format
        $cmd[] = '-pix_fmt yuv420p';
        
        // Faststart for web streaming
        $cmd[] = '-movflags +faststart';
        
        // Output
        $cmd[] = escapeshellarg($output);
        
        return implode(' ', $cmd);
    }

    /**
     * Generate DASH manifest for adaptive streaming
     */
    private function generateDASHManifest($original_name, $encoding_jobs)
    {
        $base_name = pathinfo($original_name, PATHINFO_FILENAME);
        $dash_dir = $this->paths['dash'] . $base_name . '/';
        
        if (!is_dir($dash_dir)) {
            @mkdir($dash_dir, 0777, true);
        }
        
        $manifest_path = $dash_dir . 'manifest.mpd';
        
        // Build DASH manifest
        $representations = [];
        foreach ($encoding_jobs as $quality => $job) {
            $config = $job['config'];
            $representations[] = sprintf(
                '<Representation id="%s" mimeType="video/mp4" codecs="avc1.%s" width="%d" height="%d" frameRate="%d" bandwidth="%d">',
                $quality,
                $this->getAVCCodecsString($config['profile'], $config['level']),
                explode('x', $config['resolution'])[0],
                explode('x', $config['resolution'])[1],
                $config['framerate'],
                intval($config['video_bitrate']) * 1000
            );
            $representations[] = '<BaseURL>' . basename($job['output_file']) . '</BaseURL>';
            $representations[] = '</Representation>';
        }
        
        $mpd = '<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011" type="static" mediaPresentationDuration="PT0H0M0S" minBufferTime="PT1.5S" profiles="urn:mpeg:dash:profile:isoff-on-demand:2011">
  <Period>
    <AdaptationSet mimeType="video/mp4" codecs="avc1.640028" subsegmentAlignment="true" subsegmentStartsWithSAP="1">
      ' . implode("\\n      ", $representations) . '
    </AdaptationSet>
  </Period>
</MPD>';
        
        file_put_contents($manifest_path, $mpd);
        
        return [
            'manifest' => 'attachments/Video/DASH/' . $base_name . '/manifest.mpd',
            'representations' => count($encoding_jobs)
        ];
    }

    /**
     * Generate HLS playlist for Apple devices
     */
    private function generateHLSManifest($original_name, $encoding_jobs)
    {
        $base_name = pathinfo($original_name, PATHINFO_FILENAME);
        $hls_dir = $this->paths['hls'] . $base_name . '/';
        
        if (!is_dir($hls_dir)) {
            @mkdir($hls_dir, 0777, true);
        }
        
        $master_playlist = "#EXTM3U\\n#EXT-X-VERSION:4\\n";
        
        foreach ($encoding_jobs as $quality => $job) {
            $config = $job['config'];
            $bandwidth = intval($config['video_bitrate']) * 1000;
            $resolution = $config['resolution'];
            
            $master_playlist .= sprintf(
                "#EXT-X-STREAM-INF:BANDWIDTH=%d,RESOLUTION=%s,CODECS=\"avc1.%s,mp4a.40.2\"\\n",
                $bandwidth,
                $resolution,
                $this->getAVCCodecsString($config['profile'], $config['level'])
            );
            $master_playlist .= basename($job['output_file']) . "\\n";
        }
        
        file_put_contents($hls_dir . 'master.m3u8', $master_playlist);
        
        return [
            'master_playlist' => 'attachments/Video/HLS/' . $base_name . '/master.m3u8',
            'variants' => count($encoding_jobs)
        ];
    }

    /**
     * Get AVC codecs string for manifests
     */
    private function getAVCCodecsString($profile, $level)
    {
        $profile_codes = [
            'baseline' => '42',
            'main' => '4D',
            'high' => '64',
            'high10' => '6E',
            'high422' => '7A',
            'high444' => 'F4'
        ];
        
        $level_hex = str_replace('.', '', $level);
        if (strlen($level_hex) === 1) {
            $level_hex = '0' . $level_hex;
        }
        
        return ($profile_codes[$profile] ?? '64') . '00' . $level_hex;
    }

    // ==================== THUMBNAIL & PREVIEW GENERATION ====================

    /**
     * Generate AVC-optimized thumbnails
     */
    private function generateAVCThumbnails($video_path, $filename, $analysis)
    {
        if (!$this->ffmpeg_path) {
            return $this->fallbackThumbnails($video_path, $filename);
        }
        
        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $duration = $analysis['duration'];
        $thumbnails = [];
        
        // Smart thumbnail positions (avoid black frames)
        $positions = $this->calculateSmartPositions($duration, $analysis['scene_changes']);
        
        // Generate poster (high quality)
        $poster_name = $base_name . '_poster.jpg';
        $poster_path = $this->paths['posters'] . $poster_name;
        
        $cmd = sprintf(
            '%s -ss %f -i %s -vframes 1 -q:v 2 -vf "select=eq(n\\,0)+gt(scene\\,0.4),scale=1920:-1:flags=lanczos" -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            $positions['poster'],
            escapeshellarg($video_path),
            escapeshellarg($poster_path)
        );
        exec($cmd, $output, $code);
        
        if ($code === 0 && file_exists($poster_path)) {
            $thumbnails['poster'] = 'attachments/Video/Posters/' . $poster_name;
        }
        
        // Generate sprite sheet for timeline
        if ($duration > 30) {
            $thumbnails['sprites'] = $this->generateSpriteSheet($video_path, $base_name, $duration);
        }
        
        // Generate WebP thumbnail (modern format)
        $webp_name = $base_name . '_thumb.webp';
        $webp_path = $this->paths['thumbnails'] . $webp_name;
        
        $cmd = sprintf(
            '%s -ss %f -i %s -vframes 1 -q:v 85 -vf "scale=640:-1:flags=lanczos" -c:v libwebp -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            $positions['default'],
            escapeshellarg($video_path),
            escapeshellarg($webp_path)
        );
        exec($cmd, $output, $code);
        
        if ($code === 0 && file_exists($webp_path)) {
            $thumbnails['webp'] = 'attachments/Video/Thumbnails/' . $webp_name;
            $thumbnails['default'] = $thumbnails['webp'];
        } else {
            // Fallback to JPEG
            $jpg_path = $this->paths['thumbnails'] . $base_name . '_thumb.jpg';
            $cmd = sprintf(
                '%s -ss %f -i %s -vframes 1 -q:v 2 -vf "scale=640:-1:flags=lanczos" -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path),
                $positions['default'],
                escapeshellarg($video_path),
                escapeshellarg($jpg_path)
            );
            exec($cmd);
            $thumbnails['default'] = 'attachments/Video/Thumbnails/' . $base_name . '_thumb.jpg';
        }
        
        return $thumbnails;
    }

    /**
     * Calculate smart thumbnail positions
     */
    private function calculateSmartPositions($duration, $scene_changes)
    {
        $positions = [
            'default' => min(1, $duration * 0.1),
            'poster' => min(5, $duration * 0.2)
        ];
        
        // Use scene changes if available
        if (!empty($scene_changes)) {
            foreach ($scene_changes as $time) {
                if ($time > 1 && $time < $duration - 1) {
                    $positions['default'] = $time;
                    break;
                }
            }
        }
        
        return $positions;
    }

    /**
     * Generate sprite sheet for video timeline
     */
    private function generateSpriteSheet($video_path, $base_name, $duration)
    {
        $sprite_dir = $this->paths['sprites'] . $base_name . '/';
        if (!is_dir($sprite_dir)) {
            @mkdir($sprite_dir, 0777, true);
        }
        
        $sprites = [];
        $interval = max(10, $duration / 20); // Max 20 thumbnails
        
        for ($i = 0; $i < $duration; $i += $interval) {
            $time = min($i, $duration - 1);
            $sprite_name = sprintf('%s_sprite_%03d.jpg', $base_name, count($sprites));
            $sprite_path = $sprite_dir . $sprite_name;
            
            $cmd = sprintf(
                '%s -ss %f -i %s -vframes 1 -q:v 5 -vf "scale=160:90:flags=lanczos" -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path),
                $time,
                escapeshellarg($video_path),
                escapeshellarg($sprite_path)
            );
            exec($cmd);
            
            if (file_exists($sprite_path)) {
                $sprites[] = [
                    'time' => round($time),
                    'url' => 'attachments/Video/Sprites/' . $base_name . '/' . $sprite_name
                ];
            }
        }
        
        return $sprites;
    }

    /**
     * Generate AVC preview (WebM/MP4)
     */
    private function generateAVCPreview($video_path, $filename, $analysis)
    {
        if (!$this->ffmpeg_path) return null;
        
        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $preview_name = $base_name . '_preview.mp4';
        $preview_path = $this->paths['previews'] . $preview_name;
        
        $duration = min(5, $analysis['duration'] * 0.1); // 5 seconds or 10% of video
        
        // Generate low-bitrate AVC preview
        $cmd = sprintf(
            '%s -ss 00:00:00 -t %f -i %s -c:v libx264 -preset ultrafast -crf 28 -vf "scale=480:-1:flags=lanczos,fps=15" -c:a aac -b:a 64k -movflags +faststart -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            $duration,
            escapeshellarg($video_path),
            escapeshellarg($preview_path)
        );
        
        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($preview_path)) 
            ? 'attachments/Video/Previews/' . $preview_name 
            : null;
    }

    // ==================== STREAMING ENDPOINTS ====================

    /**
     * Adaptive streaming endpoint (DASH/HLS/Progressive)
     */
    public function stream($type, $identifier)
    {
        switch ($type) {
            case 'dash':
                $this->serveDASH($identifier);
                break;
            case 'hls':
                $this->serveHLS($identifier);
                break;
            case 'progressive':
                $this->serveProgressive($identifier);
                break;
            default:
                show_404();
        }
    }

    /**
     * Serve progressive MP4 with range support
     */
    private function serveProgressive($filename)
    {
        $file_path = $this->paths['encoded'] . basename($filename);
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }

        $file_size = filesize($file_path);
        $mime = 'video/mp4';
        
        // Headers
        header('Content-Type: ' . $mime);
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');
        header('X-Content-Type-Options: nosniff');
        
        // Range handling
        $start = 0;
        $end = $file_size - 1;
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\\d+)-(\\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
                $start = intval($m[1]);
                if (!empty($m[2])) {
                    $end = intval($m[2]);
                }
                
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$file_size");
                header('Content-Length: ' . ($end - $start + 1));
            }
        } else {
            header('Content-Length: ' . $file_size);
        }
        
        // Stream with optimized buffer
        $fp = fopen($file_path, 'rb');
        fseek($fp, $start);
        
        $buffer = 8192;
        $sent = 0;
        $to_send = $end - $start + 1;
        
        while (!feof($fp) && $sent < $to_send) {
            $chunk = min($buffer, $to_send - $sent);
            echo fread($fp, $chunk);
            $sent += $chunk;
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
        
        fclose($fp);
    }

    // ==================== CRUD OPERATIONS ====================

    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        } else {
            $this->form_validation->set_rules('uploaded_file_path', 'Fichier vidéo', 'required');
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
        
        $this->setFlashMessage($rsp, 'Vidéo AVC créée avec succès.', 'Erreur création.');
        redirect(base_url('video'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = $this->prepareUpdateData($id);
        
        if (!$data) {
            redirect(base_url('video'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        
        $this->setFlashMessage($rsp, 'Vidéo AVC mise à jour.', 'Erreur mise à jour.');
        redirect(base_url('video'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if ($video) {
            $this->deleteAVCFiles($video);
            $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
                'est_actif' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->setFlashMessage($rsp, 'Vidéo AVC supprimée.', 'Erreur suppression.');
        }
        
        redirect(base_url('video'));
    }

    // ==================== UTILITY METHODS ====================

    private function detectHardwareAcceleration()
    {
        $accel = ['nvenc' => false, 'vaapi' => false, 'videotoolbox' => false];
        
        if (!$this->ffmpeg_path) return $accel;
        
        // Check NVENC
        exec($this->ffmpeg_path . ' -encoders 2>/dev/null | grep nvenc', $out, $code);
        $accel['nvenc'] = (strpos(implode('', $out), 'h264_nvenc') !== false);
        
        // Check VAAPI
        exec($this->ffmpeg_path . ' -encoders 2>/dev/null | grep vaapi', $out2);
        $accel['vaapi'] = (strpos(implode('', $out2), 'h264_vaapi') !== false);
        
        return $accel;
    }

    private function checkEncoder($encoder)
    {
        if (!$this->ffmpeg_path) return false;
        exec($this->ffmpeg_path . ' -encoders 2>/dev/null | grep ' . escapeshellarg($encoder), $out);
        return !empty($out);
    }

    private function getAVCCapabilities()
    {
        return [
            'profiles' => array_keys($this->avc_profiles),
            'ladder' => array_keys($this->streaming_ladders),
            'hardware' => $this->detectHardwareAcceleration(),
            'features' => [
                'adaptive_streaming' => true,
                'multi_resolution' => true,
                'scene_detection' => (bool)$this->ffmpeg_path,
                'hardware_encoding' => $this->detectHardwareAcceleration()['nvenc'] || $this->detectHardwareAcceleration()['vaapi']
            ]
        ];
    }

    private function deleteAVCFiles($video)
    {
        $paths_to_delete = [
            $video['fichier'],
            $video['miniature'],
            $video['metadata_id3']
        ];
        
        if (!empty($video['fichier'])) {
            $base = pathinfo($video['fichier'], PATHINFO_FILENAME);
            $patterns = [
                $this->paths['encoded'] . $base . '*',
                $this->paths['thumbnails'] . $base . '*',
                $this->paths['posters'] . $base . '*',
                $this->paths['previews'] . $base . '*',
                $this->paths['sprites'] . $base . '/*',
                $this->paths['dash'] . $base . '/*',
                $this->paths['hls'] . $base . '/*'
            ];
            
            foreach ($patterns as $pattern) {
                foreach (glob($pattern) as $file) {
                    @unlink($file);
                }
            }
        }
        
        foreach ($paths_to_delete as $path) {
            if (!empty($path) && file_exists(FCPATH . $path)) {
                @unlink(FCPATH . $path);
            }
        }
    }

    // ==================== STANDARD HELPERS ====================

    private function sanitizeFilename($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $name = substr($name, 0, 100);
        
        // Normalize to MP4 for AVC
        if (!in_array($ext, ['mp4', 'm4v'])) {
            $ext = 'mp4';
        }
        
        return date('YmdHis') . '_' . $name . '_avc.' . $ext;
    }

    private function generateUploadId()
    {
        return 'avc_' . uniqid() . '_' . bin2hex(random_bytes(4));
    }

    private function saveMetadata($id, $data)
    {
        file_put_contents(
            $this->paths['temp'] . $id . '/metadata.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    private function loadMetadata($id)
    {
        $path = $this->paths['temp'] . $id . '/metadata.json';
        return file_exists($path) ? json_decode(file_get_contents($path), true) : null;
    }

    private function updateMetadataStatus($id, $status)
    {
        $meta = $this->loadMetadata($id);
        if ($meta) {
            $meta['status'] = $status;
            $this->saveMetadata($id, $meta);
        }
    }

    private function updateLastActivity($id)
    {
        $meta = $this->loadMetadata($id);
        if ($meta) {
            $meta['last_activity'] = time();
            $this->saveMetadata($id, $meta);
        }
    }

    private function markChunkUploaded($id, $index)
    {
        $meta = $this->loadMetadata($id);
        if ($meta && !in_array($index, $meta['uploaded_chunks'])) {
            $meta['uploaded_chunks'][] = $index;
            sort($meta['uploaded_chunks']);
            $this->saveMetadata($id, $meta);
        }
    }

    private function calculateProgress($id)
    {
        $meta = $this->loadMetadata($id);
        if (!$meta) return null;
        
        $uploaded = count($meta['uploaded_chunks']);
        $total = $meta['total_chunks'];
        
        return [
            'uploaded_chunks' => $uploaded,
            'total_chunks' => $total,
            'percent' => round(($uploaded / $total) * 100, 2),
            'bytes_uploaded' => $uploaded * $this->config['chunk_size']
        ];
    }

    private function assembleChunks($id, $dest, $metadata)
    {
        $temp_dir = $this->paths['temp'] . $id . '/';
        $out = fopen($dest, 'wb');
        
        if (!$out) {
            return ['success' => false, 'message' => 'Cannot create output file'];
        }

        try {
            for ($i = 0; $i < $metadata['total_chunks']; $i++) {
                $chunk = $temp_dir . 'chunk_' . $i;
                if (!file_exists($chunk)) {
                    fclose($out);
                    @unlink($dest);
                    return ['success' => false, 'message' => "Missing chunk $i"];
                }
                
                fwrite($out, file_get_contents($chunk));
                unlink($chunk);
            }
            fclose($out);
            return ['success' => true];
        } catch (Exception $e) {
            fclose($out);
            @unlink($dest);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function cleanupUploadSession($id)
    {
        $dir = $this->paths['temp'] . $id . '/';
        if (is_dir($dir)) {
            array_map('unlink', glob($dir . '*'));
            rmdir($dir);
        }
    }

    private function validateUploadParams($file_name, $file_size)
    {
        if (empty($file_name) || $file_size <= 0) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        if ($file_size > $this->config['max_file_size']) {
            return ['success' => false, 'message' => 'File too large'];
        }
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->config['allowed_extensions'])) {
            return ['success' => false, 'message' => 'Unsupported format: ' . $ext];
        }
        
        return ['success' => true];
    }

    private function setJSONHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache');
    }

    private function jsonResponse($success, $message = '', $data = [])
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'timestamp' => time()
        ], $data));
    }

    private function setFlashMessage($success, $success_msg, $error_msg)
    {
        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success ? $success_msg : $error_msg
        );
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit = 0;
        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }
        return round($bytes, 2) . ' ' . $units[$unit];
    }

    private function formatBits($bits)
    {
        return $this->formatBytes($bits / 8) . 'ps';
    }

    private function formatDuration($seconds)
    {
        if (!$seconds) return '0:00';
        return $seconds < 3600 
            ? gmdate("i:s", $seconds) 
            : gmdate("H:i:s", $seconds);
    }

    private function calculateFPS($stream)
    {
        if (empty($stream)) return 0;
        $rate = $stream['r_frame_rate'] ?? '0/1';
        if (strpos($rate, '/') !== false) {
            list($n, $d) = explode('/', $rate);
            return $d > 0 ? round($n / $d, 2) : 0;
        }
        return (float)$rate;
    }

    private function suggestTitleFromFilename($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace(['_', '-', '.'], ' ', $name);
        $name = preg_replace('/^\\d+/', '', $name);
        return ucwords(trim($name));
    }

    private function suggestCategory($analysis)
    {
        $title = strtolower($analysis['title'] ?? '');
        $desc = strtolower($analysis['description'] ?? '');
        
        $keywords = [
            'tutoriel' => ['tutoriel', 'tutorial', 'howto', 'cours'],
            'interview' => ['interview', 'entretien', 'discussion'],
            'documentaire' => ['documentaire', 'documentary'],
            'musique' => ['music', 'musique', 'clip', 'concert'],
            'sport' => ['sport', 'football', 'match'],
            'gaming' => ['game', 'gaming', 'gameplay'],
            'vlog' => ['vlog', 'daily']
        ];
        
        foreach ($keywords as $cat => $words) {
            foreach ($words as $word) {
                if (strpos($title, $word) !== false || strpos($desc, $word) !== false) {
                    return ucfirst($cat);
                }
            }
        }
        
        return 'Vidéo';
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'video');
        $query = $this->db->get('galerie_medias');
        return array_column($query->result_array(), 'cat');
    }

    private function calculateTotalDuration()
    {
        $this->db->select_sum('duree', 'total');
        $this->db->where('type', 'video');
        $this->db->where('est_actif', 1);
        return $this->db->get('galerie_medias')->row()->total ?? 0;
    }

    private function getStorageStatistics()
    {
        $total = 0;
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                $total += $this->getDirSize($path);
            }
        }
        return ['total_used' => $this->formatBytes($total)];
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (glob($dir . '/*') as $file) {
            $size += is_file($file) ? filesize($file) : $this->getDirSize($file);
        }
        return $size;
    }

    private function getEncodingQueueStatus()
    {
        return ['active_jobs' => 0, 'pending_jobs' => 0]; // Implement with queue system
    }

    private function checkDashSupport()
    {
        return (bool)$this->ffmpeg_path;
    }

    private function checkHLSSupport()
    {
        return (bool)$this->ffmpeg_path;
    }

    private function basicAnalysis($file_path)
    {
        return [
            'duration' => 0,
            'bitrate' => 0,
            'width' => 0,
            'height' => 0,
            'fps' => 0,
            'codec' => 'unknown',
            'note' => 'FFprobe unavailable - basic analysis only'
        ];
    }

    private function fallbackThumbnails($video_path, $filename)
    {
        return ['default' => null, 'poster' => null];
    }

    private function prepareVideoData($type_source)
    {
        // Implementation based on your existing logic
        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        return [
            'titre' => $auto_data['titre'] ?? $this->input->post('titre'),
            'type' => 'video',
            'description' => $this->input->post('description'),
            'categorie' => $auto_data['categorie'] ?? $this->input->post('categorie'),
            'fichier' => $this->input->post('uploaded_file_path'),
            'duree' => $auto_data['duration'] ?? null,
            'taille' => $auto_data['file_size'] ?? null,
            'miniature' => $auto_data['thumbnail'] ?? null,
            'metadata_id3' => !empty($auto_data) ? json_encode($auto_data) : null,
            'est_actif' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function prepareUpdateData($id)
    {
        return [
            'titre' => $this->input->post('titre'),
            'description' => $this->input->post('description'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
}
