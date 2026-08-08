<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_Us extends MY_Controller {

	function __construct()
    {
        parent::__construct();
		is_admin();
	}

	/**
	 * Liste des messages de contact
	 */
	public function index()
	{
		$data['contactus']=$this->Model->read('contact_us',null,'IdContact');
		$this->load->view('Contact_Us_View',$data);
	}

	function Create(){

		// --- Validation serveur (en complément du HTML) ---
		$this->load->library('form_validation');
		$this->form_validation->set_rules('FullName','Nom complet','required|trim|max_length[250]');
		$this->form_validation->set_rules('Email','Email','required|trim|valid_email|max_length[250]');
		$this->form_validation->set_rules('Subject','Sujet','required|trim|max_length[250]');
		$this->form_validation->set_rules('Message','Message','required|trim');
		$this->form_validation->set_rules('PhoneNumber','Téléphone','required|trim|max_length[12]');

		if ($this->form_validation->run() == FALSE) {
			$sms['sms']='<div class="alert alert-danger fade show mt-1 message" role="alert">
							 '.validation_errors().'
						 </div>';
			$this->session->set_flashdata($sms);
			redirect(base_url('contact_us/Contact_Us'));
		}

		$data=array(
			'FullName'     => $this->security->xss_clean(trim($this->input->post('FullName', TRUE))),
			'Email'        => $this->security->xss_clean(trim($this->input->post('Email', TRUE))),
			'Subject'      => $this->security->xss_clean(trim($this->input->post('Subject', TRUE))),
			'Message'      => $this->security->xss_clean(trim($this->input->post('Message', TRUE))),
			'PhoneNumber'  => $this->security->xss_clean(trim($this->input->post('PhoneNumber', TRUE))),
			'Date_creation'=> $this->input->post('Date_creation') ? date('Y-m-d H:i:s', strtotime($this->input->post('Date_creation'))) : date('Y-m-d H:i:s'),
			'Location'     => '',
		);
		$rsp=$this->Model->create('contact_us',$data);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-success fade show mt-1 message" role="alert">
						     Content created successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('contact_us/Contact_Us'));
	}

	function Update(){
		$IdContact=$this->input->post('IdContact');

		// Validation serveur
		$this->load->library('form_validation');
		$this->form_validation->set_rules('IdContact','IdContact','required|integer');
		$this->form_validation->set_rules('FullName','Nom complet','required|trim|max_length[250]');
		$this->form_validation->set_rules('Email','Email','required|trim|valid_email|max_length[250]');
		$this->form_validation->set_rules('Subject','Sujet','required|trim|max_length[250]');
		$this->form_validation->set_rules('Message','Message','required|trim');
		$this->form_validation->set_rules('PhoneNumber','Téléphone','required|trim|max_length[12]');

		if ($this->form_validation->run() == FALSE) {
			$sms['sms']='<div class="alert alert-danger fade show mt-1 message" role="alert">
						     '.validation_errors().'
						 </div>';
			$this->session->set_flashdata($sms);
			redirect(base_url('contact_us/Contact_Us'));
		}

		$data=array(
			'FullName'     => $this->security->xss_clean(trim($this->input->post('FullName', TRUE))),
			'Email'        => $this->security->xss_clean(trim($this->input->post('Email', TRUE))),
			'Subject'      => $this->security->xss_clean(trim($this->input->post('Subject', TRUE))),
			'Message'      => $this->security->xss_clean(trim($this->input->post('Message', TRUE))),
			'PhoneNumber'  => $this->security->xss_clean(trim($this->input->post('PhoneNumber', TRUE))),
		);
		$rsp=$this->Model->update('contact_us',['IdContact'=>$IdContact],$data);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-success fade show mt-1 message" role="alert">
						     Content updated successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('contact_us/Contact_Us'));
	}


	function Delete(){
		$IdContact=$this->input->post('IdContact');

		if (empty($IdContact) || !is_numeric($IdContact)) {
			$this->session->set_flashdata('sms','<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong class="text-danger">Invalid ID.</strong>
						 </div>');
			redirect(base_url('contact_us/Contact_Us'));
		}

		$rsp=$this->Model->delete('contact_us',['IdContact'=>$IdContact]);

		if ($rsp) {
			$sms['sms']='<div class="alert alert-success fade show mt-1 message" role="alert">
						     Content deleted successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('contact_us/Contact_Us'));
	}



public function MarkAsRead($id) {
    header('Content-Type: application/json');

    $id = (int) $id;
    if ($id <= 0) {
        echo json_encode(['status' => false, 'message' => 'ID invalide']);
        return;
    }

    $message = $this->Model->read_one('contact_us', ['IdContact' => $id]);
    if (empty($message)) {
        echo json_encode(['status' => false, 'message' => 'Message introuvable']);
        return;
    }

    $rsp = $this->Model->update('contact_us', ['IdContact' => $id], ['is_readed' => 1]);
    echo json_encode(['status' => (bool) $rsp]);
}


}