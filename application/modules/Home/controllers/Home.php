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
     * Page d'accueil dynamique (français uniquement)
     */
    public function index()
    {
        $this->Model->log_visit();

        $data['show_translator'] = false;

        $page = static_pages_one(['slug' => 'home', 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');

        // Données spécifiques à la home
        $data['slides']   = $this->Model->read('hero_slides', ['is_active' => 1], 'slide_order', 'ASC');
        $data['appels_action'] = $this->Model->get_appels_action_translated('fr');

        // Produits (table advertise_product)
        $data['produits'] = $this->db->select("id, main_image, slug, price, created_at, title, description, in_vedette")
            ->where('is_active', 1)
            ->where('deleted_at IS NULL')
            ->order_by('in_vedette', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(8)
            ->get('advertise_product')
            ->result_array();

        // Données communes (sections, enfants, etc.)
        $data = array_merge($data, $this->_get_page_data($page['id_page']));

        // SEO
        $data = array_merge($data, $this->_prepare_seo_data($page));
        $data['meta_title']       = $page['titre_page'] . ' - ' . $this->Model->get_setting('site_name', 'AGF Phytomed');
        $data['meta_description'] = $page['meta_description'] ?? $this->Model->get_setting('site_description');

        $this->load->view('Home_View', $data);
    }

    /**
     * Afficher une page dynamique par slug (français uniquement)
     */
    public function view($slug = null)
    {
        if (!$slug) redirect('/');

        $slug = $this->security->xss_clean($slug);

        $page = static_pages_one(['slug' => $slug, 'est_publiee' => 1]);
        if (!$page) show_404();

        $data['page'] = $page;

        // Données communes
        $data = array_merge($data, $this->_get_page_data($page['id_page']));
        $data = array_merge($data, $this->_prepare_seo_data($page));

        $view_name = $this->_determine_view($page);
        $this->load->view($view_name, $data);
    }

    /**
     * Récupère toutes les données communes à une page (sections, enfants, breadcrumb)
     */
    private function _get_page_data($page_id)
    {
        $data = [];

        // ----- 1. Sections de contenu (français uniquement) -----
        $sections = static_sections_where(['id_page' => $page_id, 'deleted_at' => null], 'ordre', 'ASC');
        $data['sections'] = $sections;

        // ----- 2. Pages enfants (sous-pages) -----
        $children = static_pages_where(['menu_parent_id' => $page_id, 'est_publiee' => 1], 'menu_ordre', 'ASC');
        $data['children'] = $children;

        // ----- 3. Pages sœurs (même parent) -----
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
     * Détermine la vue à afficher pour une page dynamique
     * (template générique rendant les sections statiques de la page)
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
     * Prépare les métadonnées SEO
     */
    private function _prepare_seo_data($page)
    {
        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $site_description = $this->Model->get_setting('site_description', 'Pionniers de la phytothérapie africaine');

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
    // Charger la librairie email personnalisée
    $this->load->library('Cpanel_email_lib');
    
    // Vérification du token CSRF pour sécurité
    if ($this->input->post('csrf_test_name') !== $this->security->get_csrf_hash()) {
        $this->session->set_flashdata('error', 'Token de sécurité invalide');
        redirect($_SERVER['HTTP_REFERER']);
    }

    $sub_type = $this->input->post('sub_type', TRUE);
    
    // Validation en fonction du type d'abonnement
    if ($sub_type === 'email') {
        $email = $this->input->post('email', TRUE);
        
        // Validation email
        if (empty($email) || !$this->cpanel_email_lib->validate_email($email)) {
            $this->session->set_flashdata('error', 'Adresse email invalide');
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        // Vérifier si l'email existe déjà
        $this->db->where('email', $email);
        $exists = $this->db->get('newsletter')->row();
        
        if ($exists) {
            $this->session->set_flashdata('warning', 'Cet email est déjà inscrit à la newsletter');
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        // Insertion par email
        $data = [
            'email' => $email,
            'telephone' => null,
            'date_inscription' => date('Y-m-d H:i:s')
        ];
        
    } elseif ($sub_type === 'phone') {
        $pays_code = $this->input->post('pays_code', TRUE);
        $indicatif = $this->input->post('indicatif_complet', TRUE);
        $telephone = $this->input->post('telephone', TRUE);
        
        // Validation téléphone
        if (empty($pays_code) || empty($indicatif) || empty($telephone)) {
            $this->session->set_flashdata('error', 'Veuillez sélectionner un pays et saisir un numéro valide');
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        // Nettoyage du numéro
        $telephone_clean = preg_replace('/[^0-9]/', '', $telephone);
        
        // Vérifier longueur minimale
        if (strlen($telephone_clean) < 8) {
            $this->session->set_flashdata('error', 'Numéro de téléphone trop court');
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        // Insertion par téléphone
        $data = [
            'email' => null,
            'telephone' => '+' . $indicatif . ' ' . $telephone_clean,
            'date_inscription' => date('Y-m-d H:i:s')
        ];
        
    } else {
        $this->session->set_flashdata('error', 'Type d\'abonnement invalide');
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    // Insertion en base de données
    try {
        // La table newsletter doit accepter NULL pour email ou telephone
        $inserted = $this->db->insert('newsletter', $data);
        
        if ($inserted) {
            // Envoyer email de confirmation (si email)
            if ($sub_type === 'email') {
                // Récupérer les informations du site pour personnaliser l'email
                $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
                
                // Générer un code de confirmation (optionnel)
                $confirmation_code = random_string('numeric', 6);
                
                // Envoyer l'email de confirmation avec la librairie personnalisée
                $email_result = $this->sendNewsletterConfirmation($data['email'], $confirmation_code);
                
                if ($email_result['success']) {
                    log_message('info', 'Email de confirmation newsletter envoyé à: ' . $data['email']);
                } else {
                    log_message('error', 'Erreur envoi email newsletter: ' . $email_result['message']);
                }
            }
            
            // Envoyer SMS de confirmation (si téléphone)
            if ($sub_type === 'phone') {
                $this->sendConfirmationSMS($data['telephone']);
            }
            
            $this->session->set_flashdata('success', 'Félicitations ! Vous êtes bien inscrit à notre newsletter');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de l\'inscription. Veuillez réessayer');
        }
        
    } catch (Exception $e) {
        log_message('error', 'Newsletter subscription error: ' . $e->getMessage());
        $this->session->set_flashdata('error', 'Une erreur technique est survenue');
    }
    
    redirect($_SERVER['HTTP_REFERER']);
}

/**
 * Envoi d'email de confirmation pour la newsletter
 */
private function sendNewsletterConfirmation($email, $confirmation_code = null) {
    // Récupérer les informations du site
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
                content: "✓";
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
                <div class="greeting">Bienvenue dans notre communauté !</div>
                
                <div class="message-text">
                    Merci de vous être inscrit à notre newsletter. Vous recevrez désormais nos actualités, 
                    promotions exclusives et dernières nouveautés directement dans votre boîte mail.
                </div>
                
                <div class="success-icon">
                    <i>✅</i>
                </div>
                
                <div class="benefits">
                    <h3>Ce que vous recevrez :</h3>
                    <ul>
                        <li>Nos actualités et événements à venir</li>
                        <li>Offres promotionnelles exclusives</li>
                        <li>Nouveautés et innovations technologiques</li>
                        <li>Conseils et astuces pour réussir</li>
                    </ul>
                </div>
                
                <div class="message-text" style="font-size: 13px; text-align: center; color: #8a9aaa;">
                    Vous pouvez vous désinscrire à tout moment en cliquant sur le lien de désabonnement 
                    présent dans chaque newsletter.
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits réservés</div>';
    
    if (!empty($logo_url)) {
        $message .= '
                <img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="footer-logo">';
    }
    
    $message .= '
            </div>
        </div>
    </body>
    </html>';
    
    // Envoyer l'email avec la librairie personnalisée
    return $this->cpanel_email_lib->send_email($email, $subject, $message);
}

/**
 * Envoi de SMS de confirmation (à implémenter avec une API SMS)
 */
private function sendConfirmationSMS($phoneNumber) {
    // Intégration avec une API SMS (Twilio, Vonage, etc.)
    // Exemple avec CURL vers un service SMS
    /*
    $ch = curl_init('https://api.sms-service.com/send');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'to' => $phoneNumber,
        'message' => 'Merci pour votre inscription à la newsletter NUFOTEC !'
    ]));
    curl_exec($ch);
    curl_close($ch);
    */
    
    // Pour l'instant, on log simplement
    log_message('info', 'SMS confirmation sent to: ' . $phoneNumber);
}

/**
 * Méthode pour désabonner un utilisateur
 */
public function Desabonner($email = null) {
    if ($email) {
        $this->db->where('email', $email);
        $this->db->delete('newsletter');
        
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Vous avez été désinscrit de la newsletter');
        } else {
            $this->session->set_flashdata('error', 'Email non trouvé dans notre liste');
        }
    }
    
    redirect($_SERVER['HTTP_REFERER']);
}

/**
 * Méthode pour envoyer une newsletter à tous les abonnés
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
        
        // Petite pause pour éviter la surcharge du serveur
        usleep(100000); // 0.1 seconde
    }
    
    return [
        'total' => count($subscribers),
        'success' => $success_count,
        'fail' => $fail_count
    ];
}
    /**
     * Changer de langue - redirige simplement vers l'accueil (plus utilisé)
     */
    public function switch_lang($lang = 'fr')
    {
        // Rediriger vers la page d'accueil
        redirect(base_url());
    }
}