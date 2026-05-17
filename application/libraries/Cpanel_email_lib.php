<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpanel_email_lib {
    
    protected $CI;
    protected $from_email;
    protected $from_name;
    
    public function __construct() {
        $this->CI =& get_instance();
        
        // Configurer l'email expéditeur
        $domain = $_SERVER['HTTP_HOST'];
        $domain = str_replace('www.', '', $domain);
        $this->from_email = $this->CI->config->item('cpanel_from_email') ?: 'info@' . $domain;
        $this->from_name = $this->CI->config->item('cpanel_from_name') ?: 'NUFOTEC BURUNDI';
        
        // Charger la librairie email
        $this->CI->load->library('email');
    }
    
    public function send_email($to, $subject, $message) {
        // Configuration pour sendmail (standard sur cPanel)
        $config = array(
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail',
            'charset' => 'utf-8',
            'mailtype' => 'html',
            'newline' => "\r\n",
            'crlf' => "\r\n",
            'wordwrap' => TRUE
        );
        
        $this->CI->email->initialize($config);
        $this->CI->email->clear(); // Important: nettoie les données précédentes
        $this->CI->email->from($this->from_email, $this->from_name);
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($message);
        
        // Tentative d'envoi
        if ($this->CI->email->send()) {
            log_message('info', 'Email sent successfully to: ' . $to);
            return ['success' => true, 'status' => 200];
        } else {
            $error = $this->CI->email->print_debugger(['headers']);
            log_message('error', 'Email error to ' . $to . ': ' . $error);
            return ['success' => false, 'message' => $error];
        }
    }
    
   public function send_otp_code($to, $user_name, $otp_code, $type = 'reset') {
    $subject = 'Code de vérification - NUFOTEC';
    
    // Récupérer les informations du site
    $site_logo = $this->CI->Model->get_setting('site_logo');
    $site_name = $this->CI->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
    $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($site_name) . ' - Code de vérification</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #f5f7fa;
                margin: 0;
                padding: 40px 20px;
                line-height: 1.6;
            }
            
            .email-container {
                max-width: 560px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            
            .email-header {
                background-color: #ffffff;
                padding: 32px 32px 24px;
                text-align: center;
                border-bottom: 1px solid #eef2f6;
            }
            
            .logo {
                max-height: 60px;
                width: auto;
                margin-bottom: 16px;
            }
            
            .company-name {
                font-size: 20px;
                font-weight: 600;
                color: #1a2a3a;
                letter-spacing: -0.3px;
            }
            
            .email-body {
                padding: 32px;
            }
            
            .greeting {
                font-size: 24px;
                font-weight: 600;
                color: #1a2a3a;
                margin-bottom: 16px;
                letter-spacing: -0.5px;
            }
            
            .message-text {
                color: #4a5a6a;
                font-size: 16px;
                margin-bottom: 24px;
            }
            
            .code-container {
                background-color: #f7f9fc;
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                margin: 24px 0;
                border: 1px solid #eef2f6;
            }
            
            .code-label {
                font-size: 14px;
                color: #6b7a8a;
                margin-bottom: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .verification-code {
                font-size: 48px;
                font-weight: 700;
                color: #0a66c2;
                letter-spacing: 12px;
                font-family: "Courier New", monospace;
                background: #ffffff;
                padding: 16px 24px;
                border-radius: 8px;
                display: inline-block;
                border: 1px solid #e0e7ed;
            }
            
            .expiry-info {
                font-size: 13px;
                color: #8a9aaa;
                margin-top: 16px;
            }
            
            .warning-text {
                background-color: #fef8e7;
                border-left: 3px solid #f5a623;
                padding: 12px 16px;
                font-size: 13px;
                color: #8a6d3b;
                margin: 24px 0;
                border-radius: 6px;
            }
            
            .email-footer {
                background-color: #f8fafc;
                padding: 24px 32px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            
            .footer-text {
                font-size: 12px;
                color: #8a9aaa;
                margin-bottom: 12px;
            }
            
            .footer-links {
                margin-top: 16px;
            }
            
            .footer-links a {
                color: #6b7a8a;
                text-decoration: none;
                font-size: 12px;
                margin: 0 8px;
            }
            
            .footer-links a:hover {
                color: #0a66c2;
                text-decoration: underline;
            }
            
            .footer-logo {
                max-height: 35px;
                width: auto;
                margin-top: 16px;
                opacity: 0.6;
            }
            
            hr {
                border: none;
                border-top: 1px solid #eef2f6;
                margin: 16px 0;
            }
            
            @media (max-width: 600px) {
                body {
                    padding: 20px 12px;
                }
                .email-body {
                    padding: 24px 20px;
                }
                .email-header {
                    padding: 24px 20px;
                }
                .verification-code {
                    font-size: 36px;
                    letter-spacing: 8px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">';
    
    // Ajouter le logo s'il existe
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    } else {
        $message .= '
                <div class="company-name">' . htmlspecialchars($site_name) . '</div>';
    }
    
    $message .= '
            </div>
            
            <div class="email-body">
                <div class="greeting">Bonjour ' . htmlspecialchars($user_name) . ',</div>
                
                <div class="message-text">
                    Vous avez demandé ' . ($type == 'reset' ? 'la réinitialisation de votre mot de passe' : 'la vérification de votre compte') . '.
                </div>
                
                <div class="code-container">
                    <div class="code-label">Votre code de vérification</div>
                    <div class="verification-code">' . $otp_code . '</div>
                    <div class="expiry-info">Ce code expire dans 15 minutes</div>
                </div>
                
                <div class="warning-text">
                    Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer cet email. 
                    Aucune modification ne sera apportée à votre compte.
                </div>
            </div>
            
            <div class="email-footer">
                <div class="footer-text">
                    &copy; ' . date('Y') . ' ' . htmlspecialchars($site_name) . '. Tous droits réservés.
                </div>
                <div class="footer-links">
                    <a href="' . base_url() . '">Accueil</a>';
    
    if (!empty($logo_url)) {
        $message .= '
                    <br>
                    <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="footer-logo">';
    }
    
    $message .= '
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    return $this->send_email($to, $subject, $message);
}




public function send_verification_code($to, $user_name, $otp_code) {
    $subject = 'Vérifiez votre adresse email - NUFOTEC';
    
    // Récupérer les informations du site
    $site_logo = $this->CI->Model->get_setting('site_logo');
    $site_name = $this->CI->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
    $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vérification email - ' . htmlspecialchars($site_name) . '</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #f5f7fa;
                margin: 0;
                padding: 40px 20px;
                line-height: 1.6;
            }
            .email-container {
                max-width: 560px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            .email-header {
                background-color: #ffffff;
                padding: 32px 32px 24px;
                text-align: center;
                border-bottom: 1px solid #eef2f6;
            }
            .logo {
                max-height: 60px;
                width: auto;
                margin-bottom: 16px;
            }
            .email-body {
                padding: 32px;
            }
            .greeting {
                font-size: 24px;
                font-weight: 600;
                color: #1a2a3a;
                margin-bottom: 16px;
            }
            .code-container {
                background-color: #f7f9fc;
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                margin: 24px 0;
                border: 1px solid #eef2f6;
            }
            .verification-code {
                font-size: 48px;
                font-weight: 700;
                color: #0a66c2;
                letter-spacing: 12px;
                font-family: "Courier New", monospace;
                background: #ffffff;
                padding: 16px 24px;
                border-radius: 8px;
                display: inline-block;
                border: 1px solid #e0e7ed;
            }
            .expiry-info {
                font-size: 13px;
                color: #8a9aaa;
                margin-top: 16px;
            }
            .email-footer {
                background-color: #f8fafc;
                padding: 24px 32px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            .footer-text {
                font-size: 12px;
                color: #8a9aaa;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">';
    
    if (!empty($logo_url)) {
        $message .= '<img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    }
    
    $message .= '
            </div>
            
            <div class="email-body">
                <div class="greeting">Bienvenue ' . htmlspecialchars($user_name) . ',</div>
                
                <p style="margin-bottom: 16px; color: #4a5a6a;">
                    Merci de vous être inscrit sur ' . htmlspecialchars($site_name) . '. 
                    Pour activer votre compte, veuillez confirmer votre adresse email.
                </p>
                
                <div class="code-container">
                    <div style="font-size: 14px; color: #6b7a8a; margin-bottom: 12px;">Votre code de vérification</div>
                    <div class="verification-code">' . $otp_code . '</div>
                    <div class="expiry-info">Ce code expire dans 15 minutes</div>
                </div>
                
                <p style="color: #4a5a6a; font-size: 14px; margin-top: 16px;">
                    Une fois votre email vérifié, vous pourrez vous connecter et profiter de tous nos services.
                </p>
            </div>
            
            <div class="email-footer">
                <div class="footer-text">
                    &copy; ' . date('Y') . ' ' . htmlspecialchars($site_name) . '. Tous droits réservés.
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    return $this->send_email($to, $subject, $message);
}



    
    // Vérifier si l'email est valide
    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Tester la configuration email
    public function test_email($to) {
        $test_subject = 'Test de configuration NUFOTEC';
        $test_message = '<h2>✅ Test réussi !</h2><p>Votre serveur email est correctement configuré.</p>';
        return $this->send_email($to, $test_subject, $test_message);
    }
}