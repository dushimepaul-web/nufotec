<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investissement_paiements extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
        }
    }
    
	public function index()
	{
		$data['paiements'] = $this->Model->read('investissement_paiements', null, 'date_paiement DESC');
		// Récupérer les investissements pour le dropdown
		$data['investissements'] = $this->Model->read('investissements', null, 'numero_investissement');
		$this->load->view('Investissement_paiements_View', $data);
	}

	function ChangeStatus(){
		$id = $this->input->post('id');
		$statut = $this->input->post('statut');
		
		// Cycle des statuts: effectue -> en_attente -> echoue -> effectue
		$statuts = ['effectue', 'en_attente', 'echoue'];
		$current_index = array_search($statut, $statuts);
		$new_statut = $statuts[($current_index + 1) % count($statuts)];
		
		$rsp = $this->Model->update('investissement_paiements', ['id' => $id], ['statut' => $new_statut]);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Statut du paiement mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissement_paiements'));	
	}

	function PaiementDetail($paiementDetail){
		$id = explode('_', $paiementDetail);
		$data['detail'] = $this->Model->readOne('investissement_paiements', ['id' => $id[0]]);
		if ($data['detail']) {
			$data['investissement'] = $this->Model->readOne('investissements', ['id' => $data['detail']['investissement_id']]);
		}
		$this->load->view('Investissement_paiementDetail_View', $data);
	}

	function Create(){
		$investissement_id = $this->input->post('investissement_id');
		$numero_paiement = $this->generate_numero();
		$montant = $this->input->post('montant');
		$type = $this->input->post('type') ?: 'investissement';
		$date_paiement = $this->input->post('date_paiement');
		$mode_paiement = $this->input->post('mode_paiement');
		$statut = $this->input->post('statut') ?: 'effectue';
		$transaction_id = $this->input->post('transaction_id');

		$data = array(
			'investissement_id' => $investissement_id,
			'numero_paiement' => $numero_paiement,
			'montant' => $montant,
			'type' => $type,
			'date_paiement' => $date_paiement,
			'mode_paiement' => $mode_paiement,
			'statut' => $statut,
			'transaction_id' => $transaction_id
		);
		
		$rsp = $this->Model->create('investissement_paiements', $data);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Paiement enregistré avec succès. N°: ' . $numero_paiement . '
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissement_paiements'));
	}

	function Update(){
		$id = $this->input->post('id');
		$investissement_id = $this->input->post('investissement_id');
		$montant = $this->input->post('montant');
		$type = $this->input->post('type');
		$date_paiement = $this->input->post('date_paiement');
		$mode_paiement = $this->input->post('mode_paiement');
		$transaction_id = $this->input->post('transaction_id');

		$data = array(
			'investissement_id' => $investissement_id,
			'montant' => $montant,
			'type' => $type,
			'date_paiement' => $date_paiement,
			'mode_paiement' => $mode_paiement,
			'transaction_id' => $transaction_id
		);

		$rsp = $this->Model->update('investissement_paiements', ['id' => $id], $data);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Paiement mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissement_paiements'));
	}

	function Delete(){
		$id = $this->input->post('id');
		$rsp = $this->Model->delete('investissement_paiements', ['id' => $id]);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Paiement supprimé avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissement_paiements'));
	}

	// Génération numéro unique
	private function generate_numero() {
		return 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
	}
}