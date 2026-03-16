<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Nettoyer tous les buffers
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        
        echo json_encode([
            'success' => true,
            'message' => 'API Test fonctionne',
            'time' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
    }
}