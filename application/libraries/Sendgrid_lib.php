<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sendgrid_lib {

    protected $CI;
    protected $from_email;
    protected $from_name;
    protected $is_sendgrid_configured = false;
    protected $last_error = null;

    public function __construct() {
        $this->CI =& get_instance();

        $domain = $_SERVER['HTTP_HOST'];
        $domain = str_replace('www.', '', $domain);
        $this->from_email = $this->CI->config->item('cpanel_from_email') ?: 'info@' . $domain;
        $this->from_name = $this->CI->config->item('cpanel_from_name') ?: 'NUFOTEC BURUNDI';

        $this->CI->load->library('email');
        $this->is_sendgrid_configured = true;
    }

    // -------- Vérifier si l'envoi mail est configuré --------
    public function is_sendgrid_configured() {
        return $this->is_sendgrid_configured;
    }

    // -------- Obtenir la dernière erreur --------
    public function get_last_error() {
        return $this->last_error;
    }

    // -------- Envoi Email --------
    public function send_email($to, $subject, $message) {

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->last_error = 'Invalid email address';
            return [
                'status' => 'error',
                'success' => false,
                'message' => 'Invalid email address'
            ];
        }

        try {
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
            $this->CI->email->clear();
            $this->CI->email->from($this->from_email, $this->from_name);
            $this->CI->email->to($to);
            $this->CI->email->subject($subject);
            $this->CI->email->message($message);

            if ($this->CI->email->send()) {
                log_message('info', 'Email sent successfully to: ' . $to);
                return ['success' => true, 'status' => 200];
            } else {
                $error = $this->CI->email->print_debugger(['headers']);
                $this->last_error = $error;
                log_message('error', 'Email error to ' . $to . ': ' . $error);
                return ['success' => false, 'status' => 'error', 'message' => $error];
            }
        } catch (Exception $e) {
            log_message('error', 'Sendgrid_lib Exception: ' . $e->getMessage());
            $this->last_error = $e->getMessage();
            return [
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
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
                .footer { margin-top: 20px; font-size: 12px; color: #8a99aa; text-align: center; }
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
}