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

    public function __construct() {
        $this->CI =& get_instance();

        // -------- SendGrid --------
        $apiKey = $this->CI->config->item('sendgrid_api_key');
        $this->sendgrid = new \SendGrid($apiKey);

        // -------- Twilio WhatsApp --------
        $this->twilio_sid   = $this->CI->config->item('twilio_sid');
        $this->twilio_token = $this->CI->config->item('twilio_token');
        $this->twilio_from  = $this->CI->config->item('twilio_whatsapp_from'); // ex: whatsapp:+14155238886
    }

    // -------- Envoi Email --------
    public function send_email($to, $subject, $message) {

        $email = new Mail();
        $email->setFrom("info@nufotec.com", "NUFOTEC BURUNDI");
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

    // -------- WhatsApp libre --------
    public function send_whatsapp($to, $message)
    {
        try {
            $client = new TwilioClient($this->twilio_sid, $this->twilio_token);
            $to_whatsapp = 'whatsapp:' . $to;

            $client->messages->create($to_whatsapp, [
                'from' => $this->twilio_from,
                'body' => $message
            ]);

            return true;

        } catch (Exception $e) {
            log_message('error', 'WhatsApp Error: ' . $e->getMessage());
            return false;
        }
    }

    // -------- WhatsApp Template --------
    public function send_whatsapp_template($to, $contentSid, $variables = [])
    {
        try {
            $client = new TwilioClient($this->twilio_sid, $this->twilio_token);
            $to_whatsapp = 'whatsapp:' . $to;

            $client->messages->create($to_whatsapp, [
                'from' => $this->twilio_from,
                'contentSid' => $contentSid,
                'contentVariables' => json_encode($variables)
            ]);

            return true;

        } catch (Exception $e) {
            log_message('error', 'WhatsApp Template Error: ' . $e->getMessage());
            return false;
        }
    }
}