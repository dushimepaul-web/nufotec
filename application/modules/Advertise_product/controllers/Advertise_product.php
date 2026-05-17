<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advertise_product extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Charger la librairie email cPanel
        $this->load->library('cpanel_email_lib');
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
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de mise à jour du statut vedette.');
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

    private function sendEmails($emails, $subject, $message, $context = 'general')
    {
        $success_count = 0;
        $error_count = 0;
        $max_emails = 50;
        $email_count = 0;
        
        foreach ($emails as $email) {
            if ($email_count >= $max_emails) {
                log_message('warning', "Limite d'emails atteinte ({$max_emails}) pour le contexte: {$context}");
                break;
            }
            
            $result = $this->cpanel_email_lib->send_email($email, $subject, $message);
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                log_message('error', "Échec d'envoi à {$email} pour {$context} : " . print_r($result, true));
            }
            $email_count++;
        }
        
        log_message('info', "Emails envoyés pour {$context} : {$success_count} succès, {$error_count} échecs");
        
        return array('success' => $success_count, 'error' => $error_count);
    }

    private function getAllEmails()
    {
        $emails = array();
        
        $active_users = $this->Model->read('users', array('is_active' => 1, 'deleted_at' => null), 'id', 'ASC');
        $newsletter_emails = $this->Model->read('newsletter', null, 'id_newsletter', 'ASC');
        
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
        
        return $emails;
    }

    // ================== NOTIFICATION NOUVEAU PRODUIT ==================
    
    private function sendProductNotification($product, $action = 'create')
    {
        if ($action !== 'create') {
            return ['success' => 0, 'error' => 0];
        }

        $required_fields = array('id', 'title', 'price', 'description', 'main_image', 'slug');
        foreach ($required_fields as $field) {
            if (!isset($product[$field])) {
                log_message('error', "Champ manquant dans le produit: " . $field);
                return array('success' => 0, 'error' => 1);
            }
        }

        $emails = $this->getAllEmails();
        
        if (empty($emails)) {
            log_message('info', "Aucun email valide trouvé pour la notification du produit ID " . $product['id']);
            return array('success' => 0, 'error' => 0);
        }
        
        $product_image_url = base_url('attachments/Products/' . $product['main_image']);
        if (!file_exists(FCPATH . 'attachments/Products/' . $product['main_image']) || $product['main_image'] === 'default-product.png') {
            $product_image_url = base_url('assets/images/default-product.jpg');
        }
        
        $site_url = base_url();
        $product_url = base_url('product/' . $product['id'] . '_' . $product['slug']);
        
        $subject = "NOUVEAU PRODUIT CHEZ NUFOTEC : " . htmlspecialchars($product['title_fr'] ?: $product['title']);
        $message = $this->buildProductNotificationTemplate(
            htmlspecialchars($product['title_fr'] ?: $product['title']),
            $this->formatPrice($product['price']),
            nl2br(htmlspecialchars($product['description_fr'] ?: $product['description'])),
            $product_image_url,
            $product_url,
            $subject,
            $site_url
        );
        
        return $this->sendEmails($emails, $subject, $message, 'new_product_' . $product['id']);
    }

    private function formatPrice($price, $lang = 'fr')
    {
        $price_value = floatval($price);
        return number_format($price_value, 0, ',', ' ') . ' FBu';
    }

    private function buildProductNotificationTemplate($product_title, $product_price, $product_description, $product_image_url, $product_url, $subject, $site_url)
    {
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
                    background: #0a2540;
                    padding: 30px 24px;
                    text-align: center;
                }
                .header h1 {
                    color: #ffffff;
                    font-size: 22px;
                    font-weight: 600;
                    margin: 0;
                }
                .header p {
                    color: rgba(255,255,255,0.8);
                    font-size: 14px;
                    margin: 8px 0 0;
                }
                .product-image {
                    width: 100%;
                    height: auto;
                    max-height: 300px;
                    object-fit: cover;
                }
                .content {
                    padding: 28px;
                }
                .product-title {
                    font-size: 22px;
                    font-weight: 700;
                    color: #1a2a3a;
                    margin-bottom: 15px;
                }
                .price-box {
                    background: #f7f9fc;
                    border-radius: 12px;
                    padding: 15px;
                    margin: 15px 0;
                    text-align: center;
                    border: 1px solid #e8ecf0;
                }
                .price-label {
                    font-size: 13px;
                    color: #8a9aaa;
                    margin-bottom: 5px;
                }
                .price-value {
                    font-size: 28px;
                    font-weight: 700;
                    color: #0a66c2;
                }
                .description {
                    color: #5a6a7a;
                    font-size: 14px;
                    margin: 20px 0;
                    line-height: 1.6;
                }
                .btn {
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
                @media (max-width: 560px) {
                    .content { padding: 20px; }
                    .product-title { font-size: 18px; }
                    .price-value { font-size: 22px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>NOUVEAU PRODUIT</h1>
                    <p>NUFOTEC BURUNDI</p>
                </div>
                <img src="' . $product_image_url . '" alt="' . $product_title . '" class="product-image">
                <div class="content">
                    <div class="product-title">' . $product_title . '</div>
                    <div class="price-box">
                        <div class="price-label">Prix</div>
                        <div class="price-value">' . $product_price . '</div>
                    </div>
                    <div class="description">' . $product_description . '</div>
                    <div style="text-align: center;">
                        <a href="' . $product_url . '" class="btn">Voir le produit</a>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-text">© ' . date('Y') . ' NUFOTEC BURUNDI - Tous droits réservés</div>
                    <div class="footer-text"><a href="' . $site_url . 'unsubscribe" style="color:#9aaab9;">Se désabonner</a></div>
                </div>
            </div>
        </body>
        </html>';
    }

    // ================== PROMOTION QUOTIDIENNE ==================

    public function promo()
    {
        $result = $this->sendDailyPromoEmail();
        
        if ($result['success'] > 0) {
            $this->session->set_flashdata('success', 'Email promotionnel envoyé avec succès à ' . $result['success'] . ' destinataires.');
        } else {
            $this->session->set_flashdata('error', 'Aucun email envoyé. Vérifiez les logs pour plus de détails.');
        }
        
        redirect(base_url('advertise-product'));
    }

    public function cron_daily_promo($secret_key = null)
{
    // Accepter la clé via GET ou via paramètre d'URL
    $valid_key = 'nufotec_promo_2024';
    $input_key = $secret_key ?: $this->input->get('key');
    
    if ($input_key !== $valid_key) {
        // Au lieu de show_404(), retourner une erreur claire
        echo "Erreur: Clé de sécurité invalide. Reçu: " . $input_key . "\n";
        echo "Attendu: " . $valid_key . "\n";
        return;
    }
    
    set_time_limit(300);
    $result = $this->sendDailyPromoEmail();
    
    header('Content-Type: text/plain');
    echo "NUFOTEC Daily Promo - " . date('Y-m-d H:i:s') . "\n";
    echo "Succès: " . $result['success'] . "\n";
    echo "Échecs: " . $result['error'] . "\n";
    echo "Total: " . ($result['success'] + $result['error']) . "\n";
}


    private function sendDailyPromoEmail()
    {
        $featured_products = $this->Model->read('advertise_product', array('is_active' => 1, 'in_vedette' => 1), 'id', 'DESC', 5);
        
        if (empty($featured_products)) {
            $featured_products = $this->Model->read('advertise_product', array('is_active' => 1), 'id', 'DESC', 3);
        }
        
        if (empty($featured_products)) {
            log_message('info', 'Aucun produit à promouvoir aujourd\'hui');
            return array('success' => 0, 'error' => 0);
        }
        
        $emails = $this->getAllEmails();
        
        if (empty($emails)) {
            log_message('warning', 'Aucun email pour la promo quotidienne');
            return array('success' => 0, 'error' => 0);
        }
        
        $products_html = $this->buildPromoProductsHtml($featured_products);
        $subject = "Offres du jour NUFOTEC - Découvrez nos produits";
        $message = $this->buildDailyPromoTemplate($products_html, $subject);
        
        return $this->sendEmails($emails, $subject, $message, 'daily_promo');
    }

    private function buildPromoProductsHtml($products)
    {
        $html = '';
        
        foreach ($products as $product) {
            $product_image_url = base_url('attachments/Products/' . $product['main_image']);
            if (!file_exists(FCPATH . 'attachments/Products/' . $product['main_image']) || $product['main_image'] == 'default-product.png') {
                $product_image_url = base_url('assets/images/default-product.jpg');
            }
            
            $product_url = base_url('product/' . $product['id'] . '_' . $product['slug']);
            $product_title = htmlspecialchars($product['title_fr'] ?: $product['title']);
            $product_price = $this->formatPrice($product['price']);
            
            $html .= '
            <div style="background: #ffffff; border-radius: 12px; margin-bottom: 20px; overflow: hidden; border: 1px solid #eef2f6;">
                <img src="' . $product_image_url . '" alt="' . $product_title . '" style="width: 100%; height: 180px; object-fit: cover;">
                <div style="padding: 16px;">
                    <h3 style="color: #1a2a3a; margin: 0 0 8px; font-size: 16px;">' . $product_title . '</h3>
                    <p style="color: #0a66c2; font-size: 18px; font-weight: bold; margin: 0 0 12px;">' . $product_price . '</p>
                    <a href="' . $product_url . '" style="display: inline-block; background: #0a2540; color: #ffffff; padding: 8px 20px; text-decoration: none; border-radius: 25px; font-size: 13px;">Découvrir</a>
                </div>
            </div>';
        }
        
        return $html;
    }

    private function buildDailyPromoTemplate($products_html, $subject)
    {
        $site_url = base_url();
        $current_date = date('d/m/Y');
        
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
                .intro {
                    background: #f8fafc;
                    padding: 20px;
                    text-align: center;
                    border-bottom: 1px solid #eef2f6;
                }
                .intro p {
                    color: #5a6a7a;
                    font-size: 14px;
                }
                .products {
                    padding: 20px;
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
                .btn-shop {
                    display: inline-block;
                    background: #0a66c2;
                    color: white;
                    padding: 12px 28px;
                    text-decoration: none;
                    border-radius: 40px;
                    font-weight: 600;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Offres du jour</h1>
                    <p>' . $current_date . '</p>
                </div>
                <div class="intro">
                    <p>Découvrez notre sélection de produits naturels pour votre bien-être quotidien.</p>
                </div>
                <div class="products">' . $products_html . '</div>
                <div style="text-align: center; padding: 0 20px 20px;">
                    <a href="' . $site_url . 'advertise-product" class="btn-shop">Voir tous nos produits</a>
                </div>
                <div class="footer">
                    <div class="footer-text">© ' . date('Y') . ' NUFOTEC BURUNDI - Votre partenaire santé naturelle</div>
                    <div class="footer-text"><a href="' . $site_url . 'unsubscribe" style="color:#9aaab9;">Se désabonner</a></div>
                </div>
            </div>
        </body>
        </html>';
    }

    // ================== CRUD PRODUITS ==================

    function Create()
    {
        $this->form_validation->set_rules('title_fr', 'Titre (FR)', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description_fr', 'Description (FR)', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title_fr = $this->input->post('title_fr');
        $slug = $this->generate_slug($title_fr);
        $category_id = $this->input->post('category_id') ?: NULL;
        
        $title_en = $this->input->post('title_en');
        $title_sw = $this->input->post('title_sw');
        $description_fr = $this->input->post('description_fr');
        $description_en = $this->input->post('description_en');
        $description_sw = $this->input->post('description_sw');
        
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
            'title' => $title_fr,
            'title_fr' => $title_fr,
            'title_en' => $title_en,
            'title_sw' => $title_sw,
            'slug' => $slug,
            'description' => $description_fr,
            'description_fr' => $description_fr,
            'description_en' => $description_en,
            'description_sw' => $description_sw,
            'price' => $this->input->post('price'),
            'is_active' => 1,
            'in_vedette' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('advertise_product', $data);

        if ($rsp) {
            $new_product = $this->Model->readOne('advertise_product', array('slug' => $slug));
            $notification_result = $this->sendProductNotification($new_product, 'create');
            
            $this->session->set_flashdata('success', 'Produit créé avec succès. ' . $notification_result['success'] . ' notifications envoyées.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du produit.');
        }
        redirect(base_url('advertise-product'));
    }

    function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('title_fr', 'Titre (FR)', 'required');
        $this->form_validation->set_rules('price', 'Prix', 'required');
        $this->form_validation->set_rules('description_fr', 'Description (FR)', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('advertise-product'));
            return;
        }

        $title_fr = $this->input->post('title_fr');
        $slug = $this->generate_slug($title_fr, $id);
        $category_id = $this->input->post('category_id') ?: NULL;
        $is_active = $this->input->post('is_active') ? 1 : 0;
        $in_vedette = $this->input->post('in_vedette') ? 1 : 0;
        
        $title_en = $this->input->post('title_en');
        $title_sw = $this->input->post('title_sw');
        $description_fr = $this->input->post('description_fr');
        $description_en = $this->input->post('description_en');
        $description_sw = $this->input->post('description_sw');

        $data = array(
            'category_id' => $category_id,
            'title' => $title_fr,
            'title_fr' => $title_fr,
            'title_en' => $title_en,
            'title_sw' => $title_sw,
            'slug' => $slug,
            'description' => $description_fr,
            'description_fr' => $description_fr,
            'description_en' => $description_en,
            'description_sw' => $description_sw,
            'price' => $this->input->post('price'),
            'is_active' => $is_active,
            'in_vedette' => $in_vedette,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!empty($_FILES['main_image']['name'])) {
            $new_image = $this->upload_image($_FILES['main_image']['tmp_name'], $_FILES['main_image']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide.');
                redirect(base_url('advertise-product'));
                return;
            }
            
            $product = $this->Model->readOne('advertise_product', array('id' => $id));
            if ($product && $product['main_image'] != 'default-product.png' && file_exists(FCPATH . 'attachments/Products/' . $product['main_image'])) {
                unlink(FCPATH . 'attachments/Products/' . $product['main_image']);
            }
            
            $data['main_image'] = $new_image;
        }

        $rsp = $this->Model->update('advertise_product', array('id' => $id), $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Produit mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('advertise-product'));
    }

    function Delete()
    {
        $id = $this->input->post('id');
        
        $product = $this->Model->readOne('advertise_product', array('id' => $id));
        
        $rsp = $this->Model->delete('advertise_product', array('id' => $id));

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

    public function upload_image($tmp_name, $filename)
    {
        $ref_folder = FCPATH . 'attachments/Products/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($tmp_name, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }
}