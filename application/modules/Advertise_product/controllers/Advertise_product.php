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

    // ================== NOUVELLE METHODE POUR ENVOYER LES NOTIFICATIONS ==================
    
    private function sendProductNotification($product, $action = 'create')
    {
        // 1. Récupérer tous les emails des utilisateurs actifs (table users)
        $active_users = $this->Model->read('users', ['is_active' => 1, 'deleted_at' => null], 'id', 'ASC');
        
        // 2. Récupérer tous les emails de la newsletter
        $newsletter_emails = $this->Model->read('newsletter', null, 'id_newsletter', 'ASC');
        
        // 3. Fusionner et éliminer les doublons
        $emails = [];
        
        foreach ($active_users as $user) {
            if (!empty($user['email'])) {
                $emails[$user['email']] = $user['email'];
            }
        }
        
        foreach ($newsletter_emails as $newsletter) {
            if (!empty($newsletter['email'])) {
                $emails[$newsletter['email']] = $newsletter['email'];
            }
        }
        
        // 4. Préparer le sujet et le message selon l'action
        $site_url = base_url();
        $product_url = base_url('product' . $product['slug']);
        $product_title = htmlspecialchars($product['title']);
        $product_price = number_format($product['price'], 0, ',', ' ') . ' FBu';
        
        if ($action == 'create') {
            $subject = "🆕 NOUVEAU PRODUIT CHEZ NUFOTEC : " . $product_title;
            $message = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                    <div style='background-color: #FF0000; padding: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>🆕 NOUVEAU PRODUIT</h1>
                    </div>
                    <div style='padding: 20px;'>
                        <h2 style='color: #333;'>" . $product_title . "</h2>
                        <p style='color: #666; font-size: 16px;'>Un nouveau produit vient d'être ajouté sur NUFOTEC !</p>
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                            <p style='margin: 5px 0;'><strong>💰 Prix :</strong> " . $product_price . "</p>
                        </div>
                        <a href='" . $product_url . "' style='display: inline-block; background-color: #FF0000; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>🔍 Voir le produit</a>
                        <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                        <p style='color: #999; font-size: 12px; text-align: center;'>NUFOTEC BURUNDI - Votre partenaire santé naturelle</p>
                        <p style='color: #999; font-size: 10px; text-align: center;'>Si vous ne souhaitez plus recevoir nos emails, <a href='" . $site_url . "unsubscribe'>cliquez ici</a>.</p>
                    </div>
                </div>
            </body>
            </html>";
        } else {
            $subject = $product_title . " CHEZ NUFOTEC :";
            $message = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                    <div style='background-color: #FFA500; padding: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>🔄 PRODUIT MIS À JOUR</h1>
                    </div>
                    <div style='padding: 20px;'>
                        <h2 style='color: #333;'>" . $product_title . "</h2>
                        <p style='color: #666; font-size: 16px;'>Ce produit a été récemment mis à jour !</p>
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                            <p style='margin: 5px 0;'><strong>💰 Prix :</strong> " . $product_price . "</p>
                        </div>
                        <a href='" . $product_url . "' style='display: inline-block; background-color: #FFA500; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>🔍 Voir les détails</a>
                        <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                        <p style='color: #999; font-size: 12px; text-align: center;'>NUFOTEC BURUNDI - Votre partenaire santé naturelle</p>
                        <p style='color: #999; font-size: 10px; text-align: center;'>Si vous ne souhaitez plus recevoir nos emails, <a href='" . $site_url . "unsubscribe'>cliquez ici</a>.</p>
                    </div>
                </div>
            </body>
            </html>";
        }
        
        // 5. Envoyer les emails
        $success_count = 0;
        $error_count = 0;
        
        foreach ($emails as $email) {
            $result = $this->sendgrid_lib->send_email($email, $subject, $message);
            if ($result['status'] == 202 || $result['status'] == 200) {
                $success_count++;
            } else {
                $error_count++;
                log_message('error', "Échec d'envoi à $email : " . print_r($result, true));
            }
        }
        
        log_message('info', "Notifications envoyées : $success_count succès, $error_count échecs pour le produit ID {$product['id']}");
        
        return ['success' => $success_count, 'error' => $error_count];
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