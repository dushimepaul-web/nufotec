<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produit_avis extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
        }
    }
    
	public function index()
	{
		$data['avis'] = $this->Model->read('produit_avis', null, 'created_at DESC');
		// Récupérer les produits et utilisateurs pour les dropdowns
		$data['produits'] = $this->Model->read('produits', ['est_actif' => 1], 'nom');
		$data['users'] = $this->Model->read('users', ['is_active' => 1], 'nom');
		$this->load->view('Produit_avis_View', $data);
	}

	function ChangeStatus(){
		$id = $this->input->post('id');
		$est_valide = $this->input->post('est_valide');
		
		$status = ($est_valide == 1) ? 0 : 1;
		$rsp = $this->Model->update('produit_avis', ['id' => $id], ['est_valide' => $status]);

		if ($rsp) {
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Validation mise à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Produit_avis'));	
	}

	function AvisDetail($avisDetail){
		$id = explode('_', $avisDetail);
		$data['detail'] = $this->Model->readOne('produit_avis', ['id' => $id[0]]);
		if ($data['detail']) {
			$data['produit'] = $this->Model->readOne('produits', ['id' => $data['detail']['produit_id']]);
			$data['user'] = $this->Model->readOne('users', ['id' => $data['detail']['user_id']]);
		}
		$this->load->view('Produit_avisDetail_View', $data);
	}

	function Create(){
		$produit_id = $this->input->post('produit_id');
		$user_id = $this->input->post('user_id');
		$note = $this->input->post('note');
		$titre = $this->input->post('titre');
		$commentaire = $this->input->post('commentaire');
		$avantages = $this->input->post('avantages');
		$inconvenients = $this->input->post('inconvenients');
		$est_valide = $this->input->post('est_valide') ? 1 : 0;

		$data = array(
			'produit_id' => $produit_id,
			'user_id' => $user_id,
			'note' => $note,
			'titre' => $titre,
			'commentaire' => $commentaire,
			'avantages' => $avantages,
			'inconvenients' => $inconvenients,
			'est_valide' => $est_valide
		);
		
		$rsp = $this->Model->create('produit_avis', $data);

		if ($rsp) {
			// Mettre à jour la note moyenne du produit
			$this->update_produit_rating($produit_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Avis créé avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Produit_avis'));
	}

	function Update(){
		$id = $this->input->post('id');
		$produit_id = $this->input->post('produit_id');
		$user_id = $this->input->post('user_id');
		$note = $this->input->post('note');
		$titre = $this->input->post('titre');
		$commentaire = $this->input->post('commentaire');
		$avantages = $this->input->post('avantages');
		$inconvenients = $this->input->post('inconvenients');
		$est_valide = $this->input->post('est_valide') ? 1 : 0;

		$data = array(
			'produit_id' => $produit_id,
			'user_id' => $user_id,
			'note' => $note,
			'titre' => $titre,
			'commentaire' => $commentaire,
			'avantages' => $avantages,
			'inconvenients' => $inconvenients,
			'est_valide' => $est_valide
		);

		$rsp = $this->Model->update('produit_avis', ['id' => $id], $data);

		if ($rsp) {
			// Mettre à jour la note moyenne du produit
			$this->update_produit_rating($produit_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Avis mis à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Produit_avis'));
	}

	function Delete(){
		$id = $this->input->post('id');
		
		// Récupérer le produit_id avant suppression pour mettre à jour la note
		$avis = $this->Model->readOne('produit_avis', ['id' => $id]);
		$produit_id = $avis ? $avis['produit_id'] : null;
		
		$rsp = $this->Model->delete('produit_avis', ['id' => $id]);

		if ($rsp) {
			// Mettre à jour la note moyenne du produit
			if ($produit_id) {
				$this->update_produit_rating($produit_id);
			}
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Avis supprimé avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Produit_avis'));
	}

	// Mettre à jour la note moyenne du produit
	private function update_produit_rating($produit_id) {
		$avis_list = $this->Model->read('produit_avis', ['produit_id' => $produit_id, 'est_valide' => 1]);
		$total_notes = 0;
		$count = count($avis_list);
		
		if ($count > 0) {
			foreach ($avis_list as $a) {
				$total_notes += $a['note'];
			}
			$moyenne = round($total_notes / $count, 2);
		} else {
			$moyenne = 0;
			$count = 0;
		}
		
		$this->Model->update('produits', ['id' => $produit_id], [
			'note_moyenne' => $moyenne,
			'nombre_avis' => $count
		]);
	}
}