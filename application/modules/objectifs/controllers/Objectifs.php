<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Objectifs extends MY_Controller{
    function __construct(){

        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
    }
}
    function index(){
        
		$data['objectifs']=$this->Model->read('objectifs',null,'id_objectif');
        $this->load->view('objectif_view',$data);
    }

    function Create(){
        $objectif=$this->input->post('Objectif');
        $details=$this->input->post('Details');

        $data=array(
            'Objectif'=>$objectif,
            'Details'=>$details
        );
             $rps=$this->Model->create('objectifs',$data);
    

    
		if ($rsp) {
			$sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     Content created successfully.
						 </div>';
		}else{
            $sms['sms']='<div class="alert alert-background fade show mt-1 message" role="alert">
						     <strong class="text-danger">Oups!</strong> An unknown error, contact admin!.
						 </div>';

        $this->session->set_flashdata($sms);
        redirect(base_url('objectifs'));
    
    }

   
    }

    function Update(){
        $objectif=$this->input->post('Objectif');
		$details=$this->input->post('Details');

         $data=array(
            'Objectif'=>$objectif,
            'Details'=>$details,
        );
        $rsp=$this->Model->update('objectifs',['id_objectif'=>$id_objectif],$data);

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
		redirect(base_url('objectifs'));
		
    }

    function delete(){
		$id_objectif=$this->input->post('id_objectif');
		$rsp=$this->Model->delete('objectifs',['id_objectif'=>$id_objectif]);

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
		redirect(base_url('objectifs'));
    }
}