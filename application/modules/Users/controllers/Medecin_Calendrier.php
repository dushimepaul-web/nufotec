<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Medecin_Calendrier extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        
        is_admin();

        // Récupérer l'ID du médecin à partir de l'utilisateur connecté
        $user_id = $this->session->userdata('user_id');
        $medecin = $this->Model->readOne('medecins', ['user_id' => $user_id]);
        if (!$medecin) {
            // Si pas de médecin associé, rediriger ou créer ?
            show_error('Aucun profil médecin trouvé pour cet utilisateur.');
        }
        $this->medecin_id = $medecin['id'];
    }

    public function index()
    {
        // Récupérer les horaires du médecin connecté
        $horaires = $this->Model->read('medecin_horaires', ['medecin_id' => $this->medecin_id], 'jour_semaine, heure_debut');

        // Organiser par jour pour faciliter l'affichage
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        $planning = array_fill_keys($jours, []);

        foreach ($horaires as $h) {
            $planning[$h['jour_semaine']][] = $h;
        }

        $data['planning'] = $planning;
        $data['medecin_id'] = $this->medecin_id;
        $data['jours'] = $jours;

        $this->load->view('Medecin_Calendrier_View', $data);
    }

    /**
     * Ajoute ou met à jour un créneau horaire
     */
    public function save()
    {
        $this->form_validation->set_rules('jour', 'Jour', 'required|in_list[lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche]');
        $this->form_validation->set_rules('heure_debut', 'Heure de début', 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]');
        $this->form_validation->set_rules('heure_fin', 'Heure de fin', 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('calendrier'));
            return;
        }

        $jour = $this->input->post('jour');
        $heure_debut = $this->input->post('heure_debut');
        $heure_fin = $this->input->post('heure_fin');
        $id = $this->input->post('id'); // Si modification

        // Vérifier que l'heure de fin est après l'heure de début
        if ($heure_debut >= $heure_fin) {
            $this->session->set_flashdata('error', 'L\'heure de fin doit être après l\'heure de début.');
            redirect(base_url('calendrier'));
            return;
        }

        // Vérifier les chevauchements avec les créneaux existants
        $overlap = $this->check_overlap($jour, $heure_debut, $heure_fin, $id);
        if ($overlap) {
            $this->session->set_flashdata('error', 'Ce créneau chevauche un créneau existant.');
            redirect(base_url('calendrier'));
            return;
        }

        $data = [
            'medecin_id'   => $this->medecin_id,
            'jour_semaine' => $jour,
            'heure_debut'  => $heure_debut,
            'heure_fin'    => $heure_fin,
            'est_actif'    => 1,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($id) {
            // Mise à jour
            $this->Model->update('medecin_horaires', ['id' => $id], $data);
            $this->session->set_flashdata('success', 'Créneau mis à jour.');
        } else {
            // Création
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->Model->create('medecin_horaires', $data);
            $this->session->set_flashdata('success', 'Créneau ajouté.');
        }

        redirect(base_url('calendrier'));
    }

    /**
     * Supprime un créneau
     */
    public function delete($id)
    {
        $horaire = $this->Model->readOne('medecin_horaires', ['id' => $id, 'medecin_id' => $this->medecin_id]);
        if (!$horaire) {
            show_404();
        }

        $this->Model->delete('medecin_horaires', ['id' => $id]);
        $this->session->set_flashdata('success', 'Créneau supprimé.');
        redirect(base_url('calendrier'));
    }

    /**
     * Vérifie si un créneau chevauche un existant
     */
    private function check_overlap($jour, $debut, $fin, $except_id = null)
    {
        $this->db->where('medecin_id', $this->medecin_id);
        $this->db->where('jour_semaine', $jour);
        $this->db->group_start();
        $this->db->where("heure_debut <", $fin);
        $this->db->where("heure_fin >", $debut);
        $this->db->group_end();
        if ($except_id) {
            $this->db->where('id !=', $except_id);
        }
        $query = $this->db->get('medecin_horaires');
        return $query->num_rows() > 0;
    }
}