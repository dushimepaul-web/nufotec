<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
        $this->load->model('Model');
         $this->load->helper('string');
    }

    /**
     * Page d'accueil dynamique (franÃ§ais uniquement)
     */
    public function index()
    {
        $this->Model->log_visit();

        $data['show_translator'] = false;

        $page = static_pages_one(['slug' => 'home', 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');

        // Produits (table advertise_product)
        $data['produits'] = $this->db->select("id, main_image, slug, price, created_at, title, description, in_vedette")
            ->where('is_active', 1)
            ->where('deleted_at IS NULL')
            ->order_by('in_vedette', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(8)
            ->get('advertise_product')
            ->result_array();

        // Chiffres clés (table chiffres_cles)
        $data['chiffres_cles'] = $this->db->select('id_chiffre, etiquette, valeur, unite, description, icone, ordre, annee_vision')
            ->order_by('ordre', 'ASC')
            ->order_by('id_chiffre', 'ASC')
            ->get('chiffres_cles')
            ->result_array();

        // DonnÃ©es communes (sections, enfants, etc.)
        $data = array_merge($data, $this->_get_page_data($page['id_page']));

        // SEO
        $data = array_merge($data, $this->_prepare_seo_data($page));
        $data['meta_title']       = $page['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $page['meta_description'] ?? $this->Model->get_setting('site_description');

        $this->load->view('Home_View', $data);
    }

    /**
     * Afficher une page dynamique par slug (franÃ§ais uniquement)
     */
    public function view($slug = null)
    {
        if (!$slug) redirect('/');

        $slug = $this->security->xss_clean($slug);

        $page = static_pages_one(['slug' => $slug, 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;

        // DonnÃ©es communes
        $data = array_merge($data, $this->_get_page_data($page['id_page']));
        $data = array_merge($data, $this->_prepare_seo_data($page));

        $view_name = $this->_determine_view($page);
        $this->load->view($view_name, $data);
    }

    /**
     * RÃ©cupÃ¨re toutes les donnÃ©es communes Ã  une page (sections, enfants, breadcrumb)
     */
    private function _get_page_data($page_id)
    {
        $data = [];

        // ----- 1. Sections de contenu (franÃ§ais uniquement) -----
        $sections = static_sections_where(['id_page' => $page_id, 'deleted_at' => null], 'ordre', 'ASC');
        $data['sections'] = $sections;

        // ----- 2. Pages enfants (sous-pages) -----
        $children = static_pages_where(['menu_parent_id' => $page_id, 'est_publiee' => 1], 'menu_ordre', 'ASC');
        $data['children'] = $children;

        // ----- 3. Pages sÅ“urs (mÃªme parent) -----
        $parent = static_pages_one(['id_page' => $page_id]);
        $parent_id = $parent['menu_parent_id'] ?? null;
        if ($parent_id) {
            $siblings = static_pages_where(['menu_parent_id' => $parent_id, 'est_publiee' => 1, 'id_page !=' => $page_id], 'menu_ordre', 'ASC');
            $data['siblings'] = $siblings;
        }

        // ----- 4. Fil d'Ariane -----
        $data['breadcrumb'] = $this->_build_breadcrumb($page_id);

        return $data;
    }

    /**
     * DÃ©termine la vue Ã  afficher pour une page dynamique
     * (template gÃ©nÃ©rique rendant les sections statiques de la page)
     */
    private function _determine_view($page)
    {
        return 'Page_View';
    }

    /**
     * Construit le fil d'Ariane
     */
    private function _build_breadcrumb($page_id)
    {
        $breadcrumb = [];

        while ($page_id) {
            $page = static_pages_one(['id_page' => $page_id]);
            if (!$page) break;

            $url = ($page['slug'] === 'home')
                ? base_url()
                : base_url($page['slug']);

            array_unshift($breadcrumb, [
                'titre' => $page['titre_page'],
                'slug'  => $page['slug'],
                'url'   => $url
            ]);

            $page_id = $page['menu_parent_id'];
        }

        return $breadcrumb;
    }

    /**
     * PrÃ©pare les mÃ©tadonnÃ©es SEO
     */
    private function _prepare_seo_data($page)
    {
        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $site_description = $this->Model->get_setting('site_description', 'Pionniers de la phytothÃ©rapie africaine');

        $meta_title = (!empty($page['meta_title']) ? $page['meta_title'] : $page['titre_page']) . ' - ' . $site_name;
        $meta_description = !empty($page['meta_description']) ? $page['meta_description'] : $site_description;

        return [
            'site_name'        => $site_name,
            'site_description' => $site_description,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords'    => $page['meta_keywords'] ?? '',
            'og_image'         => !empty($page['image_social']) ? base_url($page['image_social']) : base_url('assets/images/og-default.jpg'),
            'canonical_url'    => base_url($page['slug'] ?? '')
        ];
    }



    public function Abonner() {
    // Charger la librairie email personnalisÃ©e
    $this->load->library('Cpanel_email_lib');
    
    $is_ajax = $this->input->is_ajax_request();
    
    // RÃ©ponse JSON si la requÃªte est AJAX
    $respond = function ($type, $message) use ($is_ajax) {
        if ($type !== 'error' && $type !== 'warning' && $type !== 'success') {
            $type = 'success';
        }
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $type === 'success', 'status' => $type, 'message' => $message]);
            exit;
        }
        $this->session->set_flashdata($type, $message);
        redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url());
    };
    
// La protection CSRF globale de CodeIgniter est deja active (verifiee avant l'appel du controleur)

    $sub_type = $this->input->post('sub_type', TRUE);
    $email = $this->input->post('email', TRUE);
    $telephone = $this->input->post('telephone', TRUE);

    // DÃ©tection automatique du type si sub_type absent (ancien formulaire Home_View)
    if (empty($sub_type)) {
        if (!empty($email)) {
            $sub_type = 'email';
        } elseif (!empty($telephone)) {
            $sub_type = 'phone';
        } else {
            $respond('error', 'Veuillez saisir votre email ou votre numÃ©ro de tÃ©lÃ©phone');
            return;
        }
    }

    // Validation en fonction du type d'abonnement
    if ($sub_type === 'email') {
        $email = $this->input->post('email', TRUE);
        
        // Validation email
        if (empty($email) || !$this->cpanel_email_lib->validate_email($email)) {
            $respond('error', 'Adresse email invalide');
            return;
        }
        
        // VÃ©rifier si l'email existe dÃ©jÃ 
        $this->db->where('email', $email);
        $exists = $this->db->get('newsletter')->row();
        
        if ($exists) {
            $respond('warning', 'Cet email est dÃ©jÃ  inscrit Ã  la newsletter');
            return;
        }
        
        // TÃ©lÃ©phone optionnel (prÃ©sent dans le formulaire Home)
        $telephone = $this->input->post('telephone', TRUE);
        $telephone_clean = !empty($telephone) ? preg_replace('/[^0-9]/', '', $telephone) : null;
        
        // Insertion par email
        $data = [
            'email' => $email,
            'telephone' => !empty($telephone_clean) ? $telephone_clean : null,
            'date_inscription' => date('Y-m-d H:i:s')
        ];
        
    } elseif ($sub_type === 'phone') {
        $pays_code = $this->input->post('pays_code', TRUE);
        $indicatif = $this->input->post('indicatif_complet', TRUE);
        $telephone = $this->input->post('telephone', TRUE);
        
        // Validation tÃ©lÃ©phone (l'indicatif est optionnel)
        if (empty($telephone)) {
            $respond('error', 'Veuillez saisir un numÃ©ro de tÃ©lÃ©phone valide');
            return;
        }
        
        // Nettoyage du numÃ©ro
        $telephone_clean = preg_replace('/[^0-9]/', '', $telephone);
        
        // VÃ©rifier longueur minimale
        if (strlen($telephone_clean) < 8) {
            $respond('error', 'NumÃ©ro de tÃ©lÃ©phone trop court');
            return;
        }
        
        // Insertion par tÃ©lÃ©phone
        $data = [
            'email' => null,
            'telephone' => (!empty($indicatif) ? '+' . $indicatif . ' ' : '') . $telephone_clean,
            'date_inscription' => date('Y-m-d H:i:s')
        ];
        
    } else {
        $respond('error', 'Type d\'abonnement invalide');
        return;
    }
    
    // Insertion en base de donnÃ©es
    try {
        // La table newsletter doit accepter NULL pour email ou telephone
        $inserted = $this->db->insert('newsletter', $data);
        
        if ($inserted) {
            // Envoyer email de confirmation (si email)
            if ($sub_type === 'email') {
                // RÃ©cupÃ©rer les informations du site pour personnaliser l'email
                $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
                
                // GÃ©nÃ©rer un code de confirmation (optionnel)
                $confirmation_code = random_string('numeric', 6);
                
                // Envoyer l'email de confirmation avec la librairie personnalisÃ©e
                $email_result = $this->sendNewsletterConfirmation($data['email'], $confirmation_code);
                
                if ($email_result['success']) {
                    log_message('info', 'Email de confirmation newsletter envoyÃ© Ã : ' . $data['email']);
                } else {
                    log_message('error', 'Erreur envoi email newsletter: ' . $email_result['message']);
                }
            }
            
            // Envoyer SMS de confirmation (si tÃ©lÃ©phone)
            if ($sub_type === 'phone') {
                $this->sendConfirmationSMS($data['telephone']);
            }
            
            $respond('success', 'FÃ©licitations ! Vous Ãªtes bien inscrit Ã  notre newsletter');
            return;
        } else {
            $respond('error', 'Erreur lors de l\'inscription. Veuillez rÃ©essayer');
            return;
        }
        
    } catch (Exception $e) {
        log_message('error', 'Newsletter subscription error: ' . $e->getMessage());
        $respond('error', 'Une erreur technique est survenue');
        return;
    }
}

/**
 * Envoi d'email de confirmation pour la newsletter
 */
private function sendNewsletterConfirmation($email, $confirmation_code = null) {
    // RÃ©cupÃ©rer les informations du site
    $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
    $site_logo = $this->Model->get_setting('site_logo');
    $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';
    
    $subject = 'Confirmation d\'inscription - ' . $site_name;
    
    // Construire le message HTML
    $message = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmation Newsletter - ' . htmlspecialchars($site_name) . '</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 20px;
                line-height: 1.5;
            }
            .container {
                max-width: 520px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            .header {
                background: linear-gradient(135deg, #0a2540 0%, #1a3a5c 100%);
                padding: 32px 24px;
                text-align: center;
            }
            .logo {
                max-height: 55px;
                width: auto;
                margin-bottom: 12px;
            }
            .site-title {
                color: #ffffff;
                font-size: 20px;
                font-weight: 600;
                letter-spacing: -0.3px;
            }
            .content {
                padding: 32px 28px;
            }
            .greeting {
                font-size: 24px;
                font-weight: 700;
                color: #1a2a3a;
                margin-bottom: 12px;
            }
            .message-text {
                color: #5a6a7a;
                font-size: 15px;
                margin-bottom: 28px;
                line-height: 1.6;
            }
            .success-icon {
                text-align: center;
                margin: 24px 0;
            }
            .success-icon i {
                font-size: 64px;
                color: #00b894;
            }
            .benefits {
                background: #f7f9fc;
                border-radius: 14px;
                padding: 20px;
                margin: 24px 0;
                border: 1px solid #e8ecf0;
            }
            .benefits h3 {
                font-size: 16px;
                color: #1a2a3a;
                margin-bottom: 12px;
                font-weight: 600;
            }
            .benefits ul {
                list-style: none;
                padding: 0;
            }
            .benefits li {
                padding: 8px 0;
                color: #5a6a7a;
                font-size: 14px;
                display: flex;
                align-items: center;
            }
            .benefits li:before {
                content: "âœ“";
                color: #00b894;
                font-weight: bold;
                margin-right: 10px;
            }
            .footer {
                background: #f8fafc;
                padding: 24px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            .footer-text {
                font-size: 12px;
                color: #9aaab9;
            }
            .footer-logo {
                max-height: 32px;
                width: auto;
                margin-top: 12px;
                opacity: 0.5;
            }
            @media (max-width: 560px) {
                body {
                    padding: 12px;
                }
                .content {
                    padding: 24px 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">';
    
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="logo">';
    }
    
    $message .= '
                <div class="site-title">' . htmlspecialchars($site_name) . '</div>
            </div>
            
            <div class="content">
                <div class="greeting">Bienvenue dans notre communautÃ© !</div>
                
                <div class="message-text">
                    Merci de vous Ãªtre inscrit Ã  notre newsletter. Vous recevrez dÃ©sormais nos actualitÃ©s, 
                    promotions exclusives et derniÃ¨res nouveautÃ©s directement dans votre boÃ®te mail.
                </div>
                
                <div class="success-icon">
                    <i>âœ…</i>
                </div>
                
                <div class="benefits">
                    <h3>Ce que vous recevrez :</h3>
                    <ul>
                        <li>Nos actualitÃ©s et Ã©vÃ©nements Ã  venir</li>
                        <li>Offres promotionnelles exclusives</li>
                        <li>NouveautÃ©s et innovations technologiques</li>
                        <li>Conseils et astuces pour rÃ©ussir</li>
                    </ul>
                </div>
                
                <div class="message-text" style="font-size: 13px; text-align: center; color: #8a9aaa;">
                    Vous pouvez vous dÃ©sinscrire Ã  tout moment en cliquant sur le lien de dÃ©sabonnement 
                    prÃ©sent dans chaque newsletter.
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-text">Â© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits rÃ©servÃ©s</div>';
    
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="footer-logo">';
    }
    
    $message .= '
            </div>
        </div>
    </body>
    </html>';
    
    // Envoyer l'email avec la librairie personnalisÃ©e
    return $this->cpanel_email_lib->send_email($email, $subject, $message);
}

/**
 * Envoi de SMS de confirmation (Ã  implÃ©menter avec une API SMS)
 */
private function sendConfirmationSMS($phoneNumber) {
    // IntÃ©gration avec une API SMS (Twilio, Vonage, etc.)
    // Exemple avec CURL vers un service SMS
    /*
    $ch = curl_init('https://api.sms-service.com/send');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'to' => $phoneNumber,
        'message' => 'Merci pour votre inscription Ã  la newsletter NUFOTEC !'
    ]));
    curl_exec($ch);
    curl_close($ch);
    */
    
    // Pour l'instant, on log simplement
    log_message('info', 'SMS confirmation sent to: ' . $phoneNumber);
}

/**
 * MÃ©thode pour dÃ©sabonner un utilisateur
 */
public function Desabonner($email = null) {
    if ($email) {
        $this->db->where('email', $email);
        $this->db->delete('newsletter');
        
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Vous avez Ã©tÃ© dÃ©sinscrit de la newsletter');
        } else {
            $this->session->set_flashdata('error', 'Email non trouvÃ© dans notre liste');
        }
    }
    
    redirect($_SERVER['HTTP_REFERER']);
}

/**
 * MÃ©thode pour envoyer une newsletter Ã  tous les abonnÃ©s
 */
public function sendMassNewsletter($subject, $message) {
    $this->db->select('email');
    $this->db->where('email IS NOT NULL', null, false);
    $subscribers = $this->db->get('newsletter')->result();
    
    $success_count = 0;
    $fail_count = 0;
    
    foreach ($subscribers as $subscriber) {
        $result = $this->cpanel_email_lib->send_email($subscriber->email, $subject, $message);
        
        if ($result['success']) {
            $success_count++;
        } else {
            $fail_count++;
            log_message('error', 'Failed to send newsletter to: ' . $subscriber->email);
        }
        
        // Petite pause pour Ã©viter la surcharge du serveur
        usleep(100000); // 0.1 seconde
    }
    
    return [
        'total' => count($subscribers),
        'success' => $success_count,
        'fail' => $fail_count
    ];
}
    /**
     * Changer de langue - redirige simplement vers l'accueil (plus utilisÃ©)
     */
    public function switch_lang($lang = 'fr')
    {
        // Rediriger vers la page d'accueil
        redirect(base_url());
    }
}
