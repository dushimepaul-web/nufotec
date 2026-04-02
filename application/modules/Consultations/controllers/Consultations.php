<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
le patient apres avoir complete le fichier  il doit recevooir une email de comfirmation 

 avec le statut de pending , le medicin le comfirme et il recoi la message de comfirmation 

 et puis aevc heure statut et le lein de 

 si le status et complete le lien est generer

*/
class Consultations extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
{
    // Récupérer les consultations avec jointures
    $this->db->select('
        c.*, 
        p.nom as patient_nom, 
        p.prenom as patient_prenom, 
        p.email as patient_email, 
        p.telephone as patient_telephone,
        u.nom as medecin_nom, 
        u.prenom as medecin_prenom,
        m.specialite as medecin_specialite,
        m.id as medecin_id
    ');
    $this->db->from('consultations c');
    $this->db->join('users p', 'p.id = c.patient_id', 'left');
    $this->db->join('medecins m', 'm.id = c.medecin_id', 'left');
    $this->db->join('users u', 'u.id = m.user_id', 'left'); 
    $this->db->order_by('c.created_at', 'DESC');
    $data['consultations'] = $this->db->get()->result_array();
    
    // Récupérer la liste des patients
    $this->db->where('type_utilisateur', 'patient');
    $this->db->where('is_active', 1);
    $this->db->order_by('nom', 'ASC');
    $data['patients'] = $this->db->get('users')->result_array();
    
    // Récupérer la liste des médecins avec leurs noms depuis users
    $this->db->select('m.*, u.nom, u.prenom, u.email, u.photo');
    $this->db->from('medecins m');
    $this->db->join('users u', 'u.id = m.user_id');
    $this->db->where('u.is_active', 1);
    $this->db->order_by('u.nom', 'ASC');
    $this->db->order_by('u.prenom', 'ASC');
    $data['medecins'] = $this->db->get()->result_array();
    
    $this->load->view('Consultations_View', $data);
}

