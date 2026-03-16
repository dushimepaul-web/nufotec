public function send_test()
{
    $this->load->library('email');

    $this->email->from('info@nufotec.com','Nufotec');
    $this->email->to('info@nufotec.com');

    $this->email->subject('Test Email');
    $this->email->message('<h2>Email Test OK</h2><p>SMTP irakora neza.</p>');

    if($this->email->send())
    {
        echo "Email yoherejwe neza";
    }
    else
    {
        echo $this->email->print_debugger();
    }
}

