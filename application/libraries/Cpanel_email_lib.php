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
        $subject = '🔐 Code de vérification - NUFOTEC';
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Code de vérification NUFOTEC</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    background: #f4f6f9; 
                    margin: 0; 
                    padding: 20px; 
                }
                .container { 
                    max-width: 520px; 
                    margin: 0 auto; 
                    background: white; 
                    border-radius: 16px; 
                    padding: 40px; 
                    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                }
                .logo { text-align: center; margin-bottom: 30px; }
                .logo h1 { color: #0a2540; font-size: 24px; }
                .code { 
                    font-size: 42px; 
                    font-weight: bold; 
                    color: #0a66c2; 
                    text-align: center; 
                    letter-spacing: 10px; 
                    margin: 30px 0; 
                    font-family: monospace;
                    background: #f0f7ff;
                    padding: 20px;
                    border-radius: 12px;
                }
                .info { text-align: center; color: #6c757d; margin: 20px 0; font-size: 14px; }
                .footer { margin-top: 30px; font-size: 12px; color: #8a99b0; text-align: center; border-top: 1px solid #e9ecef; padding-top: 20px; }
                .btn { 
                    background: #0a66c2; 
                    color: white; 
                    padding: 12px 24px; 
                    text-decoration: none; 
                    border-radius: 8px; 
                    display: inline-block;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="logo">
                    <h1>🏥 NUFOTEC BURUNDI</h1>
                </div>
                
                <h2 style="color:#0a2540; margin-bottom: 15px;">Bonjour ' . htmlspecialchars($user_name) . ',</h2>
                
                <p style="margin-bottom: 15px; color: #334155;">Vous avez demandé ' . ($type == 'reset' ? 'la réinitialisation de votre mot de passe' : 'la vérification de votre compte') . '.</p>
                
                <p style="margin-bottom: 10px; color: #334155;">Voici votre code de vérification :</p>
                
                <div class="code">' . $otp_code . '</div>
                
                <p class="info">⏰ Ce code est valable pendant <strong>15 minutes</strong>.</p>
                
                <p class="info">🔒 Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e9ecef;">
                
                <div class="footer">
                    &copy; ' . date('Y') . ' NUFOTEC Burundi. Tous droits réservés.<br>
                    <a href="' . base_url() . '" style="color:#0a66c2; text-decoration:none;">' . base_url() . '</a>
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