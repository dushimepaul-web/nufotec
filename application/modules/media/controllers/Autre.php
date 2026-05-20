<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre extends MY_Controller {

    public function __construct() {
        parent::__construct();
        is_admin();
        $this->load->model('autre_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form', 'text']);
            $this->load->library('cpanel_email_lib');
    }

    // ============ ADMIN LISTE (Vue principale) ============



public function admin_liste($offset = 0) {
    // Vérifier connexion admin si nécessaire
    // $this->_check_admin();
    
    // Gestion du filtre
    $filtre = $this->input->get('filtre');
    $types_valides = ['photo', 'book', 'texte', 'link', 'other'];
    
    $config['base_url'] = base_url('Autre/admin_liste');
    $config['per_page'] = 12;
    $config['uri_segment'] = 3;
    
    // Récupérer tous les médias de type 'autre' (exclure video et audio)
    if ($filtre && in_array($filtre, $types_valides)) {
        $data['medias'] = $this->autre_model->get_by_sous_type($filtre, $config['per_page'], $offset);
        $data['total_rows'] = $this->autre_model->count_by_sous_type($filtre);
        $data['filtre_actif'] = $filtre;
    } else {
        $data['medias'] = $this->autre_model->get_all($config['per_page'], $offset);
        $data['total_rows'] = $this->autre_model->count_all();
    }
    
    // Configuration de la pagination
    $config['total_rows'] = $data['total_rows'];
    $config['full_tag_open'] = '<div class="pagination-container"><ul class="pagination">';
    $config['full_tag_close'] = '</ul></div>';
    $config['prev_link'] = '« Précédent';
    $config['next_link'] = 'Suivant »';
    $config['first_link'] = 'Premier';
    $config['last_link'] = 'Dernier';
    
    $this->load->library('pagination');
    $this->pagination->initialize($config);
    
    $data['title'] = 'Gestion - Autres Médias (Photos, Livres, Textes, Liens)';
    $data['pagination'] = $this->pagination->create_links();
    $data['total_items'] = $data['total_rows'];
    
    $this->load->view('Autre_View', $data);
}





    // ============ AJOUTER ============

    public function admin_ajouter() {
    if ($this->input->post()) {
        $this->_validation_form();
        
        if ($this->form_validation->run()) {
            $insert_data = $this->_prepare_data();
            $sous_type = $this->input->post('sous_type');
            
            // Traitement selon le type
            if ($sous_type === 'link') {
                $insert_data['lien'] = $this->input->post('lien');
                $insert_data['miniature'] = $this->input->post('miniature_externe');
                
            } elseif ($sous_type === 'texte') {
                $insert_data['contenu_texte'] = $this->input->post('contenu_texte');
                
            } else {
                if (!empty($_FILES['fichier']['name'])) {
                    $fichier = $this->_upload_fichier();
                    if ($fichier) {
                        $insert_data['fichier'] = $fichier;
                        $insert_data['taille'] = $_FILES['fichier']['size'];
                        $insert_data['mime_type'] = $_FILES['fichier']['type'];
                    }
                }
                
                if (!empty($_FILES['miniature']['name'])) {
                    $miniature = $this->_upload_miniature();
                    if ($miniature) {
                        $insert_data['miniature'] = $miniature;
                    }
                }
            }
            
            $id = $this->autre_model->insert($insert_data);
            
            if ($id) {
                // ============================================
                // ENVOYER LA NOTIFICATION
                // ============================================
                if (!isset($this->cpanel_email_lib)) {
                    $this->load->library('cpanel_email_lib');
                }
                
                if (isset($this->cpanel_email_lib) && is_object($this->cpanel_email_lib)) {
                    $media_data = $this->autre_model->get_by_id($id);
                    if ($media_data) {
                        $media_array = (array)$media_data;
                        $notification_result = $this->sendMediaNotification($media_array, $sous_type);
                        $this->session->set_flashdata('success', 'Média ajouté avec succès. ' . $notification_result['success'] . ' notifications envoyées.');
                    } else {
                        $this->session->set_flashdata('success', 'Média ajouté avec succès.');
                    }
                } else {
                    $this->session->set_flashdata('success', 'Média ajouté avec succès. (Email non envoyé - librairie non disponible)');
                }
            } else {
                $this->session->set_flashdata('error', 'Erreur lors de l\'ajout');
            }
            
            redirect('Autre/admin_liste');
        }
    }
    
    redirect('autre/admin_liste');
}




    // ============ MODIFIER ============

    public function admin_modifier($id) {
        if ($this->input->post()) {
            $this->_validation_form();
            
            if ($this->form_validation->run()) {
                $media = $this->autre_model->get_by_id($id);
                
                if (!$media) {
                    $this->session->set_flashdata('error', 'Média introuvable');
                    redirect('autre/admin_liste');
                }
                
                $update_data = $this->_prepare_data();
                $sous_type = $this->input->post('sous_type');
                
                // Traitement selon le type
                if ($sous_type === 'link') {
                    $update_data['lien'] = $this->input->post('lien');
                    $update_data['miniature'] = $this->input->post('miniature_externe');
                    // Supprimer ancien fichier si existait
                    if (!empty($media->fichier)) {
                        $this->_supprimer_fichier($media->fichier);
                        $update_data['fichier'] = null;
                        $update_data['taille'] = null;
                        $update_data['mime_type'] = null;
                    }
                    
                } elseif ($sous_type === 'texte') {
                    $update_data['contenu_texte'] = $this->input->post('contenu_texte');
                    // Supprimer ancien fichier si existait
                    if (!empty($media->fichier)) {
                        $this->_supprimer_fichier($media->fichier);
                        $update_data['fichier'] = null;
                        $update_data['taille'] = null;
                        $update_data['mime_type'] = null;
                    }
                    
                } else {
                    // Nouveau fichier uploadé ?
                    if (!empty($_FILES['fichier']['name'])) {
                        $fichier = $this->_upload_fichier();
                        
                        if ($fichier) {
                            // Supprimer ancien fichier
                            if (!empty($media->fichier)) {
                                $this->_supprimer_fichier($media->fichier);
                            }
                            
                            $update_data['fichier'] = $fichier;
                            $update_data['taille'] = $_FILES['fichier']['size'];
                            $update_data['mime_type'] = $_FILES['fichier']['type'];
                        }
                    }
                    
                    // Nouvelle miniature ?
                    if (!empty($_FILES['miniature']['name'])) {
                        $miniature = $this->_upload_miniature();
                        if ($miniature) {
                            if (!empty($media->miniature)) {
                                $this->_supprimer_fichier($media->miniature);
                            }
                            $update_data['miniature'] = $miniature;
                        }
                    }
                }
                
                $this->autre_model->update($id, $update_data);
                $this->session->set_flashdata('success', 'Média modifié avec succès');
                redirect('autre/admin_liste');
            }
        }
        
        redirect('autre/admin_liste');
    }

    // ============ SUPPRIMER ============

    public function admin_supprimer($id) {
        $media = $this->autre_model->get_by_id($id);
        
        if ($media) {
            // Supprimer les fichiers physiques
            if (!empty($media->fichier)) {
                $this->_supprimer_fichier($media->fichier);
            }
            if (!empty($media->miniature)) {
                $this->_supprimer_fichier($media->miniature);
            }
            
            $this->autre_model->delete($id);
            $this->session->set_flashdata('success', 'Média supprimé');
        } else {
            $this->session->set_flashdata('error', 'Média introuvable');
        }
        
        redirect('autre/admin_liste');
    }

    // ============ AJAX - GET JSON ============

    public function get_json($id) {
        $media = $this->autre_model->get_by_id($id);
        
        if ($media) {
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(['success' => true, 'media' => $media]));
        } else {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Non trouvé']));
        }
    }

    // ============ MÉTHODES PRIVÉES ============

    private function _validation_form() {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required|trim');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('categorie', 'Catégorie', 'trim|max_length[100]');
        
        $sous_type = $this->input->post('sous_type');
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien externe', 'required|valid_url');
        } elseif ($sous_type === 'texte') {
            $this->form_validation->set_rules('contenu_texte', 'Contenu', 'required|trim');
        }
    }

    private function _prepare_data() {
    $titre = $this->input->post('titre');
    $id = $this->input->post('id_media');
    
    return [
        'titre' => $titre,
        'type' => $this->input->post('sous_type'), // ✅ corrigé ici
        'slug' => $this->autre_model->generate_slug($titre, $id),
        'sous_type' => $this->input->post('sous_type'),
        'description' => $this->input->post('description'),
        'categorie' => $this->input->post('categorie'),
        'est_actif' => $this->input->post('est_actif') ? 1 : 0,
        'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
        'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
        'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
    ];
}

    private function _upload_fichier() {
        $ref_folder = FCPATH . 'attachments/autre/files/';
        
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }
        
        $code = date("YmdHis") . uniqid();
        $file_extension = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
        $filename = $code . '.' . $file_extension;
        $filepath = $ref_folder . $filename;
        
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp', 'svg', 'pdf', 'doc', 'docx', 'txt', 'zip', 'mp4', 'mp3'];
        
        if (!in_array($file_extension, $valid_ext)) {
            return null;
        }
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $filepath)) {
            return 'attachments/autre/files/' . $filename;
        }
        
        return null;
    }

    private function _upload_miniature() {
        $ref_folder = FCPATH . 'attachments/autre/thumbnails/';
        
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }
        
        $code = date("YmdHis") . uniqid();
        $file_extension = strtolower(pathinfo($_FILES['miniature']['name'], PATHINFO_EXTENSION));
        $filename = $code . '.' . $file_extension;
        $filepath = $ref_folder . $filename;
        
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp'];
        
        if (!in_array($file_extension, $valid_ext)) {
            return null;
        }
        
        if (move_uploaded_file($_FILES['miniature']['tmp_name'], $filepath)) {
            return 'attachments/autre/thumbnails/' . $filename;
        }
        
        return null;
    }

    private function _supprimer_fichier($chemin_relatif) {
        $chemin_complet = FCPATH . $chemin_relatif;
        if (file_exists($chemin_complet)) {
            unlink($chemin_complet);
        }
    }

    // ==================== NOTIFICATION NOUVEAU MÉDIA ====================

