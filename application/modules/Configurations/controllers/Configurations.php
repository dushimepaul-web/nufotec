<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configurations extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer les configurations avec tri multiple
        $configs = $this->Model->read('configurations', [],'id','ASC');
        
        // Grouper par catégorie
        $data['configurations'] = [];
        foreach ($configs as $config) {
            $data['configurations'][$config['categorie']][] = $config;
        }
        
        // Liste des catégories disponibles
        $data['categories'] = $this->getCategoriesList();
        $data['types'] = [
            'texte' => 'Texte', 
            'nombre' => 'Nombre', 
            'boolean' => 'Booléen (Oui/Non)', 
            'json' => 'JSON', 
            'image' => 'Image/Logo'
        ];
        
        $this->load->view('Configurations_View', $data);
    }

    // Mise à jour AJAX (texte, nombre, boolean)
    public function update()
    {
        // CORRECTION : Accepter aussi les requêtes non-AJAX pour debug
        // if (!$this->input->is_ajax_request()) {
        //     show_404();
        // }

        // CORRECTION : Récupérer les données POST correctement
        $id = $this->input->post('id');
        $valeur = $this->input->post('valeur');
        
        // DEBUG : Log pour voir ce qui arrive
        log_message('debug', 'Update config - ID: ' . $id . ' - Valeur: ' . $valeur);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $config = $this->Model->readOne('configurations', ['id' => $id]);
        if (!$config) {
            echo json_encode(['success' => false, 'message' => 'Configuration non trouvée']);
            return;
        }

        // Validation selon le type
        $valeur_sanitized = $this->validateValueByType($valeur, $config['type']);

        // CORRECTION : Ne pas mettre à jour updated_at manuellement (géré par la DB)
        $rsp = $this->Model->update('configurations', ['id' => $id], [
            'valeur' => $valeur_sanitized
        ]);

        // CORRECTION : Meilleure gestion de la réponse
        if ($rsp) {
            echo json_encode([
                'success' => true, 
                'message' => 'Configuration mise à jour avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors de la mise à jour en base de données'
            ]);
        }
    }

   public function upload_image()
{
    try {
        $id = $this->input->post('id');
        if (!is_scalar($id) || empty($id)) {
            throw new Exception('ID manquant ou invalide');
        }

        if (empty($_FILES['image']['name'])) {
            throw new Exception('Fichier image manquant');
        }

        $config_data = $this->Model->readOne('configurations', ['id' => $id]);
        if (!$config_data || !is_array($config_data)) {
            throw new Exception('Configuration non trouvée');
        }

        // ✅ VÉRIFICATION : s'assurer que 'cle' existe
        if (!isset($config_data['cle']) || empty($config_data['cle'])) {
            // Utiliser un prefix par défaut si 'cle' n'existe pas
            $cle = 'config_' . $id;
            log_message('warning', 'upload_image: cle manquante pour ID ' . $id . ', utilisation de: ' . $cle);
        } else {
            $cle = $config_data['cle'];
        }

        $filename = $this->upload_configuration_image('image', $cle);
        if (!$filename) {
            $error = $this->upload->display_errors('', '');
            throw new Exception('Erreur upload : ' . $error);
        }

        $rsp = $this->Model->update('configurations', ['id' => $id], ['valeur' => $filename]);
        if (!$rsp) {
            throw new Exception('Erreur lors de la mise à jour en base de données');
        }
        // DEBUG
log_message('debug', 'Config data: ' . print_r($config_data, true));
        echo json_encode([
            'success' => true,
            'message' => 'Image uploadée avec succès',
            'filename' => $filename,
            'url' => base_url('attachments/Configurations/' . $filename)
        ]);
    } catch (Throwable $e) {
        log_message('error', 'upload_image - Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
    // Création d'une nouvelle configuration
    public function create()
    {
        $this->form_validation->set_rules('cle', 'Clé', 'required|is_unique[configurations.cle]');
        $this->form_validation->set_rules('type', 'Type', 'required');
        $this->form_validation->set_rules('categorie', 'Catégorie', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Configurations'));
            return;
        }

        $type = $this->input->post('type');
        $cle = $this->input->post('cle');

        // Gestion de la valeur selon le type
        if ($type === 'image' && !empty($_FILES['valeur_image']['name'])) {
            $valeur = $this->upload_configuration_image($_FILES['valeur_image'], $cle);
            if (!$valeur) {
                $this->session->set_flashdata('error', 'Erreur lors de l\'upload de l\'image: ' . $this->upload->display_errors('', ''));
                redirect(base_url('Configurations'));
                return;
            }
        } else {
            $valeur = $this->input->post('valeur') ?? '';
        }

        $data = [
            'cle' => $cle,
            'valeur' => $valeur,
            'type' => $type,
            'categorie' => $this->input->post('categorie'),
            'description' => $this->input->post('description')
            // CORRECTION : Pas besoin de created_at/updated_at, gérés par la DB
        ];

        $rsp = $this->Model->create('configurations', $data);

        $this->session->set_flashdata($rsp ? 'success' : 'error', 
            $rsp ? 'Configuration créée avec succès' : 'Erreur lors de la création');
        
        redirect(base_url('Configurations'));
    }

    // Suppression
    public function delete()
    {
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID manquant');
            redirect(base_url('Configurations'));
            return;
        }

        // Récupérer pour supprimer le fichier si c'est une image
        $config = $this->Model->readOne('configurations', ['id' => $id]);
        if ($config && $config['type'] === 'image' && !empty($config['valeur'])) {
            $filepath = FCPATH . 'attachments/Configurations/' . $config['valeur'];
            if (file_exists($filepath) && $config['valeur'] !== 'default-logo.png') {
                unlink($filepath);
            }
        }

        $rsp = $this->Model->delete('configurations', ['id' => $id]);

        $this->session->set_flashdata($rsp ? 'success' : 'error', 
            $rsp ? 'Configuration supprimée' : 'Erreur lors de la suppression');
        
        redirect(base_url('Configurations'));
    }

private function upload_configuration_image($field_name, $prefix = 'config')
{
    // Vérification du nom du champ
    if (empty($field_name) || !is_string($field_name)) {
        log_message('error', 'upload_configuration_image: nom de champ invalide');
        return FALSE;
    }

    // Nettoyer le prefix
    $prefix = !empty($prefix) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix) : 'config';
    $prefix = substr($prefix, 0, 50);

    // Construction du chemin ABSOLU et VÉRIFIÉ
    $ref_folder = FCPATH . 'attachments' . DIRECTORY_SEPARATOR . 'Configurations' . DIRECTORY_SEPARATOR;
    
    // Normaliser le chemin (Windows/Linux)
    $ref_folder = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ref_folder);
   

    // CRÉATION FORCÉE DU DOSSIER avec permissions maximales
    if (!is_dir($ref_folder)) {
        // Créer récursivement avec erreur silencieuse
        $old_umask = umask(0);
        $created = @mkdir($ref_folder, 0777, TRUE);
        umask($old_umask);
        
        if (!$created) {
            log_message('error', 'Impossible de créer: ' . $ref_folder);
            return FALSE;
        }
        log_message('debug', 'Dossier créé avec succès');
    }

    // Vérification écriture
    if (!is_writable($ref_folder)) {
        @chmod($ref_folder, 0777);
        if (!is_writable($ref_folder)) {
            log_message('error', 'Dossier non writable: ' . $ref_folder);
            return FALSE;
        }
    }

    // Configuration de l'upload
    $config = [
        'upload_path'   => $ref_folder,
        'allowed_types' => 'jpg|jpeg|png|gif|webp|svg|ico',
        'max_size'      => 4096,
        'file_name'     => $prefix . '_' . date('YmdHis') . '_' . uniqid(),
        'overwrite'     => FALSE,
        'remove_spaces' => TRUE,
        'detect_mime'   => TRUE,
        'mod_mime_fix'  => TRUE
    ];

    // ✅ CRUCIAL: Charger et initialiser PROPREMENT
    $this->load->library('upload');
    $this->upload->initialize($config, TRUE); // TRUE = reset complètement la config

    // Vérifier si fichier existe
    if (empty($_FILES[$field_name]['name'])) {
        log_message('error', 'Aucun fichier trouvé pour: ' . $field_name);
        log_message('debug', 'FILES: ' . print_r($_FILES, TRUE));
        return FALSE;
    }

    // Upload
    if (!$this->upload->do_upload($field_name)) {
        $error = $this->upload->display_errors('', '');
        log_message('error', 'Upload échoué: ' . $error);
        log_message('debug', 'Upload data: ' . print_r($this->upload->data(), TRUE));
        return FALSE;
    }

    $data = $this->upload->data();
    log_message('debug', 'Upload réussi: ' . $data['file_name']);
    
    return $data['file_name'];
}

    // Validation selon le type
    private function validateValueByType($valeur, $type)
    {
        switch ($type) {
            case 'nombre':
                return floatval($valeur);
            case 'boolean':
                return in_array($valeur, ['1', 'true', 'on', 'yes', 1, true]) ? '1' : '0';
            case 'json':
                // Vérifier si c'est du JSON valide
                json_decode($valeur);
                return (json_last_error() === JSON_ERROR_NONE) ? $valeur : '{}';
            default:
                return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
        }
    }

    // Liste des catégories
    private function getCategoriesList()
    {
        return [
            'agf_identity' => 'Identité AGF',
            'agf_facility' => 'Facility AGF',
            'agf_finance' => 'Finance AGF',
            'contact' => 'Contact',
            'ui' => 'Interface Utilisateur',
            'system' => 'Système',
            'general' => 'Général',
            'media' => 'Médias & Logos'
        ];
    }


    public function test_upload_path()
{
    $path = rtrim(FCPATH, DIRECTORY_SEPARATOR) 
          . DIRECTORY_SEPARATOR . 'attachments' 
          . DIRECTORY_SEPARATOR . 'Configurations' 
          . DIRECTORY_SEPARATOR;
    
    echo '<h3>Diagnostic du chemin d\'upload</h3>';
    echo 'FCPATH : ' . FCPATH . '<br>';
    echo 'Chemin complet : ' . $path . '<br>';
    echo 'Dossier existe ? ' . (is_dir($path) ? 'OUI' : 'NON') . '<br>';
    echo 'Accessible en écriture ? ' . (is_writable($path) ? 'OUI' : 'NON') . '<br>';

    if (!is_dir($path)) {
        if (mkdir($path, 0755, TRUE)) {
            echo 'Dossier créé avec succès !<br>';
        } else {
            echo 'Échec de la création du dossier.<br>';
        }
    }
}
}