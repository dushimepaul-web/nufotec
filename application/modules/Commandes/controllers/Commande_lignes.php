<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commande_lignes extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
        }
    }
    
	public function index()
	{
		$data['lignes'] = $this->Model->read('commande_lignes', null, 'created_at DESC');
		// Récupérer les données pour les dropdowns
		$data['commandes'] = $this->Model->read('commandes', null, 'numero_commande');
		$data['produits'] = $this->Model->read('produits', ['est_actif' => 1], 'nom');
		$data['vendeurs'] = $this->Model->read('vendeurs', null, 'nom');
		$this->load->view('Commande_lignes_View', $data);
	}

	function ChangeStatus(){
		// Pas de champ statut dans cette table
		$sms['sms'] = '<div class="alert alert-info fade show mt-1 message" role="alert">
					     Les lignes de commande n\'ont pas de statut à modifier.
					 </div>';
		$this->session->set_flashdata($sms);
		redirect(base_url('Commande_lignes'));	
	}

	function LigneDetail($ligneDetail){
		$id = explode('_', $ligneDetail);
		$data['detail'] = $this->Model->readOne('commande_lignes', ['id' => $id[0]]);
		if ($data['detail']) {
			$data['commande'] = $this->Model->readOne('commandes', ['id' => $data['detail']['commande_id']]);
			$data['produit'] = $this->Model->readOne('produits', ['id' => $data['detail']['produit_id']]);
			$data['vendeur'] = $this->Model->readOne('vendeurs', ['id' => $data['detail']['vendeur_id']]);
		}
		$this->load->view('Commande_ligneDetail_View', $data);
	}

	function Create(){
		$commande_id = $this->input->post('commande_id');
		$produit_id = $this->input->post('produit_id');
		$vendeur_id = $this->input->post('vendeur_id');
		$quantite = $this->input->post('quantite');
		$prix_unitaire_ht = $this->input->post('prix_unitaire_ht');
		$taux_tva = $this->input->post('taux_tva');

		// Récupérer les infos du produit
		$produit = $this->Model->readOne('produits', ['id' => $produit_id]);
		$nom_produit = $produit ? $produit['nom'] : 'Produit inconnu';
		$reference_produit = $produit ? $produit['reference'] : 'N/A';

		$data = array(
			'commande_id' => $commande_id,
			'produit_id' => $produit_id,
			'vendeur_id' => $vendeur_id,
			'nom_produit' => $nom_produit,
			'reference_produit' => $reference_produit,
			'quantite' => $quantite,
			'prix_unitaire_ht' => $prix_unitaire_ht,
			'taux_tva' => $taux_tva
		);
		
		$rsp = $this->Model->create('commande_lignes', $data);

		if ($rsp) {
			// Mettre à jour les totaux de la commande
			$this->update_commande_totaux($commande_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Ligne de commande ajoutée avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Commande_lignes'));
	}

	function Update(){
		$id = $this->input->post('id');
		$commande_id = $this->input->post('commande_id');
		$produit_id = $this->input->post('produit_id');
		$vendeur_id = $this->input->post('vendeur_id');
		$nom_produit = $this->input->post('nom_produit');
		$reference_produit = $this->input->post('reference_produit');
		$quantite = $this->input->post('quantite');
		$prix_unitaire_ht = $this->input->post('prix_unitaire_ht');
		$taux_tva = $this->input->post('taux_tva');

		$data = array(
			'commande_id' => $commande_id,
			'produit_id' => $produit_id,
			'vendeur_id' => $vendeur_id,
			'nom_produit' => $nom_produit,
			'reference_produit' => $reference_produit,
			'quantite' => $quantite,
			'prix_unitaire_ht' => $prix_unitaire_ht,
			'taux_tva' => $taux_tva
		);

		$rsp = $this->Model->update('commande_lignes', ['id' => $id], $data);

		if ($rsp) {
			// Mettre à jour les totaux de la commande
			$this->update_commande_totaux($commande_id);
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Ligne de commande mise à jour avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Commande_lignes'));
	}

	function Delete(){
		$id = $this->input->post('id');
		
		// Récupérer la commande_id avant suppression
		$ligne = $this->Model->readOne('commande_lignes', ['id' => $id]);
		$commande_id = $ligne ? $ligne['commande_id'] : null;
		
		$rsp = $this->Model->delete('commande_lignes', ['id' => $id]);

		if ($rsp) {
			// Mettre à jour les totaux de la commande
			if ($commande_id) {
				$this->update_commande_totaux($commande_id);
			}
			
			$sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
						     Ligne de commande supprimée avec succès.
						 </div>';
		} else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
						     <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
						 </div>';
		}
		$this->session->set_flashdata($sms);
		redirect(base_url('Commande_lignes'));
	}

	// Mettre à jour les totaux de la commande
	private function update_commande_totaux($commande_id) {
		$lignes = $this->Model->read('commande_lignes', ['commande_id' => $commande_id]);
		
		$total_ht = 0;
		$total_tva = 0;
		$total_ttc = 0;
		
		foreach ($lignes as $ligne) {
			$total_ht += $ligne['total_ligne_ht'];
			$total_ttc += $ligne['total_ligne_ttc'];
		}
		
		$total_tva = $total_ttc - $total_ht;
		
		$this->Model->update('commandes', ['id' => $commande_id], [
			'total_ht' => $total_ht,
			'total_tva' => $total_tva,
			'total_ttc' => $total_ttc
		]);
	}
}