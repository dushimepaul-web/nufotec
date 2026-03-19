<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MX_Controller {
    
    public function upload() {
        // Désactiver toute mise en page
        $this->output->set_content_type('text/plain');
        
        echo "=== DEBUG UPLOAD API ===\n\n";
        
        // 1. Vérifier la méthode
        echo "1. REQUEST METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
        
        // 2. Vérifier les POST data
        echo "2. POST DATA:\n";
        print_r($_POST);
        
        // 3. Vérifier les FILES
        echo "\n3. FILES DATA:\n";
        print_r($_FILES);
        
        // 4. Vérifier si CSRF est valide
        echo "\n4. CSRF CHECK:\n";
        echo "Token name: " . $this->security->get_csrf_token_name() . "\n";
        echo "Token value: " . $this->security->get_csrf_hash() . "\n";
        echo "Post token: " . ($_POST[$this->security->get_csrf_token_name()] ?? 'NOT SET') . "\n";
        
        // 5. Tester si on peut charger le modèle Video
        echo "\n5. CONTROLLER TEST:\n";
        try {
            $this->load->model('media/Model_media', 'Model');
            echo "Model loaded OK\n";
        } catch(Exception $e) {
            echo "Model error: " . $e->getMessage() . "\n";
        }
        
        // 6. Tester l'appel à initUpload directement
        echo "\n6. CALLING initUpload:\n";
        
        // Simuler les données POST
        $_POST['file_name'] = 'test.mp4';
        $_POST['file_size'] = '1024';
        $_POST['file_hash'] = 'abc123';
        
        // Capturer la sortie
        ob_start();
        
        // Appeler la méthode du contrôleur Video
        $VC = modules::load('media/video');
        $VC->initUpload();
        
        $output = ob_get_clean();
        echo "Output captured: " . substr($output, 0, 500) . "\n";
    }
}