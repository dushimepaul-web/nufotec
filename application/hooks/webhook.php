<?php
class WebhookHook {
    
    public function handle() {
        // Vérifier si c'est une requête webhook
        if (strpos($_SERVER['REQUEST_URI'], 'webhook') !== false) {
            // Désactiver le CSRF pour le webhook
            $CI =& get_instance();
            $CI->config->set_item('csrf_protection', FALSE);
        }
    }
}