/**
 * Récupérer tous les emails des utilisateurs actifs
 */
private function getAllUserEmails()
{
    $emails = [];
    
    $active_users = $this->db->select('email')
        ->where('is_active', 1)
        ->where('deleted_at IS NULL')
        ->get('users')
        ->result_array();
    
    $newsletter_emails = $this->db->select('email')
        ->get('newsletter')
        ->result_array();
    
    foreach ($active_users as $user) {
        if (!empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[$user['email']] = $user['email'];
        }
    }
    
    foreach ($newsletter_emails as $newsletter) {
        if (!empty($newsletter['email']) && filter_var($newsletter['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[$newsletter['email']] = $newsletter['email'];
        }
    }
    
    return array_values($emails);
}

/**
 * Obtenir le libellé du type de média
 */
private function getTypeLabel($sous_type)
{
    $labels = [
        'photo' => '📷 PHOTO',
        'book' => '📚 LIVRE / DOCUMENT',
        'texte' => '📝 TEXTE / ARTICLE',
        'link' => '🔗 LIEN EXTERNE',
        'other' => '📁 AUTRE DOCUMENT'
    ];
    return $labels[$sous_type] ?? '📄 DOCUMENT';
}

/**
 * Obtenir l'icône du type de média
 */
private function getTypeIcon($sous_type)
{
    $icons = [
        'photo' => '🖼️',
        'book' => '📖',
        'texte' => '📄',
        'link' => '🔗',
        'other' => '📎'
    ];
    return $icons[$sous_type] ?? '📄';
}

/**
 * Obtenir l'URL de la miniature
 */
private function getThumbnailUrl($media_data, $sous_type)
{
    // Miniature personnalisée
    if (!empty($media_data['miniature'])) {
        return base_url($media_data['miniature']);
    }
    
    // Miniature par défaut selon le type
    $defaults = [
        'photo' => base_url('assets/images/image-default.jpg'),
        'book' => base_url('assets/images/document-default.jpg'),
        'texte' => base_url('assets/images/text-default.png'),
        'link' => base_url('assets/images/link-default.jpg'),
        'other' => base_url('assets/images/file-default.png')
    ];
    
    return $defaults[$sous_type] ?? base_url('assets/images/default-thumbnail.jpg');
}

/**
 * Construire le template HTML pour la notification
 */
private function buildMediaNotificationTemplate($media_data, $sous_type, $media_url, $subject, $site_name, $logo_url, $whatsapp_link)
{
    $current_date = date('d/m/Y');
    $type_label = $this->getTypeLabel($sous_type);
    $type_icon = $this->getTypeIcon($sous_type);
    $thumbnail_url = $this->getThumbnailUrl($media_data, $sous_type);
    $description = !empty($media_data['description']) ? nl2br(htmlspecialchars($media_data['description'])) : 'Aucune description';
    
    // Pour le type texte, afficher un extrait
    if ($sous_type === 'texte' && !empty($media_data['contenu_texte'])) {
        $description = nl2br(htmlspecialchars(substr($media_data['contenu_texte'], 0, 300))) . 
            (strlen($media_data['contenu_texte']) > 300 ? '...' : '');
    }
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 20px;
                line-height: 1.5;
            }
            .container {
                max-width: 560px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            .header {
                background: linear-gradient(135deg, #0a2540, #0f4c3a);
                padding: 30px 24px;
                text-align: center;
            }
            .header-logo {
                max-width: 100px;
                margin-bottom: 15px;
            }
            .header h1 {
                color: #ffffff;
                font-size: 24px;
                font-weight: 700;
                margin: 0;
            }
            .header p {
                color: rgba(255,255,255,0.8);
                font-size: 14px;
                margin: 8px 0 0;
            }
            .type-badge {
                display: inline-block;
                background: rgba(255,255,255,0.2);
                padding: 5px 15px;
                border-radius: 20px;
                margin: 10px 0;
                font-size: 12px;
                font-weight: 600;
            }
            .thumbnail {
                width: 100%;
                height: auto;
                max-height: 300px;
                object-fit: cover;
            }
            .content {
                padding: 28px;
            }
            .media-title {
                font-size: 22px;
                font-weight: 700;
                color: #1a2a3a;
                margin-bottom: 10px;
            }
            .type-indicator {
                display: inline-block;
                background: #e8ecf0;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                color: #5a6a7a;
                margin-bottom: 15px;
            }
            .description {
                color: #5a6a7a;
                font-size: 14px;
                margin: 20px 0;
                line-height: 1.6;
            }
            .btn-view {
                display: inline-block;
                background: #0a66c2;
                color: white;
                padding: 12px 28px;
                text-decoration: none;
                border-radius: 40px;
                font-weight: 600;
                font-size: 14px;
                margin: 10px 0;
            }
            .btn-whatsapp {
                display: inline-block;
                background: #25D366;
                color: white;
                padding: 10px 24px;
                text-decoration: none;
                border-radius: 40px;
                font-weight: 600;
                font-size: 13px;
                margin: 5px;
            }
            .social-links {
                margin: 15px 0;
                text-align: center;
            }
            .footer {
                background: #f8fafc;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            .footer-text {
                font-size: 12px;
                color: #9aaab9;
            }
            .date {
                color: #8a9aaa;
                font-size: 12px;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                ' . (!empty($logo_url) ? '<img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="header-logo">' : '') . '
                <h1>' . $type_icon . ' Nouveau contenu disponible</h1>
                <div class="type-badge">' . $type_label . '</div>
                <p>' . htmlspecialchars($site_name) . '</p>
            </div>
            <img src="' . $thumbnail_url . '" alt="' . htmlspecialchars($media_data['titre']) . '" class="thumbnail">
            <div class="content">
                <div class="media-title">' . htmlspecialchars($media_data['titre']) . '</div>
                <div class="type-indicator">' . $type_label . '</div>
                <div class="date">📅 Publié le ' . $current_date . '</div>
                <div class="description">' . $description . '</div>
                <div style="text-align: center;">
                    <a href="' . $media_url . '" class="btn-view">🔍 Voir le contenu</a>
                </div>
            </div>
            <div class="footer">
                <div class="social-links">
                    ' . ($whatsapp_link != '#' ? '<a href="' . $whatsapp_link . '" class="btn-whatsapp" target="_blank">📱 Rejoignez notre groupe WhatsApp</a>' : '') . '
                </div>
                <div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Votre partenaire santé naturelle</div>
                <div class="footer-text"><a href="' . base_url() . '" style="color:#9aaab9;">Visitez notre site</a></div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Envoyer une notification à tous les utilisateurs pour un nouveau média
 */
private function sendMediaNotification($media_data, $sous_type)
{
    try {
        // Vérifier que la librairie email est chargée
        if (!isset($this->cpanel_email_lib) || !is_object($this->cpanel_email_lib)) {
            log_message('error', 'cpanel_email_lib non disponible - impossible d\'envoyer les notifications');
            return ['success' => 0, 'error' => 1, 'message' => 'Librairie email non disponible'];
        }
        
        // Récupérer tous les emails des utilisateurs actifs
        $emails = $this->getAllUserEmails();
        
        if (empty($emails)) {
            log_message('info', "Aucun email trouvé pour la notification du média ID " . ($media_data['id_media'] ?? 'nouveau'));
            return ['success' => 0, 'error' => 0];
        }
        
        // Récupérer les informations du site
        $site_logo = $this->db->select('setting_value')->where('setting_name', 'site_logo')->get('settings')->row();
        $site_name = $this->db->select('setting_value')->where('setting_name', 'site_name')->get('settings')->row();
        $linkgroupewhatsapp = $this->db->select('setting_value')->where('setting_name', 'linkgroupewhatsapp')->get('settings')->row();
        
        $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo->setting_value) : '';
        $site_name_value = $site_name->setting_value ?? 'NUFOTEC BURUNDI';
        $whatsapp_link = !empty($linkgroupewhatsapp) ? $linkgroupewhatsapp->setting_value : '#';
        
        // Construire l'URL du détail
        $media_slug = !empty($media_data['slug']) ? $media_data['slug'] : $media_data['id_media'];
        $media_url = base_url('media/detail/' . $media_slug);
        
        $success_count = 0;
        $error_count = 0;
        $max_emails = 50;
        $email_count = 0;
        
        $subject = "📄 NOUVEAU CONTENU - " . htmlspecialchars($site_name_value);
        
        foreach ($emails as $email) {
            if ($email_count >= $max_emails) {
                log_message('warning', "Limite d'emails atteinte ({$max_emails}) pour notification");
                break;
            }
            
            $message = $this->buildMediaNotificationTemplate(
                $media_data,
                $sous_type,
                $media_url,
                $subject,
                $site_name_value,
                $logo_url,
                $whatsapp_link
            );
            
            $result = $this->cpanel_email_lib->send_email($email, $subject, $message);
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                log_message('error', "Échec d'envoi à {$email} pour notification: " . print_r($result, true));
            }
            $email_count++;
        }
        
        log_message('info', "Notifications envoyées: {$success_count} succès, {$error_count} échecs");
        return ['success' => $success_count, 'error' => $error_count];
        
    } catch (Exception $e) {
        log_message('error', "Erreur lors de l'envoi des notifications: " . $e->getMessage());
        return ['success' => 0, 'error' => 1];
    }
}
}