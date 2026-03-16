<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panier_lignes extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
    }

    }
    
	public function index()
	{
		$data['panier_lignes']=$this->Model->read('panier_lignes',null,'id');
		$this->load->view('Panier_lignes_View',$data);
	}


	function ChangeStatus(){
	  $id=$this->input->post('id');
	  $is_active=$this->input->post('is_active');
	  if ($is_active==1) {
	  	$status=0;
	  }else{
	  	$status=1;
	  }
	  $rsp=$this->Model->update('panier_lignes',['id'=>$id],['is_active'=>$status]);

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
		redirect(base_url('Panier_lignes'));	
	}

	function Panier_lignesDetail($Panier_lignesDetail){
	  $id=explode('_', $Panier_lignesDetail);
	  $data['detail']=$this->Model->readOne('panier_lignes',['id'=>$id[0]]);
	  $this->load->view('Panier_lignesDetail_View',$data);
	}

	function Create(){
		$panier_id=$this->input->post('panier_id');
		$produit_id=$this->input->post('produit_id');
		$quantite=$this->input->post('quantite');
		$prix_unitaire_ht=$this->input->post('prix_unitaire_ht');
		$taux_tva=$this->input->post('taux_tva');

		$data=array('panier_id'=>$panier_id,
	                'produit_id'=>$produit_id,
	                'quantite'=>$quantite,
	                'prix_unitaire_ht'=>$prix_unitaire_ht,
	                'taux_tva'=>$taux_tva,
	               );
		$rsp=$this->Model->create('panier_lignes',$data);

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
		redirect(base_url('Panier_lignes'));
	}

	function Update(){
		$id=$this->input->post('id');
		$panier_id=$this->input->post('panier_id');
		$produit_id=$this->input->post('produit_id');
		$quantite=$this->input->post('quantite');
		$prix_unitaire_ht=$this->input->post('prix_unitaire_ht');
		$taux_tva=$this->input->post('taux_tva');

		$data=array('panier_id'=>$panier_id,
	                'produit_id'=>$produit_id,
	                'quantite'=>$quantite,
	                'prix_unitaire_ht'=>$prix_unitaire_ht,
	                'taux_tva'=>$taux_tva,
	               );
		$rsp=$this->Model->update('panier_lignes',['id'=>$id],$data);

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
		redirect(base_url('Panier_lignes'));
	}


	function Delete(){
		$id=$this->input->post('id');
		$rsp=$this->Model->delete('panier_lignes',['id'=>$id]);

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
		redirect(base_url('Panier_lignes'));
	}
}