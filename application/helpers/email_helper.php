<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('send_email')) {

    function send_email($to, $subject, $message)
    {
        $CI =& get_instance();

        // Load library, configuration izofatwa automatic muri config/email.php
        $CI->load->library('email');

        $CI->email->from('info@nufotec.com', 'Nufotec');
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);

        if ($CI->email->send()) {
            return true;
        } else {
            return $CI->email->print_debugger();
        }
    }

}