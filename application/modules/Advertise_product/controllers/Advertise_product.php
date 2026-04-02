<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advertise_product extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->library('Sendgrid_lib');
    }
    
    public function index()
    {
        $data['products'] = $this->Model->read('advertise_product', null, 'id', 'DESC');
        $data['categories'] = $this->Model->read('product_categories', null, 'id', 'ASC');
        $this->load->view('AdvertiseProduct_View', $data);
    }
    
    function ChangeStatus()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('advertise_product', ['id' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('advertise-product'));    
    }
    
    function ChangeFeatured()
    {
        $id = $this->input->post('id');
        $in_vedette = $this->input->post('in_vedette');
        
        $status = ($in_vedette == 1) ? 0 : 1;
        $rsp = $this->Model->update('advertise_product', ['id' => $id], ['in_vedette' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut vedette mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut vedette.');
        }
        redirect(base_url('advertise-product'));    
    }
    
    function ProductDetail($productDetail)
    {
        $id = explode('_', $productDetail);
        $data['detail'] = $this->Model->readOne('advertise_product', ['id' => $id[0]]);
        $data['categories'] = $this->Model->read('product_categories', null, 'id');
        $this->load->view('AdvertiseProductDetail_View', $data);
    }
    
    private function generate_slug($title, $id = null)
    {
        $slug = url_title($title, '-', true);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        if (empty($slug)) {
            $slug = 'produit-' . uniqid();
        }
        
        $slug = substr($slug, 0, 100);
        
        $where = ['slug' => $slug];
        if ($id) {
            $where['id !='] = $id;
        }
        
        $existing = $this->Model->readOne('advertise_product', $where);
        
        if ($existing) {
            $suffix = 1;
            $base_slug = $slug;
            
            while ($this->Model->readOne('advertise_product', ['slug' => $base_slug . '-' . $suffix])) {
                $suffix++;
            }
            $slug = $base_slug . '-' . $suffix;
        }
        
        return $slug;
    }

private function sendProductNotification($product, $action = 'create')
{
    // Vérifier que le produit contient les données requises
    $required_fields = array('id', 'title', 'price', 'description', 'main_image', 'slug');
    foreach ($required_fields as $field) {
        if (!isset($product[$field])) {
            log_message('error', "Champ manquant dans le produit: " . $field);
            return array('success' => 0, 'error' => 1);
        }
    }

    // 1. Récupérer tous les emails des utilisateurs actifs (table users)
    $active_users = $this->Model->read('users', array('is_active' => 1, 'deleted_at' => null), 'id', 'ASC');
    
    // 2. Récupérer tous les emails de la newsletter
    $newsletter_emails = $this->Model->read('newsletter', null, 'id_newsletter', 'ASC');
    
    // 3. Fusionner et éliminer les doublons
    $emails = array();
    
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
    
    // Si aucun email valide, arrêter ici
    if (empty($emails)) {
        log_message('info', "Aucun email valide trouvé pour la notification du produit ID " . $product['id']);
        return array('success' => 0, 'error' => 0);
    }
    
    // 4. Préparer l'URL de l'image du produit
    $product_image_url = base_url('attachments/Products/' . $product['main_image']);
    if (!file_exists(FCPATH . 'attachments/Products/' . $product['main_image']) || $product['main_image'] === 'default-product.png') {
        $product_image_url = base_url('assets/images/default-product.jpg');
    }
    
    // 5. Préparer les variables - CORRECTION: sans l'opérateur ?? pour compatibilité PHP 5.x
    $site_url = base_url();
    $product_url = base_url('product/' . $product['id'] . '_' . $product['slug']);
    $product_title = htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8');
    $product_price = $product['price'];
    $product_description = nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'));
    
    // 6. Sujet selon l'action
    if ($action === 'create') {
        $subject = "🆕 NOUVEAU PRODUIT CHEZ NUFOTEC : " . $product_title;
    } else {
        $subject = "🔄 PRODUIT MIS À JOUR : " . $product_title;
    }
    
    // 7. Template HTML - CORRECTION: utilisation de guillemets doubles pour éviter les conflits
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>{$subject}</title>
        <style>
            @import url(\"https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap\");
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
        </style>
    </head>
    <body style=\"font-family: 'Poppins', Arial, sans-serif; background-color: #f5f5f5; padding: 20px; margin: 0;\">
        <div style=\"max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 35px rgba(0,0,0,0.1);\">
            
            <!-- Header avec couleur primaire -->
            <div style=\"background: linear-gradient(135deg, #0f4c3a 0%, #1a6b52 100%); padding: 30px 20px; text-align: center;\">
                <div style=\"margin-bottom: 15px;\">
                    <span style=\"font-size: 48px;\">" . ($action === 'create' ? '🆕' : '🔄') . "</span>
                </div>
                <h1 style=\"color: #d4af37; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.5px;\">
                    " . ($action === 'create' ? 'NOUVEAU PRODUIT' : 'PRODUIT MIS À JOUR') . "
                </h1>
                <p style=\"color: rgba(255,255,255,0.8); margin: 10px 0 0; font-size: 14px;\">NUFOTEC BURUNDI</p>
            </div>
            
            <!-- Image du produit -->
            <div style=\"padding: 0;\">
                <img src=\"{$product_image_url}\" alt=\"{$product_title}\" style=\"width: 100%; height: auto; max-height: 350px; object-fit: cover;\">
            </div>
            
            <!-- Contenu -->
            <div style=\"padding: 25px 30px;\">
                <h2 style=\"color: #0f4c3a; font-size: 22px; margin: 0 0 15px 0; font-weight: 600; border-left: 4px solid #d4af37; padding-left: 15px;\">{$product_title}</h2>
                
                <div style=\"background-color: #f8f9fa; border-radius: 12px; padding: 15px; margin: 15px 0;\">
                    <p style=\"margin: 0;\"><strong style=\"color: #0f4c3a;\">💰 Prix :</strong> <span style=\"color: #d4af37; font-size: 22px; font-weight: 700;\">{$product_price}</span></p>
                </div>
                
                <!-- Description complète -->
                <div style=\"margin: 20px 0;\">
                    <h3 style=\"color: #0f4c3a; font-size: 16px; margin: 0 0 10px 0;\">📋 Description :</h3>
                    <div style=\"color: #555; line-height: 1.7; font-size: 14px; background-color: #fafafa; padding: 15px; border-radius: 10px;\">
                        {$product_description}
                    </div>
                </div>
                
                <!-- Bouton Voir les détails -->
                <div style=\"text-align: center; margin: 30px 0 20px;\">
                    <a href=\"{$product_url}\" style=\"display: inline-block; background: linear-gradient(135deg, #d4af37 0%, #b8962e 100%); color: #0f4c3a; padding: 14px 35px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(212,175,55,0.3);\">
                        🔍 VOIR LES DÉTAILS
                    </a>
                </div>
                
                <hr style=\"margin: 25px 0 15px; border: none; border-top: 1px solid #e0e0e0;\">
                
                <div style=\"text-align: center;\">
                    <p style=\"color: #999; font-size: 12px; margin: 5px 0;\">
                        NUFOTEC BURUNDI - Votre partenaire santé naturelle
                    </p>
                    <p style=\"color: #bbb; font-size: 10px; margin: 5px 0;\">
                        <a href=\"{$site_url}unsubscribe\" style=\"color: #999; text-decoration: underline;\">Se désabonner</a>
                    </p>
                </div>
            </div>
            
            <!-- Footer avec couleur primaire -->
            <div style=\"background-color: #0a3326; padding: 15px; text-align: center;\">
                <p style=\"color: rgba(212,175,55,0.7); font-size: 11px; margin: 0;\">
                    &copy; " . date('Y') . " NUFOTEC BURUNDI - Tous droits réservés
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    // 8. Envoyer les emails avec limite pour éviter le timeout
    $success_count = 0;
    $error_count = 0;
    $max_emails = 50; // Limite pour éviter le timeout
    $email_count = 0;
    
    foreach ($emails as $email) {
        if ($email_count >= $max_emails) {
            log_message('warning', "Limite d'emails atteinte ({$max_emails}). Reste: " . (count($emails) - $max_emails));
            break;
        }
        
        // Vérifier que sendgrid_lib est disponible
        if (!isset($this->sendgrid_lib)) {
            log_message('error', 'sendgrid_lib non chargée');
            return array('success' => $success_count, 'error' => count($emails) - $success_count);
        }
        
        $result = $this->sendgrid_lib->send_email($email, $subject, $message);
        if ($result['status'] == 202 || $result['status'] == 200) {
            $success_count++;
        } else {
            $error_count++;
            log_message('error', "Échec d'envoi à {$email} : " . print_r($result, true));
        }
        $email_count++;
    }
    
    log_message('info', "Notifications envoyées : {$success_count} succès, {$error_count} échecs pour le produit ID {$product['id']}");
    
    return array('success' => $success_count, 'error' => $error_count);
}
// ================== CREATE AVEC NOTIFICATION ==================
    
    function Create()
    {
        $this->form_validation->set_rules('title', 'Titre', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title = $this->input->post('title');
        $slug = $this->generate_slug($title);
        $category_id = $this->input->post('category_id') ?: NULL;
        
        $main_image = 'default-product.png';
        if (!empty($_FILES['main_image']['name'])) {
            $main_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($main_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('advertise-product'));
                return;
            }
        } else {
            $this->session->set_flashdata('error', 'L\'image principale est requise.');
            redirect(base_url('advertise-product'));
            return;
        }

        $data = array(
            'category_id' => $category_id,
            'main_image' => $main_image,
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'is_active' => 1,
            'in_vedette' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('advertise_product', $data);

        if ($rsp) {
            // Récupérer le produit créé pour l'envoyer dans la notification
            $new_product = $this->Model->readOne('advertise_product', ['slug' => $slug]);
            $notification_result = $this->sendProductNotification($new_product, 'create');
            
            $this->session->set_flashdata('success', 'Produit créé avec succès. ' . $notification_result['success'] . ' notifications envoyées.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du produit.');
        }
        redirect(base_url('advertise-product'));
    }

    // ================== UPDATE AVEC NOTIFICATION ==================
    
    function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('title', 'Titre', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title = $this->input->post('title');
        $slug = $this->generate_slug($title, $id);
        $category_id = $this->input->post('category_id') ?: NULL;
        $is_active = $this->input->post('is_active') ? 1 : 0;
        $in_vedette = $this->input->post('in_vedette') ? 1 : 0;

        $data = array(
            'category_id' => $category_id,
            'title' => $title,
            'slug' => $slug,
            'description' => $this->input->post('description'),
            'price' => $this->input->post('price'),
            'is_active' => $is_active,
            'in_vedette' => $in_vedette,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!empty($_FILES['main_image']['name'])) {
            $new_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('advertise-product'));
                return;
            }
            
            $product = $this->Model->readOne('advertise_product', ['id' => $id]);
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            
            $data['main_image'] = $new_image;
        }

        $rsp = $this->Model->update('advertise_product', ['id' => $id], $data);

        if ($rsp) {
            // Récupérer le produit mis à jour pour envoyer la notification
            $updated_product = $this->Model->readOne('advertise_product', ['id' => $id]);
            $notification_result = $this->sendProductNotification($updated_product, 'update');
            
            $this->session->set_flashdata('success', 'Produit mis à jour avec succès. ' . $notification_result['success'] . ' notifications envoyées.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('advertise-product'));
    }

    function Delete()
    {
        $id = $this->input->post('id');
        
        $product = $this->Model->readOne('advertise_product', ['id' => $id]);
        
        $rsp = $this->Model->delete('advertise_product', ['id' => $id]);

        if ($rsp) {
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            $this->session->set_flashdata('success', 'Produit supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('advertise-product'));
    }

    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Products/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }
}