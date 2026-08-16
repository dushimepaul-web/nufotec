<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpanel_email_lib {
    
    protected $CI;
    protected $from_email;
    protected $from_name;
    
    public function __construct() {
        $this->CI =& get_instance();
        
        // Configurer l'email expÃ©diteur
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
        $this->CI->email->clear(); // Important: nettoie les donnÃ©es prÃ©cÃ©dentes
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
    

    
public function send_otp_code($to, $user_name, $otp_code, $type = 'reset')
{
    $subject = 'Code de vÃ©rification - NUFOTEC';

    // Informations du site
    $site_logo = $this->CI->Model->get_setting('site_logo');
    $site_name = $this->CI->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
    $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';

    $message = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($site_name) . ' - Code de vÃ©rification</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 20px;
                line-height: 1.5;
            }
            .container {
                max-width: 520px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            .header {
                background: #0a2540;
                padding: 32px 24px;
                text-align: center;
            }
            .logo {
                max-height: 55px;
                width: auto;
                margin-bottom: 12px;
            }
            .site-title {
                color: #ffffff;
                font-size: 20px;
                font-weight: 600;
                letter-spacing: -0.3px;
            }
            .content {
                padding: 32px 28px;
            }
            .greeting {
                font-size: 24px;
                font-weight: 700;
                color: #1a2a3a;
                margin-bottom: 12px;
            }
            .message-text {
                color: #5a6a7a;
                font-size: 15px;
                margin-bottom: 28px;
                line-height: 1.6;
            }
            .code-box {
                background: #f7f9fc;
                border-radius: 14px;
                padding: 28px 20px;
                text-align: center;
                margin: 24px 0;
                border: 1px solid #e8ecf0;
            }
            .code-label {
                font-size: 13px;
                color: #8a9aaa;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                font-weight: 600;
                margin-bottom: 16px;
            }
            .code {
                font-size: 48px;
                font-weight: 800;
                color: #0a66c2;
                letter-spacing: 12px;
                font-family: "Courier New", monospace;
                background: #ffffff;
                padding: 16px 20px;
                border-radius: 12px;
                display: inline-block;
                border: 1px solid #e0e7ed;
            }
            .expiry {
                font-size: 13px;
                color: #8a9aaa;
                margin-top: 16px;
            }
            .warning {
                background: #fef8e7;
                border-left: 3px solid #f5a623;
                padding: 14px 18px;
                font-size: 13px;
                color: #856404;
                margin-top: 24px;
                border-radius: 8px;
            }
            .footer {
                background: #f8fafc;
                padding: 24px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            .footer-text {
                font-size: 12px;
                color: #9aaab9;
            }
            .footer-logo {
                max-height: 32px;
                width: auto;
                margin-top: 12px;
                opacity: 0.5;
            }
            @media (max-width: 560px) {
                body {
                    padding: 12px;
                }
                .content {
                    padding: 24px 20px;
                }
                .header {
                    padding: 28px 20px;
                }
                .greeting {
                    font-size: 22px;
                }
                .code {
                    font-size: 36px;
                    letter-spacing: 8px;
                    padding: 12px 16px;
                }
                .message-text {
                    font-size: 14px;
                }
            }
            @media (max-width: 420px) {
                .code {
                    font-size: 28px;
                    letter-spacing: 5px;
                    padding: 12px 12px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">';
    
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    }
    
    $message .= '
                <div class="site-title">' . htmlspecialchars($site_name) . '</div>
            </div>
            
            <div class="content">
                <div class="greeting">Bonjour ' . htmlspecialchars($user_name) . '</div>
                
                <div class="message-text">';
    
    if ($type == 'reset') {
        $message .= 'Nous avons reÃ§u une demande de rÃ©initialisation de votre mot de passe.';
    } else {
        $message .= 'Merci de confirmer votre adresse email.';
    }
    
    $message .= '</div>
                
                <div class="code-box">
                    <div class="code-label">CODE DE VÃ‰RIFICATION</div>
                    <div class="code">' . $otp_code . '</div>
                    <div class="expiry">Ce code expire dans 15 minutes</div>
                </div>
                
                <div class="warning">
                    Si vous n\'Ãªtes pas Ã  l\'origine de cette demande, ignorez cet email. Aucune modification ne sera apportÃ©e Ã  votre compte.
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-text">Â© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits rÃ©servÃ©s</div>';
    
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="footer-logo">';
    }
    
    $message .= '
            </div>
        </div>
    </body>
    </html>';
    
    return $this->send_email($to, $subject, $message);
}





    
    // VÃ©rifier si l'email est valide
    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Tester la configuration email
    public function test_email($to) {
        $test_subject = 'Test de configuration NUFOTEC';
        $test_message = '<h2>âœ… Test rÃ©ussi !</h2><p>Votre serveur email est correctement configurÃ©.</p>';
        return $this->send_email($to, $test_subject, $test_message);
    }
}