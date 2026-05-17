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
    

    
public function send_otp_code($to, $user_name, $otp_code, $type = 'reset')
{
    $subject = 'Code de vérification - NUFOTEC';

    // Informations du site
    $site_logo = $this->CI->Model->get_setting('site_logo');
    $site_name = $this->CI->Model->get_setting('site_name', 'NUFOTEC BURUNDI');

    $logo_url = !empty($site_logo)
        ? base_url('attachments/Configurations/' . $site_logo)
        : '';

    $message = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>' . htmlspecialchars($site_name) . '</title>

        <style>

            body{
                margin:0;
                padding:0;
                background:#f3f6fb;
                font-family:Arial, Helvetica, sans-serif;
                -webkit-font-smoothing:antialiased;
            }

            table{
                border-spacing:0;
            }

            img{
                border:0;
                display:block;
                max-width:100%;
            }

            .wrapper{
                width:100%;
                table-layout:fixed;
                background:#f3f6fb;
                padding:30px 15px;
            }

            .main{
                width:100%;
                max-width:620px;
                margin:0 auto;
                background:#ffffff;
                border-radius:22px;
                overflow:hidden;
                box-shadow:0 10px 40px rgba(0,0,0,0.08);
            }

            .header{
                background:linear-gradient(135deg,#0f172a,#1d4ed8,#2563eb);
                padding:45px 30px;
                text-align:center;
            }

            .logo{
                max-height:75px;
                margin:0 auto 18px;
            }

            .site-name{
                color:#ffffff;
                font-size:30px;
                font-weight:700;
                margin-bottom:10px;
            }

            .header-text{
                color:rgba(255,255,255,0.88);
                font-size:15px;
                line-height:1.7;
            }

            .content{
                padding:40px 35px;
            }

            .greeting{
                font-size:28px;
                font-weight:700;
                color:#111827;
                margin-bottom:20px;
            }

            .text{
                color:#4b5563;
                font-size:16px;
                line-height:1.9;
                margin-bottom:28px;
            }

            .otp-container{
                background:linear-gradient(135deg,#eff6ff,#dbeafe);
                border:2px dashed #2563eb;
                border-radius:20px;
                padding:35px 20px;
                text-align:center;
                margin:35px 0;
            }

            .otp-label{
                color:#1d4ed8;
                font-size:14px;
                font-weight:700;
                letter-spacing:1px;
                text-transform:uppercase;
                margin-bottom:18px;
            }

            .otp-code{
                display:inline-block;
                background:#ffffff;
                color:#111827;
                font-size:44px;
                font-weight:800;
                letter-spacing:12px;
                font-family:"Courier New", monospace;
                padding:18px 28px;
                border-radius:16px;
                box-shadow:0 5px 20px rgba(37,99,235,0.15);
            }

            .expire{
                margin-top:18px;
                color:#6b7280;
                font-size:13px;
            }

            .security-box{
                background:#fff7ed;
                border-left:4px solid #f97316;
                padding:18px;
                border-radius:12px;
                margin-top:30px;
            }

            .security-title{
                font-size:14px;
                font-weight:700;
                color:#c2410c;
                margin-bottom:8px;
            }

            .security-text{
                color:#7c2d12;
                font-size:14px;
                line-height:1.7;
            }

            .footer{
                background:#111827;
                padding:35px 25px;
                text-align:center;
            }

            .footer-text{
                color:#9ca3af;
                font-size:13px;
                line-height:1.8;
            }

            .footer-links{
                margin-top:18px;
            }

            .footer-links a{
                color:#60a5fa;
                text-decoration:none;
                font-size:13px;
                margin:0 10px;
            }

            .footer-logo{
                max-height:40px;
                margin:20px auto 0;
                opacity:0.7;
            }

            @media screen and (max-width:600px){

                .content{
                    padding:30px 22px !important;
                }

                .header{
                    padding:35px 20px !important;
                }

                .greeting{
                    font-size:24px !important;
                }

                .otp-code{
                    font-size:34px !important;
                    letter-spacing:8px !important;
                    padding:16px 20px !important;
                }

                .text{
                    font-size:15px !important;
                }

                .site-name{
                    font-size:24px !important;
                }
            }

            @media screen and (max-width:420px){

                .otp-code{
                    width:100%;
                    box-sizing:border-box;
                    font-size:28px !important;
                    letter-spacing:5px !important;
                }

                .content{
                    padding:25px 18px !important;
                }
            }

        </style>

    </head>

    <body>

        <div class="wrapper">

            <table class="main" align="center">

                <!-- HEADER -->
                <tr>
                    <td class="header">';

    if (!empty($logo_url)) {

        $message .= '
                        <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    }

    $message .= '

                        <div class="site-name">
                            ' . htmlspecialchars($site_name) . '
                        </div>

                        <div class="header-text">
                            Vérification sécurisée de votre compte
                        </div>

                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td class="content">

                        <div class="greeting">
                            Bonjour ' . htmlspecialchars($user_name) . ' 👋
                        </div>

                        <div class="text">';

    if ($type == 'reset') {

        $message .= '
                            Vous avez demandé la réinitialisation de votre mot de passe.
                            Utilisez le code de vérification ci-dessous pour continuer la procédure.';
    } else {

        $message .= '
                            Utilisez le code ci-dessous pour vérifier votre compte et sécuriser votre accès.';
    }

    $message .= '
                        </div>

                        <div class="otp-container">

                            <div class="otp-label">
                                Votre code OTP
                            </div>

                            <div class="otp-code">
                                ' . htmlspecialchars($otp_code) . '
                            </div>

                            <div class="expire">
                                ⏳ Ce code expire dans 15 minutes
                            </div>

                        </div>

                        <div class="security-box">

                            <div class="security-title">
                                🔒 Avertissement de sécurité
                            </div>

                            <div class="security-text">
                                Ne partagez jamais ce code avec une autre personne.
                                Si vous n\'êtes pas à l\'origine de cette demande,
                                ignorez simplement cet email. Aucune modification ne sera effectuée sur votre compte.
                            </div>

                        </div>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td class="footer">

                        <div class="footer-text">
                            © ' . date('Y') . ' ' . htmlspecialchars($site_name) . '<br>
                            Tous droits réservés.
                        </div>

                        <div class="footer-links">
                            <a href="' . base_url() . '">Accueil</a>
                        </div>';

    if (!empty($logo_url)) {

        $message .= '
                        <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="footer-logo">';
    }

    $message .= '

                    </td>
                </tr>

            </table>

        </div>

    </body>
    </html>';

    return $this->send_email($to, $subject, $message);
}






public function send_verification_code($to, $user_name, $otp_code)
{
    $subject = 'Vérifiez votre adresse email - NUFOTEC';

    // Informations du site
    $site_logo = $this->CI->Model->get_setting('site_logo');
    $site_name = $this->CI->Model->get_setting('site_name', 'NUFOTEC BURUNDI');

    $logo_url = !empty($site_logo)
        ? base_url('attachments/Configurations/' . $site_logo)
        : '';

    $message = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vérification Email</title>

        <style>
            body{
                margin:0;
                padding:0;
                background:#f4f7fb;
                font-family:Arial, Helvetica, sans-serif;
                -webkit-font-smoothing:antialiased;
            }

            table{
                border-spacing:0;
            }

            img{
                border:0;
                display:block;
                max-width:100%;
            }

            .wrapper{
                width:100%;
                table-layout:fixed;
                background:#f4f7fb;
                padding:30px 15px;
            }

            .main{
                background:#ffffff;
                margin:0 auto;
                width:100%;
                max-width:600px;
                border-radius:20px;
                overflow:hidden;
                box-shadow:0 10px 40px rgba(0,0,0,0.08);
            }

            .header{
                background:linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
                padding:40px 30px;
                text-align:center;
            }

            .logo{
                max-height:70px;
                margin:0 auto 15px;
            }

            .header-title{
                color:#ffffff;
                font-size:28px;
                font-weight:700;
                margin:0;
            }

            .header-subtitle{
                color:rgba(255,255,255,0.85);
                font-size:15px;
                margin-top:10px;
            }

            .content{
                padding:40px 35px;
            }

            .welcome{
                font-size:26px;
                color:#111827;
                font-weight:700;
                margin-bottom:20px;
            }

            .text{
                font-size:16px;
                color:#4b5563;
                line-height:1.8;
                margin-bottom:20px;
            }

            .otp-box{
                background:linear-gradient(135deg,#eff6ff,#dbeafe);
                border:2px dashed #2563eb;
                border-radius:18px;
                padding:30px 20px;
                text-align:center;
                margin:35px 0;
            }

            .otp-label{
                font-size:15px;
                color:#1e40af;
                margin-bottom:15px;
                font-weight:600;
            }

            .otp-code{
                font-size:42px;
                letter-spacing:12px;
                font-weight:800;
                color:#111827;
                background:#ffffff;
                display:inline-block;
                padding:18px 30px;
                border-radius:14px;
                box-shadow:0 4px 15px rgba(37,99,235,0.15);
                font-family:"Courier New", monospace;
            }

            .expire{
                margin-top:18px;
                font-size:13px;
                color:#6b7280;
            }

            .security-box{
                background:#f9fafb;
                border-left:4px solid #2563eb;
                padding:18px;
                border-radius:10px;
                margin-top:30px;
            }

            .security-text{
                font-size:14px;
                color:#4b5563;
                line-height:1.7;
            }

            .footer{
                background:#111827;
                padding:30px 20px;
                text-align:center;
            }

            .footer-text{
                color:#9ca3af;
                font-size:13px;
                line-height:1.7;
            }

            .footer-link{
                color:#60a5fa;
                text-decoration:none;
            }

            @media screen and (max-width:600px){

                .content{
                    padding:30px 22px !important;
                }

                .header{
                    padding:35px 20px !important;
                }

                .welcome{
                    font-size:22px !important;
                }

                .otp-code{
                    font-size:32px !important;
                    letter-spacing:8px !important;
                    padding:15px 20px !important;
                }

                .text{
                    font-size:15px !important;
                }

                .header-title{
                    font-size:24px !important;
                }
            }

            @media screen and (max-width:400px){

                .otp-code{
                    font-size:26px !important;
                    letter-spacing:5px !important;
                    width:100%;
                    box-sizing:border-box;
                }
            }

        </style>
    </head>

    <body>

        <div class="wrapper">

            <table class="main" align="center">

                <!-- HEADER -->
                <tr>
                    <td class="header">';

    if (!empty($logo_url)) {
        $message .= '
                        <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    }

    $message .= '

                        <h1 class="header-title">Confirmation Email</h1>

                        <div class="header-subtitle">
                            Sécurisez votre compte en vérifiant votre adresse email
                        </div>

                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td class="content">

                        <div class="welcome">
                            Bonjour ' . htmlspecialchars($user_name) . ' 👋
                        </div>

                        <div class="text">
                            Merci de rejoindre <strong>' . htmlspecialchars($site_name) . '</strong>.
                            Utilisez le code de vérification ci-dessous pour confirmer votre adresse email et activer votre compte.
                        </div>

                        <div class="otp-box">

                            <div class="otp-label">
                                Votre code de vérification
                            </div>

                            <div class="otp-code">
                                ' . htmlspecialchars($otp_code) . '
                            </div>

                            <div class="expire">
                                ⏳ Ce code expire dans 15 minutes
                            </div>

                        </div>

                        <div class="security-box">
                            <div class="security-text">
                                🔒 Pour votre sécurité, ne partagez jamais ce code avec une autre personne.
                                Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement cet email.
                            </div>
                        </div>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td class="footer">

                        <div class="footer-text">
                            © ' . date('Y') . ' ' . htmlspecialchars($site_name) . '<br>
                            Tous droits réservés.
                        </div>

                    </td>
                </tr>

            </table>

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