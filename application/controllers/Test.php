<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {

    public function send() {

        $this->load->library('sendgrid_lib');

        $result = $this->sendgrid_lib->send_email(
            "dushimepaul51@gmail.com",
            "Test SendGrid CodeIgniter",
            "<h1>Ça marche 🔥</h1>"
        );

        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
}