<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MLService - Intégration PHP du service ML Python
 * 
 * Appelle le service FastAPI pour la détection de langue et d'intention.
 * Fallback automatique vers le système PHP si le service est indisponible.
 * 
 * @author  NUFOTEC Chatbot Team
 * @version 1.0
 */
class MLService
{
    /**
     * URL du service ML Python
     */
    protected $ml_url = 'http://127.0.0.1:8001';
    
    /**
     * Token d'authentification
     */
    protected $ml_token = '';
    
    /**
     * Timeout en secondes
     */
    protected $timeout = 3;
    
    /**
     * Mode debug
     */
    protected $debug = false;
    
    /**
     * CI instance
     */
    protected $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        
        // Charger la config
        $this->CI->load->library('session');
        
        // Configurer le service ML
        $host = getenv('ML_HOST') ?: '127.0.0.1';
        $port = getenv('ML_PORT') ?: '8001';
        $this->ml_url = "http://{$host}:{$port}";
        
        $this->ml_token = getenv('ML_SERVICE_TOKEN') ?: 'nufotec-ml-secret-token-change-me';
        
        $this->debug = (getenv('ML_DEBUG') ?: 'false') === 'true';
    }
    
    /**
     * Appelle le service ML pour la détection de langue et d'intention
     * 
     * @param string $text Texte à analyser
     * @param string|null $previous_language Langue précédente de la conversation
     * @return array ['language' => 'rn', 'language_confidence' => 0.94, 'intent' => 'greeting', 'intent_confidence' => 0.89, 'low_confidence' => false]
     */
    public function predict($text, $previous_language = null)
    {
        $start = microtime(true);
        
        try {
            $result = $this->_call_ml('/predict', array(
                'text' => $text,
                'previous_language' => $previous_language,
            ));
            
            $elapsed_ms = (microtime(true) - $start) * 1000;
            
            if ($this->debug) {
                log_message('info', "[ML] Prédiction en {$elapsed_ms}ms: lang={$result['language']} ({$result['language_confidence']}), intent={$result['intent']} ({$result['intent_confidence']})");
            }
            
            return array(
                'language'           => $result['language'] ?? 'rn',
                'language_confidence' => (float) ($result['language_confidence'] ?? 0.30),
                'intent'             => $result['intent'] ?? 'unknown',
                'intent_confidence'  => (float) ($result['intent_confidence'] ?? 0.30),
                'low_confidence'     => (bool) ($result['low_confidence'] ?? true),
                'processing_time_ms' => round($elapsed_ms, 2),
                'source'             => 'ml_service',
            );
            
        } catch (Exception $e) {
            $elapsed_ms = (microtime(true) - $start) * 1000;
            
            log_message('error', "[ML] Erreur prédiction ({$elapsed_ms}ms): " . $e->getMessage());
            
            // Fallback: retourner un résultat indiquant qu'il faut utiliser le PHP
            return array(
                'language'           => null,
                'language_confidence' => 0,
                'intent'             => null,
                'intent_confidence'  => 0,
                'low_confidence'     => true,
                'processing_time_ms' => round($elapsed_ms, 2),
                'source'             => 'fallback_needed',
                'error'              => $e->getMessage(),
            );
        }
    }
    
    /**
     * Vérifie si le service ML est disponible
     * 
     * @return bool
     */
    public function is_available()
    {
        try {
            $result = $this->_call_ml('/health', array(), 'GET');
            return isset($result['status']) && $result['status'] === 'ok';
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Vérifie si les modèles sont chargés
     * 
     * @return bool
     */
    public function are_models_loaded()
    {
        try {
            $result = $this->_call_ml('/health', array(), 'GET');
            return !empty($result['model_loaded']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Appelle un endpoint du service ML
     * 
     * @param string $endpoint Endpoint (ex: /predict, /health)
     * @param array $data Données à envoyer (pour POST)
     * @param string $method Méthode HTTP (GET ou POST)
     * @return array Réponse décodée
     * @throws Exception En cas d'erreur
     */
    protected function _call_ml($endpoint, $data = array(), $method = 'POST')
    {
        $url = $this->ml_url . $endpoint;
        
        $ch = curl_init();
        
        // Options de base
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        
        // Headers
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        
        if ($this->ml_token) {
            $headers[] = 'Authorization: Bearer ' . $this->ml_token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Méthode
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        // Exécuter
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        
        curl_close($ch);
        
        // Vérifier les erreurs cURL
        if ($errno !== 0) {
            throw new Exception("Erreur cURL ({$errno}): {$error}");
        }
        
        // Vérifier le code HTTP
        if ($http_code !== 200) {
            throw new Exception("Erreur HTTP {$http_code}: {$response}");
        }
        
        // Décoder le JSON
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON invalide: " . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Retourne le statut du service pour le débogage
     * 
     * @return array
     */
    public function get_status()
    {
        return array(
            'ml_url'           => $this->ml_url,
            'timeout'          => $this->timeout,
            'available'        => $this->is_available(),
            'models_loaded'    => $this->are_models_loaded(),
        );
    }
}
