<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_Us extends MY_Controller {

	function __construct()
    {
        parent::__construct();
		is_admin();
	}


    
	public function index()
	{
		$data['contactus']=$this->Model->read('contact_us',null,'IdContact');
		$this->load->view('Contact_Us_View',$data);
	}


	function Create(){
		$FullName=$this->input->post('FullName');
		$Email=$this->input->post('Email');
		$Subject=$this->input->post('Subject');
		$Message=$this->input->post('Message');
		$PhoneNumber=$this->input->post('PhoneNumber');
		$Date_creation=$this->input->post('Date_creation');
		

		$data=array('FullName'=>$FullName,
	                'Email'=>$Email,
	                'Subject'=>$Subject,
	                'Message'=>$Message,
	                'PhoneNumber'=>$PhoneNumber,
	                'Date_creation'=>$Date_creation,
	               );
		$rsp=$this->Model->create('contact_us',$data);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     Content created successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Contact_us'));
	}

	function Update(){
		$IdContact=$this->input->post('IdContact');
        $FullName=$this->input->post('FullName');
		$Email=$this->input->post('Email');
		$Subject=$this->input->post('Subject');
		$Message=$this->input->post('Message');
		$PhoneNumber=$this->input->post('PhoneNumber');
		$Date_creation=$this->input->post('Date_creation');
		

		$data=array('FullName'=>$FullName,
	                'Email'=>$Email,
	                'Subject'=>$Subject,
	                'Message'=>$Message,
	                'PhoneNumber'=>$PhoneNumber,
	                'Date_creation'=>$Date_creation,
	               );
		$rsp=$this->Model->update('contact_us',['IdContact'=>$IdContact],$data);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     Content updated successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Contact_us'));
	}


	function Delete(){
		$IdContact=$this->input->post('IdContact');
		$rsp=$this->Model->delete('contact_us',['IdContact'=>$IdContact]);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     Content deleted successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Contact_us'));
	}



public function MarkAsRead($id) {
    $this->Model->update('contact_us', ['IdContact' => $id], ['is_readed' => 1]);
    echo json_encode(['status' => true]);
}


}