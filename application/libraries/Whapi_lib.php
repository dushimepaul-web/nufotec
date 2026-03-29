<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whapi_lib {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    
    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('whapi');
        
        $config = $CI->config->item('whapi');
        $this->api_key = $config['api_key'];
        $this->base_url = $config['base_url'];
        $this->timeout = $config['timeout'] ?? 30;
        $this->debug = $config['debug'] ?? true;
    }
    
    /**
     * Récupère la liste des groupes WhatsApp
     */
    public function get_groupes() {
        $url = $this->base_url . '/groups';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Envoie un message à un groupe spécifique
     */
    public function envoyer_message_groupe($groupe_id, $message) {
        $url = $this->base_url . '/messages/text';
        
        $payload = array(
            'to' => $groupe_id,
            'body' => $message
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie un message à plusieurs groupes
     */
    public function envoyer_message_multigroupes($groupes_ids, $message, $delai_ms = 1000) {
        $resultats = array();
        $total = count($groupes_ids);
        $reussis = 0;
        $echoues = 0;
        
        foreach ($groupes_ids as $index => $groupe_id) {
            $resultat = $this->envoyer_message_groupe($groupe_id, $message);
            
            $statut = $resultat['success'] ? 'succès' : 'échec';
            if ($resultat['success']) {
                $reussis++;
            } else {
                $echoues++;
            }
            
            $resultats[] = array(
                'groupe_id' => $groupe_id,
                'statut' => $statut,
                'reponse' => $resultat,
                'index' => $index + 1
            );
            
            // Attendre avant d'envoyer le prochain message
            if ($index < $total - 1) {
                usleep($delai_ms * 1000);
            }
        }
        
        return array(
            'total' => $total,
            'reussis' => $reussis,
            'echoues' => $echoues,
            'details' => $resultats
        );
    }
    
    /**
     * Exécute une requête API
     */
    private function requete_api($method, $url, $data = null) {
        $ch = curl_init();
        
        $headers = array(
            'accept: application/json',
            'authorization: Bearer ' . $this->api_key,
            'content-type: application/json'
        );
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        $resultat = array(
            'success' => ($http_code >= 200 && $http_code < 300),
            'status_code' => $http_code,
            'response' => json_decode($response, true)
        );
        
        if ($error) {
            $resultat['error'] = $error;
            $resultat['success'] = false;
        }
        
        if ($this->debug) {
            log_message('debug', 'Whapi request: ' . $method . ' ' . $url);
            log_message('debug', 'Whapi response status: ' . $http_code);
        }
        
        return $resultat;
    }


    public function test_connexion() {
    $url = $this->base_url . '/health';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $this->api_key
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return array(
        'success' => ($http_code == 200),
        'status_code' => $http_code,
        'response' => json_decode($response, true),
        'error' => $error
    );
}
}