<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use SendGrid\Mail\Mail;
use Twilio\Rest\Client as TwilioClient;

class Sendgrid_lib {

    protected $CI;
    protected $sendgrid;
    protected $twilio_sid;
    protected $twilio_token;
    protected $twilio_from;
    protected $is_sendgrid_configured = false;
    protected $is_twilio_configured = false;
    protected $last_error = null;

    public function __construct() {
        $this->CI =& get_instance();
        
        // -------- SendGrid Configuration --------
        $apiKey = $this->CI->config->item('sendgrid_api_key');
        
        if (!empty($apiKey) && $apiKey != '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') {
            try {
                $this->sendgrid = new \SendGrid($apiKey);
                $this->is_sendgrid_configured = true;
                // Remplacer log_message par error_log pour éviter l'erreur
                error_log('SendGrid initialized successfully');
            } catch (Exception $e) {
                error_log('SendGrid initialization error: ' . $e->getMessage());
                $this->last_error = $e->getMessage();
                $this->is_sendgrid_configured = false;
            }
        } else {
            error_log('SendGrid API key not configured or using default value');
            $this->is_sendgrid_configured = false;
        }

        // -------- Twilio WhatsApp Configuration --------
        $this->twilio_sid   = $this->CI->config->item('twilio_sid');
        $this->twilio_token = $this->CI->config->item('twilio_token');
        $this->twilio_from  = $this->CI->config->item('twilio_whatsapp_from');
        
        if (!empty($this->twilio_sid) && !empty($this->twilio_token) && 
            $this->twilio_sid != 'VOTRE_TWILIO_SID' && $this->twilio_token != 'VOTRE_TWILIO_TOKEN') {
            $this->is_twilio_configured = true;
        } else {
            error_log('Twilio not configured');
        }
    }

    // -------- Vérifier si SendGrid est configuré --------
    public function is_sendgrid_configured() {
        return $this->is_sendgrid_configured;
    }

    // -------- Vérifier si Twilio est configuré --------
    public function is_twilio_configured() {
        return $this->is_twilio_configured;
    }

    // -------- Obtenir la dernière erreur --------
    public function get_last_error() {
        return $this->last_error;
    }

    // -------- Envoi Email amélioré --------
    public function send_email($to, $subject, $message) {
        
        // Vérifier la configuration
        if (!$this->is_sendgrid_configured) {
            $error_msg = 'SendGrid not configured. Please check your API key.';
            error_log($error_msg);
            return [
                'status' => 'error',
                'success' => false,
                'message' => $error_msg,
                'simulation' => true,
                'code' => $this->extract_code_from_message($message)
            ];
        }

        // Valider l'email
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'success' => false,
                'message' => 'Invalid email address'
            ];
        }

        try {
            $email = new Mail();
            $email->setFrom("info@nufotec.com", "NUFOTEC BURUNDI");
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/html", $message);
            
            $response = $this->sendgrid->send($email);
            $statusCode = $response->statusCode();
            
            if ($statusCode >= 200 && $statusCode < 300) {
                error_log('Email sent successfully to: ' . $to);
                return [
                    'status' => $statusCode,
                    'success' => true,
                    'body' => $response->body()
                ];
            } else {
                error_log('SendGrid error: Status ' . $statusCode . ' - ' . $response->body());
                $this->last_error = 'Status ' . $statusCode . ': ' . $response->body();
                return [
                    'status' => $statusCode,
                    'success' => false,
                    'message' => 'Failed to send email. Status: ' . $statusCode
                ];
            }

        } catch (Exception $e) {
            error_log('SendGrid Exception: ' . $e->getMessage());
            $this->last_error = $e->getMessage();
            return [
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage(),
                'simulation' => true,
                'code' => $this->extract_code_from_message($message)
            ];
        }
    }

    // -------- Extraire le code OTP du message (pour simulation) --------
    private function extract_code_from_message($message) {
        if (preg_match('/<div class="code">(\d{6})<\/div>/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    // -------- Envoi de code OTP par email --------
    public function send_otp_code($to, $user_name, $otp_code, $type = 'reset') {
        
        $subject = 'Code de vérification - NUFOTEC';
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Code de vérification</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .code { font-size: 36px; font-weight: bold; color: #0a66c2; text-align: center; letter-spacing: 8px; margin: 25px 0; font-family: monospace; }
                .footer { margin-top: 20px; font-size: 12px; color: #8a99b0; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2 style="color:#0a2540;">Bonjour ' . htmlspecialchars($user_name) . ',</h2>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Voici votre code de vérification :</p>
                <div class="code">' . $otp_code . '</div>
                <p>Ce code est valable pendant <strong>15 minutes</strong>.</p>
                <p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>
                <hr>
                <div class="footer">
                    &copy; ' . date('Y') . ' NUFOTEC Burundi. Tous droits réservés.
                </div>
            </div>
        </body>
        </html>';
        
        return $this->send_email($to, $subject, $message);
    }

    // -------- WhatsApp simplifié --------
    public function send_whatsapp($to, $message) {
        
        if (!$this->is_twilio_configured) {
            error_log('Twilio not configured - WhatsApp message not sent');
            return [
                'success' => false, 
                'message' => 'WhatsApp not configured',
                'simulation' => true
            ];
        }
        
        try {
            $client = new TwilioClient($this->twilio_sid, $this->twilio_token);
            $to_whatsapp = 'whatsapp:' . $this->format_phone_number($to);

            $client->messages->create($to_whatsapp, [
                'from' => $this->twilio_from,
                'body' => $message
            ]);

            error_log('WhatsApp message sent to: ' . $to);
            return ['success' => true];

        } catch (Exception $e) {
            error_log('WhatsApp Error: ' . $e->getMessage());
            $this->last_error = $e->getMessage();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------- Formater numéro de téléphone --------
    private function format_phone_number($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }
        return $phone;
    }
}