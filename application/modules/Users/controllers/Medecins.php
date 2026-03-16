<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Medecins extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Requête principale
        $this->db->select('medecins.*, users.nom, users.prenom, users.email, users.telephone, users.photo, users.is_active, users.est_verifie');
        $this->db->from('medecins');
        $this->db->join('users', 'users.id = medecins.user_id');
        $this->db->order_by('medecins.id', 'DESC');
        $data['medecins'] = $this->db->get()->result_array();
        
        // Horaires pour chaque médecin - regroupés par jour
        foreach ($data['medecins'] as &$medecin) {
            $this->db->reset_query();
            $this->db->from('medecin_horaires');
            $this->db->where('medecin_id', $medecin['id']);
            $this->db->where('est_actif', 1);
            $this->db->order_by("FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')");
            $this->db->order_by('heure_debut', 'ASC');
            $medecin['horaires'] = $this->db->get()->result_array();
            
            // Regrouper les horaires par jour pour l'affichage
            $medecin['horaires_groupes'] = [];
            foreach ($medecin['horaires'] as $h) {
                $jour = $h['jour_semaine'];
                if (!isset($medecin['horaires_groupes'][$jour])) {
                    $medecin['horaires_groupes'][$jour] = [];
                }
                $medecin['horaires_groupes'][$jour][] = $h;
            }
        }
        
        // Users disponibles (non médecins)
        $this->db->reset_query();
        $sql = "SELECT u.id, u.nom, u.prenom, u.email 
                FROM users u 
                WHERE u.is_active = 1 
                AND u.id NOT IN (SELECT user_id FROM medecins WHERE user_id IS NOT NULL)
                ORDER BY u.nom, u.prenom";
        $data['users_disponibles'] = $this->db->query($sql)->result_array();
        
        $data['jours_semaine'] = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        
        $this->load->view('Medecins_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $current = $this->input->post('est_disponible');
        $new = ($current == 1) ? 0 : 1;
        
        $this->Model->update('medecins', ['id' => $id], [
            'est_disponible' => $new, 
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        redirect(base_url('Users/Medecins'));    
    }

    function VerifierKYC(){
        $id = $this->input->post('id');
        $current = $this->input->post('est_verifie');
        $new = ($current == 1) ? 0 : 1;
        
        $medecin = $this->Model->readOne('medecins', ['id' => $id]);
        if ($medecin) {
            $this->Model->update('users', ['id' => $medecin['user_id']], [
                'est_verifie' => $new,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        redirect(base_url('Users/Medecins'));    
    }

    function Detail($id){
        $this->db->select('medecins.*, users.*');
        $this->db->from('medecins');
        $this->db->join('users', 'users.id = medecins.user_id');
        $this->db->where('medecins.id', $id);
        $data['detail'] = $this->db->get()->row_array();
        
        if(empty($data['detail'])) {
            redirect(base_url('Users/Medecins'));
        }

        $this->db->from('medecin_horaires');
        $this->db->where('medecin_id', $id);
        $this->db->where('est_actif', 1);
        $this->db->order_by("FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')");
        $this->db->order_by('heure_debut', 'ASC');
        $data['horaires'] = $this->db->get()->result_array();
        
        // Regrouper par jour
        $data['horaires_groupes'] = [];
        foreach ($data['horaires'] as $h) {
            $jour = $h['jour_semaine'];
            if (!isset($data['horaires_groupes'][$jour])) {
                $data['horaires_groupes'][$jour] = [];
            }
            $data['horaires_groupes'][$jour][] = $h;
        }
        
        $this->load->view('MedecinDetail_View', $data);
    }

    // ==================== CREATE ====================
    function Create(){
        $this->form_validation->set_rules('user_id', 'Utilisateur', 'required|integer');
        $this->form_validation->set_rules('specialite', 'Spécialité', 'required|trim');
        $this->form_validation->set_rules('numero_licence', 'Numéro de Licence', 'required|trim|is_unique[medecins.numero_licence]');
        $this->form_validation->set_rules('annees_experience', 'Années d\'expérience', 'integer');
        $this->form_validation->set_rules('honoraires_consultation', 'Honoraires', 'numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Users/Medecins'));
            return;
        }

          
          // Génération UUID sécurisé
        $uuid = $this->generateUUID();
        
        // Vérification unicité (double sécurité)
        $attempts = 0;
        while ($this->Model->readOne('medecins', ['uuid' => $uuid]) && $attempts < 5) {
            $uuid = $this->generateUUID();
            $attempts++;
        }


        // Créer médecin
        $data = [
            'user_id' => $this->input->post('user_id'),
             'uuid'  => $uuid,
            'specialite' => $this->input->post('specialite'),
            'numero_licence' => $this->input->post('numero_licence'),
            'annees_experience' => $this->input->post('annees_experience') ?: 0,
            'diplomes' => $this->input->post('diplomes'),
            'langues_parlees' => $this->input->post('langues_parlees'),
            'honoraires_consultation' => $this->input->post('honoraires_consultation') ?: 0,
            'est_disponible' => 1,
            'note_moyenne' => 0.00,
            'nombre_avis' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $medecin_id = $this->Model->create('medecins', $data);

        if ($medecin_id) {
            // Traiter les horaires multiples - structure: horaires[jour][index][debut/fin]
            $this->saveHoraires($medecin_id, $this->input->post('horaires'));
            
            $this->session->set_flashdata('success', 'Médecin créé avec succès avec ses horaires.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création du médecin.');
        }
        redirect(base_url('Users/Medecins'));
    }

    // ==================== UPDATE ====================
    function Update(){
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID médecin manquant.');
            redirect(base_url('Users/Medecins'));
            return;
        }
        
        // Vérifier licence unique (sauf pour ce médecin)
        $licence = $this->input->post('numero_licence');
        $exists = $this->Model->readOne('medecins', [
            'numero_licence' => $licence, 
            'id !=' => $id
        ]);
        
        if ($exists) {
            $this->session->set_flashdata('error', 'Ce numéro de licence est déjà utilisé par un autre médecin.');
            redirect(base_url('Users/Medecins'));
            return;
        }
        
        // Update médecin
        $update_data = [
            'specialite' => $this->input->post('specialite'),
            'numero_licence' => $licence,
            'annees_experience' => $this->input->post('annees_experience'),
            'diplomes' => $this->input->post('diplomes'),
            'langues_parlees' => $this->input->post('langues_parlees'),
            'honoraires_consultation' => $this->input->post('honoraires_consultation'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->Model->update('medecins', ['id' => $id], $update_data);
        
        // Supprimer tous les anciens horaires
        $this->Model->delete('medecin_horaires', ['medecin_id' => $id]);
        
        // Insérer les nouveaux horaires
        $this->saveHoraires($id, $this->input->post('horaires'));
        
        $this->session->set_flashdata('success', 'Médecin et horaires mis à jour avec succès.');
        redirect(base_url('Users/Medecins'));
    }

    /**
     * Méthode privée pour sauvegarder les horaires multiples
     * Format attendu: horaires[lundi][0][debut], horaires[lundi][0][fin], etc.
     * ou horaires[0][jour], horaires[0][debut], horaires[0][fin]
     */
    private function saveHoraires($medecin_id, $horaires_post)
    {
        if (empty($horaires_post) || !is_array($horaires_post)) {
            return;
        }

        $jours_valides = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        $horaires_to_insert = [];

        // Détecter le format des données
        $first_key = array_key_first($horaires_post);
        
        if (in_array($first_key, $jours_valides)) {
            // Format: horaires[lundi][0][debut/fin]
            foreach ($horaires_post as $jour => $creneaux) {
                if (!in_array($jour, $jours_valides) || !is_array($creneaux)) {
                    continue;
                }
                
                foreach ($creneaux as $index => $h) {
                    if (!is_array($h)) continue;
                    
                    $debut = $h['debut'] ?? '';
                    $fin = $h['fin'] ?? '';
                    
                    if ($this->validateCreneau($jour, $debut, $fin)) {
                        $horaires_to_insert[] = [
                            'medecin_id' => $medecin_id,
                            'jour_semaine' => $jour,
                            'heure_debut' => $debut,
                            'heure_fin' => $fin,
                            'est_actif' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        } else {
            // Format: horaires[0][jour], horaires[0][debut], horaires[0][fin]
            foreach ($horaires_post as $h) {
                if (!is_array($h)) continue;
                
                $jour = $h['jour'] ?? '';
                $debut = $h['debut'] ?? '';
                $fin = $h['fin'] ?? '';
                
                if ($this->validateCreneau($jour, $debut, $fin)) {
                    $horaires_to_insert[] = [
                        'medecin_id' => $medecin_id,
                        'jour_semaine' => $jour,
                        'heure_debut' => $debut,
                        'heure_fin' => $fin,
                        'est_actif' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        // Insertion batch si plusieurs horaires
        if (!empty($horaires_to_insert)) {
            if (count($horaires_to_insert) > 1 && method_exists($this->db, 'insert_batch')) {
                $this->db->insert_batch('medecin_horaires', $horaires_to_insert);
            } else {
                // Insertion individuelle si insert_batch non dispo ou un seul horaire
                foreach ($horaires_to_insert as $horaire) {
                    $this->Model->create('medecin_horaires', $horaire);
                }
            }
        }
    }



private function generateUUID()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%08s-%04s-%04s-%04s-%012s', str_split(bin2hex($data), 4));
    }

    /**
     * Valider un créneau horaire
     */
    private function validateCreneau($jour, $debut, $fin)
    {
        $jours_valides = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        
        if (!in_array($jour, $jours_valides)) return false;
        if (empty($debut) || empty($fin)) return false;
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $debut)) return false;
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $fin)) return false;
        if ($fin <= $debut) return false; // Fin doit être après début
        
        return true;
    }

    function Delete(){
        $id = $this->input->post('id');
        if (!empty($id)) {
            // Supprimer d'abord les horaires (contrainte FK)
            $this->Model->delete('medecin_horaires', ['medecin_id' => $id]);
            // Puis le médecin
            $this->Model->delete('medecins', ['id' => $id]);
            $this->session->set_flashdata('success', 'Médecin supprimé avec succès.');
        }
        redirect(base_url('Users/Medecins'));
    }

    // ==================== AJAX HELPERS ====================
    
    /**
     * Vérifier disponibilité d'un créneau (AJAX)
     */
    function checkAvailability()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $medecin_id = $this->input->post('medecin_id');
        $jour = $this->input->post('jour');
        $debut = $this->input->post('debut');
        $fin = $this->input->post('fin');
        $exclude_id = $this->input->post('exclude_id'); // Pour update

        if (empty($medecin_id) || empty($jour) || empty($debut) || empty($fin)) {
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            return;
        }

        // Vérifier chevauchement
        $this->db->where('medecin_id', $medecin_id);
        $this->db->where('jour_semaine', $jour);
        $this->db->where('est_actif', 1);
        
        if (!empty($exclude_id)) {
            $this->db->where('id !=', $exclude_id);
        }

        // Condition de chevauchement: (debut1 < fin2) AND (fin1 > debut2)
        $this->db->where("heure_debut < '$fin'");
        $this->db->where("heure_fin > '$debut'");

        $conflict = $this->db->get('medecin_horaires')->row();

        if ($conflict) {
            echo json_encode([
                'success' => false, 
                'message' => 'Ce créneau chevauche un horaire existant (' . 
                            $conflict->heure_debut . ' - ' . $conflict->heure_fin . ')'
            ]);
        } else {
            echo json_encode(['success' => true]);
        }
    }


    /**
 * Génère le formulaire d'édition pour AJAX
 */
function EditForm($id)
{
    $medecin = $this->Model->readOne('medecins', ['id' => $id]);
    if (!$medecin) {
        echo '<div class="alert alert-danger">Médecin non trouvé</div>';
        return;
    }
    
    // Récupérer les horaires existants
    $this->db->from('medecin_horaires');
    $this->db->where('medecin_id', $id);
    $this->db->order_by("FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')");
    $this->db->order_by('heure_debut', 'ASC');
    $horaires = $this->db->get()->result_array();
    
    // Si aucun horaire, créer une ligne vide par défaut
    if (empty($horaires)) {
        $horaires = [['id' => '', 'jour_semaine' => '', 'heure_debut' => '08:00', 'heure_fin' => '12:00']];
    }
    
    $jours_semaine = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    
    // Générer le HTML du formulaire
    ?>
    <div class="modal-header bg-warning text-white">
        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier Médecin</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    
    <form action="<?= base_url('Users/Medecins/Update') ?>" method="POST" class="form-medecin">
        <input type="hidden" name="id" value="<?= $medecin['id'] ?>">
        
        <div class="modal-body p-4">
            <div class="row">
                <!-- Colonne gauche -->
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="bx bx-user me-2"></i>Informations générales</h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Spécialité *</label>
                        <input type="text" class="form-control" name="specialite" value="<?= htmlspecialchars($medecin['specialite']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Numéro de licence *</label>
                        <input type="text" class="form-control" name="numero_licence" value="<?= htmlspecialchars($medecin['numero_licence']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Expérience (ans)</label>
                            <input type="number" class="form-control" name="annees_experience" min="0" value="<?= $medecin['annees_experience'] ?? 0 ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Honoraires ($) *</label>
                            <input type="number" step="0.01" class="form-control" name="honoraires_consultation" value="<?= $medecin['honoraires_consultation'] ?? 0 ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Langues parlées</label>
                        <input type="text" class="form-control" name="langues_parlees" value="<?= htmlspecialchars($medecin['langues_parlees'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Diplômes</label>
                        <textarea class="form-control" name="diplomes" rows="3"><?= htmlspecialchars($medecin['diplomes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Colonne droite : Horaires -->
                <div class="col-md-6">
                    <h6 class="text-primary mb-3 d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-time me-2"></i>Horaires de consultation</span>
                        <button type="button" class="btn btn-sm btn-success" id="btn-add-horaire-update">
                            <i class="bx bx-plus"></i> Ajouter créneau
                        </button>
                    </h6>
                    
                    <div class="horaires-dynamic-container bg-light p-3 rounded" id="horaires-container-update" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($horaires as $index => $h): ?>
                        <div class="horaire-row d-flex align-items-center gap-2 mb-2 p-2 bg-white rounded border">
                            <select class="form-select form-select-sm" name="horaires[<?= $index ?>][jour]" style="width: 110px;" required>
                                <option value="">Jour...</option>
                                <?php foreach ($jours_semaine as $jour): ?>
                                    <option value="<?= $jour ?>" <?= ($h['jour_semaine'] ?? '') == $jour ? 'selected' : '' ?>>
                                        <?= ucfirst($jour) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <div class="d-flex align-items-center gap-1 flex-grow-1">
                                <input type="time" class="form-control form-control-sm" name="horaires[<?= $index ?>][debut]" value="<?= substr($h['heure_debut'] ?? '08:00', 0, 5) ?>" required>
                                <span class="text-muted small">à</span>
                                <input type="time" class="form-control form-control-sm" name="horaires[<?= $index ?>][fin]" value="<?= substr($h['heure_fin'] ?? '12:00', 0, 5) ?>" required>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-horaire" title="Supprimer ce créneau">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">Modifiez ou ajoutez des créneaux</small>
                        <span class="badge bg-secondary" id="count-horaires-update"><?= count($horaires) ?> créneau<?= count($horaires) > 1 ? 'x' : '' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-warning">
                <i class="bx bx-save me-2"></i>Mettre à jour
            </button>
        </div>
    </form>
    <?php
}
}