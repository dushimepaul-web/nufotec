<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investissements extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
        }
    }
    
	public function index()
	{
		$data['investissements'] = $this->Model->read('investissements', null, 'created_at DESC');
		// Récupérer les données pour les dropdowns
		$data['investisseurs'] = $this->Model->read('users', ['is_active' => 1], 'nom');
		$data['projets'] = $this->Model->read('projets_investissement', null, 'titre');
		$this->load->view('Investissements_View', $data);
	}

	function ChangeStatus(){
		$id = $this->input->post('id');
		$statut = $this->input->post('statut');
		
		// Cycle des statuts: actif -> termine -> rembourse -> annule -> actif
		$statuts = ['actif', 'termine', 'rembourse', 'annule'];
		$current_index = array_search($statut, $statuts);
		$new_statut = $statuts[($current_index + 1) % count($statuts)];
		
		$rsp = $this->Model->update('investissements', ['id' => $id], ['statut' => $new_statut]);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Statut de l\'investissement mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissements'));	
	}

	function ChangePaiementStatus(){
		$id = $this->input->post('id');
		$statut_paiement = $this->input->post('statut_paiement');
		
		$statuts = ['en_attente', 'paye', 'echoue'];
		$current_index = array_search($statut_paiement, $statuts);
		$new_statut = $statuts[($current_index + 1) % count($statuts)];
		
		$update_data = ['statut_paiement' => $new_statut];
		if ($new_statut == 'paye') {
			$update_data['date_paiement'] = date('Y-m-d H:i:s');
		}
		
		$rsp = $this->Model->update('investissements', ['id' => $id], $update_data);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Statut de paiement mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissements'));	
	}

	function InvestissementDetail($investissementDetail){
		$id = explode('_', $investissementDetail);
		$data['detail'] = $this->Model->readOne('investissements', ['id' => $id[0]]);
		if ($data['detail']) {
			$data['investisseur'] = $this->Model->readOne('users', ['id' => $data['detail']['investisseur_id']]);
			$data['projet'] = $this->Model->readOne('projets_investissement', ['id' => $data['detail']['projet_id']]);
		}
		$this->load->view('InvestissementDetail_View', $data);
	}

	function Create(){
		$investisseur_id = $this->input->post('investisseur_id');
		$projet_id = $this->input->post('projet_id');
		$numero_investissement = $this->generate_numero();
		$montant = $this->input->post('montant');
		$nombre_parts = $this->input->post('nombre_parts');
		$rendement_annuel = $this->input->post('rendement_annuel');
		$duree_mois = $this->input->post('duree_mois');
		$type = $this->input->post('type') ?: 'classique';
		$date_investissement = $this->input->post('date_investissement');
		$mode_paiement = $this->input->post('mode_paiement');
		$statut_paiement = $this->input->post('statut_paiement') ?: 'en_attente';
		$transaction_id = $this->input->post('transaction_id');

		$data = array(
			'investisseur_id' => $investisseur_id,
			'projet_id' => $projet_id,
			'numero_investissement' => $numero_investissement,
			'montant' => $montant,
			'nombre_parts' => $nombre_parts,
			'rendement_annuel' => $rendement_annuel,
			'duree_mois' => $duree_mois,
			'type' => $type,
			'statut' => 'actif',
			'date_investissement' => $date_investissement,
			'mode_paiement' => $mode_paiement,
			'statut_paiement' => $statut_paiement,
			'transaction_id' => $transaction_id
		);
		
		if ($statut_paiement == 'paye') {
			$data['date_paiement'] = date('Y-m-d H:i:s');
		}

		$rsp = $this->Model->create('investissements', $data);

		if ($rsp) {
			// Mettre à jour le montant collecté du projet
			$this->update_projet_collecte($projet_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Investissement créé avec succès. N°: ' . $numero_investissement . '
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissements'));
	}

	function Update(){
		$id = $this->input->post('id');
		$investisseur_id = $this->input->post('investisseur_id');
		$projet_id = $this->input->post('projet_id');
		$montant = $this->input->post('montant');
		$nombre_parts = $this->input->post('nombre_parts');
		$rendement_annuel = $this->input->post('rendement_annuel');
		$duree_mois = $this->input->post('duree_mois');
		$type = $this->input->post('type');
		$date_investissement = $this->input->post('date_investissement');
		$mode_paiement = $this->input->post('mode_paiement');
		$transaction_id = $this->input->post('transaction_id');

		$data = array(
			'investisseur_id' => $investisseur_id,
			'projet_id' => $projet_id,
			'montant' => $montant,
			'nombre_parts' => $nombre_parts,
			'rendement_annuel' => $rendement_annuel,
			'duree_mois' => $duree_mois,
			'type' => $type,
			'date_investissement' => $date_investissement,
			'mode_paiement' => $mode_paiement,
			'transaction_id' => $transaction_id
		);

		$rsp = $this->Model->update('investissements', ['id' => $id], $data);

		if ($rsp) {
			// Recalculer le montant collecté pour l'ancien et nouveau projet si différent
			$this->update_projet_collecte($projet_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Investissement mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissements'));
	}

	function Delete(){
		$id = $this->input->post('id');
		
		// Récupérer le projet_id avant suppression
		$inv = $this->Model->readOne('investissements', ['id' => $id]);
		$projet_id = $inv ? $inv['projet_id'] : null;
		
		$rsp = $this->Model->delete('investissements', ['id' => $id]);

		if ($rsp) {
			// Mettre à jour le montant collecté du projet
			if ($projet_id) {
				$this->update_projet_collecte($projet_id);
			}
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Investissement supprimé avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Investissements'));
	}

	// Génération numéro unique
	private function generate_numero() {
		return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
	}

	// Mettre à jour le montant collecté du projet
	private function update_projet_collecte($projet_id) {
		$investissements = $this->Model->read('investissements', [
			'projet_id' => $projet_id,
			'statut_paiement' => 'paye',
			'statut !=' => 'annule'
		]);
		
		$total = 0;
		$count = count($investissements);
		
		foreach ($investissements as $inv) {
			$total += $inv['montant'];
		}
		
		$this->Model->update('projets_investissement', ['id' => $projet_id], [
			'montant_collecte' => $total,
			'nombre_investisseurs' => $count
		]);
	}
}