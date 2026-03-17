<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use SendGrid\Mail\Mail;

class Sendgrid_lib {

    protected $CI;
    protected $sendgrid;

    public function __construct() {
        $this->CI =& get_instance();

        // Récupérer la clé API depuis config
        $apiKey = $this->CI->config->item('sendgrid_api_key');

        $this->sendgrid = new SendGrid($apiKey);
    }

    public function send_email($to, $subject, $message) {

        $email = new Mail();
        $email->setFrom("info@nufotec.com", "Nufotec");
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/html", $message);

        try {
            $response = $this->sendgrid->send($email);

            return [
                'status' => $response->statusCode(),
                'body' => $response->body()
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}