    function ChangeStatus(){
        $id = $this->input->post('id');
        $statut = $this->input->post('statut');
        
        $data = ['statut' => $statut];
        
        // Si passage à "en_cours", on enregistre la date de début
        if ($statut == 'en_cours') {
            $data['date_debut'] = date('Y-m-d H:i:s');
        }
        // Si passage à "terminee", on enregistre la date de fin
        if ($statut == 'terminee') {
            $data['date_fin'] = date('Y-m-d H:i:s');
            // Calcul de la durée réelle si date_debut existe
            $consultation = $this->Model->readOne('consultations', ['id' => $id]);
            if ($consultation && !empty($consultation['date_debut'])) {
                $debut = strtotime($consultation['date_debut']);
                $fin = time();
                $data['duree_minutes'] = round(($fin - $debut) / 60);
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $rsp = $this->Model->update('consultations', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de la consultation mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Consultations'));    
    }

    function Cancel(){
        $id = $this->input->post('id');
        $motif = $this->input->post('motif_annulation');
        
        $rsp = $this->Model->update('consultations', ['id' => $id], [
            'statut' => 'annulee',
            'motif_annulation' => $motif,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Consultation annulée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de l\'annulation.');
        }
        redirect(base_url('Consultations'));
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('patient_id', 'Patient', 'required|numeric');
        $this->form_validation->set_rules('medecin_id', 'Médecin', 'numeric');
        $this->form_validation->set_rules('type', 'Type de consultation', 'required|in_list[video,presentiel,telephone]');
        $this->form_validation->set_rules('date_souhaitee', 'Date souhaitée', 'required');
        $this->form_validation->set_rules('prix_ht', 'Prix HT', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('duree_minutes', 'Durée', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Consultations'));
            return;
        }

        // Générer un numéro de consultation unique
        $numero_consultation = $this->generate_consultation_number();
        
        // Générer un room_id pour les consultations vidéo
        $room_id = null;
        if ($this->input->post('type') == 'video') {
            $room_id = $this->generate_room_id();
        }

        $data = array(
            'patient_id' => $this->input->post('patient_id'),
            'medecin_id' => $this->input->post('medecin_id') ?: NULL,
            'numero_consultation' => $numero_consultation,
            'type' => $this->input->post('type'),
            'poids' => $this->input->post('poids') ?: NULL,
            'taille' => $this->input->post('taille') ?: NULL,
            'symptomes' => $this->input->post('symptomes') ?: NULL,
            'examens_demandes' => $this->input->post('examens_demandes') ?: NULL,
            'diagnostic' => $this->input->post('diagnostic') ?: NULL,
            'traitement' => $this->input->post('traitement') ?: NULL,
            'notes_medecin' => $this->input->post('notes_medecin') ?: NULL,
            'date_souhaitee' => $this->input->post('date_souhaitee'),
            'date_confirmee' => $this->input->post('date_confirmee') ?: NULL,
            'duree_minutes' => $this->input->post('duree_minutes'),
            'room_id' => $room_id,
            'statut' => 'en_attente',
            'prix_ht' => $this->input->post('prix_ht'),
            'tva' => $this->input->post('tva') ?: 20.00,
            'paiement_statut' => $this->input->post('paiement_statut') ?: 'en_attente',
            'ip_creation' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload preuve de paiement si fournie
        if (!empty($_FILES['preuve_paiement']['name'])) {
            $preuve = $this->upload_file($_FILES['preuve_paiement']['tmp_name'], $_FILES['preuve_paiement']['name']);
            if ($preuve === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la preuve de paiement.');
                redirect(base_url('Consultations'));
                return;
            }
            $data['preuve_paiement'] = $preuve;
        }
        
        $rsp = $this->Model->create('consultations', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Consultation créée avec succès. N°: ' . $numero_consultation);
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la consultation.');
        }
        redirect(base_url('Consultations'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('patient_id', 'Patient', 'required|numeric');
        $this->form_validation->set_rules('medecin_id', 'Médecin', 'numeric');
        $this->form_validation->set_rules('type', 'Type de consultation', 'required|in_list[video,presentiel,telephone]');
        $this->form_validation->set_rules('date_souhaitee', 'Date souhaitée', 'required');
        $this->form_validation->set_rules('prix_ht', 'Prix HT', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('duree_minutes', 'Durée', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Consultations'));
            return;
        }

        $data = array(
            'patient_id' => $this->input->post('patient_id'),
            'medecin_id' => $this->input->post('medecin_id') ?: NULL,
            'type' => $this->input->post('type'),
            'poids' => $this->input->post('poids') ?: NULL,
            'taille' => $this->input->post('taille') ?: NULL,
            'symptomes' => $this->input->post('symptomes') ?: NULL,
            'examens_demandes' => $this->input->post('examens_demandes') ?: NULL,
            'diagnostic' => $this->input->post('diagnostic') ?: NULL,
            'traitement' => $this->input->post('traitement') ?: NULL,
            'notes_medecin' => $this->input->post('notes_medecin') ?: NULL,
            'date_souhaitee' => $this->input->post('date_souhaitee'),
            'date_confirmee' => $this->input->post('date_confirmee') ?: NULL,
            'duree_minutes' => $this->input->post('duree_minutes'),
            'prix_ht' => $this->input->post('prix_ht'),
            'tva' => $this->input->post('tva') ?: 20.00,
            'paiement_statut' => $this->input->post('paiement_statut'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Gestion du room_id selon le type
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        if ($this->input->post('type') == 'video' && empty($consultation['room_id'])) {
            $data['room_id'] = $this->generate_room_id();
        } elseif ($this->input->post('type') != 'video') {
            $data['room_id'] = NULL;
        }

        // Upload nouvelle preuve de paiement si fournie
        if (!empty($_FILES['preuve_paiement']['name'])) {
            $preuve = $this->upload_file($_FILES['preuve_paiement']['tmp_name'], $_FILES['preuve_paiement']['name']);
            if ($preuve === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide pour la preuve de paiement.');
                redirect(base_url('Consultations'));
                return;
            }
            
            // Supprimer l'ancienne preuve si existe
            if ($consultation && !empty($consultation['preuve_paiement']) && file_exists(FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement'])) {
                unlink(FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement']);
            }
            
            $data['preuve_paiement'] = $preuve;
        }

        $rsp = $this->Model->update('consultations', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Consultation mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Consultations'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer la consultation pour supprimer la preuve de paiement
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        
        $rsp = $this->Model->delete('consultations', ['id' => $id]);

        if ($rsp) {
            // Supprimer la preuve de paiement si existe
            if ($consultation && !empty($consultation['preuve_paiement']) && file_exists(FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement'])) {
                unlink(FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement']);
            }
            $this->session->set_flashdata('success', 'Consultation supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Consultations'));
    }

    // Générer un numéro de consultation unique (ex: CONS-20240220-XXXXX)
    private function generate_consultation_number() {
        $prefix = 'CONS-' . date('Ymd') . '-';
        $random = strtoupper(substr(uniqid(), -5));
        $number = $prefix . $random;
        
        // Vérifier l'unicité
        while ($this->Model->readOne('consultations', ['numero_consultation' => $number])) {
            $random = strtoupper(substr(uniqid(), -5));
            $number = $prefix . $random;
        }
        
        return $number;
    }

    // Générer un room_id unique pour les consultations vidéo
    private function generate_room_id() {
        return 'room-' . uniqid() . '-' . bin2hex(random_bytes(4));
    }

    // Upload fichiers (preuve de paiement)
    public function upload_file($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Consultations/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('pdf', 'jpg', 'png', 'jpeg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }

    // Démarrer une consultation (changement statut + création room)
    function StartConsultation($id){
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        
        if (!$consultation) {
            $this->session->set_flashdata('error', 'Consultation non trouvée.');
            redirect(base_url('Consultations'));
            return;
        }

        // Générer room_id si consultation vidéo et pas de room existante
        $room_id = $consultation['room_id'];
        if ($consultation['type'] == 'video' && empty($room_id)) {
            $room_id = $this->generate_room_id();
        }

        $rsp = $this->Model->update('consultations', ['id' => $id], [
            'statut' => 'en_cours',
            'date_debut' => date('Y-m-d H:i:s'),
            'room_id' => $room_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Consultation démarrée. Room ID: ' . $room_id);
        } else {
            $this->session->set_flashdata('error', 'Erreur lors du démarrage de la consultation.');
        }
        redirect(base_url('Consultations'));
    }

    // Terminer une consultation
    function EndConsultation($id){
        $consultation = $this->Model->readOne('consultations', ['id' => $id]);
        
        $data = [
            'statut' => 'terminee',
            'date_fin' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Calcul de la durée réelle
        if ($consultation && !empty($consultation['date_debut'])) {
            $debut = strtotime($consultation['date_debut']);
            $fin = time();
            $data['duree_minutes'] = round(($fin - $debut) / 60);
        }

        $rsp = $this->Model->update('consultations', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Consultation terminée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la clôture de la consultation.');
        }
        redirect(base_url('Consultations'));
    }





    /**
 * Télécharge un fichier (preuve, examen, ordonnance) associé à une consultation
 * @param string $type 'preuve'|'examen'|'ordonnance'
 * @param int $consultation_id
 * @param int $index (optionnel) pour les tableaux JSON (examens/ordonnances)
 */
public function download_file($type, $consultation_id, $index = null)
{
    $consultation = $this->Model->readOne('consultations', ['id' => $consultation_id]);
    if (!$consultation) {
        show_404();
    }

    $file_path = null;
    $filename = null;

    switch ($type) {
        case 'preuve':
            if (!empty($consultation['preuve_paiement'])) {
                $file_path = FCPATH . 'attachments/Consultations/' . $consultation['preuve_paiement'];
                $filename = $consultation['preuve_paiement'];
            }
            break;

        case 'examen':
            if (!empty($consultation['examens_demandes'])) {
                $files = json_decode($consultation['examens_demandes'], true);
                if (is_array($files) && isset($files[$index])) {
                    $file_path = FCPATH . 'attachments/Consultations/' . $files[$index];
                    $filename = $files[$index];
                }
            }
            break;

        case 'ordonnance':
            if (!empty($consultation['ordonnances'])) {
                $files = json_decode($consultation['ordonnances'], true);
                if (is_array($files) && isset($files[$index])) {
                    $file_path = FCPATH . 'attachments/Consultations/' . $files[$index];
                    $filename = $files[$index];
                }
            }
            break;
    }

    if (!$file_path || !file_exists($file_path)) {
        show_404();
    }

    // Déterminer le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    // Forcer l'affichage dans le navigateur pour les PDF/images
    $inline_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
    $disposition = in_array($mime, $inline_types) ? 'inline' : 'attachment';

    header('Content-Type: ' . $mime);
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($file_path);
    exit;
}
}