<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('form_validation');
        
        // ============================================
        // CHARGER LA LIBRAIRIE EMAIL cPanel
        // ============================================
        $this->load->library('cpanel_email_lib');
    }

    /**
     * Page de paiement
     * @param string $numero_consultation Numéro de consultation
     */
    public function index($numero_consultation = null) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Veuillez vous connecter pour accéder au paiement.');
            redirect('Auth');
            return;
        }

        // Vérifier si le numéro de consultation est fourni
        if (!$numero_consultation) {
            show_404();
            return;
        }

        // Récupérer la consultation par son NUMERO
        $consultation = $this->Model->getConsultationByNumber($numero_consultation);
        
        if (!$consultation) {
            $this->session->set_flashdata('error', 'Consultation introuvable.');
            redirect('Dashboard/patient_dashboard');
            return;
        }

        // Vérifier que le patient est bien le propriétaire
        $patient_id = $this->session->userdata('user_id');
        if ($consultation['patient_id'] != $patient_id) {
            $this->session->set_flashdata('error', 'Vous n\'êtes pas autorisé à payer cette consultation.');
            redirect('Dashboard/patient_dashboard');
            return;
        }

        // Vérifier si la consultation est déjà payée
        if ($consultation['paiement_statut'] == 'paye') {
            $this->session->set_flashdata('warning', 'Cette consultation a déjà été payée.');
            redirect('Consultations/success/' . $consultation['id']);
            return;
        }

        // Récupérer les informations du médecin
        $medecin = null;
        if (isset($consultation['medecin_id']) && $consultation['medecin_id']) {
            $medecin = $this->Model->getDoctorById($consultation['medecin_id']);
        }

        // Récupérer les modes de paiement depuis la base de données
        $mode_payements = $this->Model->getActivePaymentMethods();
        
        // Ajouter les méthodes internationales
        $international_methods = [
            [
                'description' => 'Carte bancaire',
                'etapepaiement' => 'international',
                'numero_compte' => '**** **** **** 1234',
                'nom_compte' => 'NUFOTEC SAS',
                'est_actif' => 1
            ],
            [
                'description' => 'PayPal',
                'etapepaiement' => 'international',
                'numero_compte' => 'payments@nufotec.com',
                'nom_compte' => 'NUFOTEC',
                'est_actif' => 1
            ],
            [
                'description' => 'Virement bancaire',
                'etapepaiement' => 'international',
                'numero_compte' => 'FR76 1234 5678 9012 3456 7890 123',
                'nom_compte' => 'NUFOTEC SAS',
                'est_actif' => 1
            ]
        ];
        
        // Fusionner les méthodes locales et internationales
        $all_payment_methods = array_merge($mode_payements, $international_methods);

        // Récupérer les taux de change
        $taux = $this->config->item('taux_devise');
        if (!$taux) {
            $taux = ['USD_TO_EUR' => 0.92, 'USD_TO_BIF' => 2900];
        }

        // Calculer les prix
        $prix_usd = isset($consultation['prix_ht']) ? (float)$consultation['prix_ht'] : 50;
        $prix_eur = $prix_usd * ($taux['USD_TO_EUR'] ?? 0.92);
        $prix_bif = $prix_usd * ($taux['USD_TO_BIF'] ?? 2900);

        // Décoder les fichiers JSON
        $examens_demandes = !empty($consultation['examens_demandes']) ? json_decode($consultation['examens_demandes'], true) : [];
        $ordonnances = !empty($consultation['ordonnances']) ? json_decode($consultation['ordonnances'], true) : [];

        // Récupérer le nom complet du patient
        $patient_fullname = '';
        if (!empty($consultation['patient_prenom']) || !empty($consultation['patient_nom'])) {
            $patient_fullname = trim($consultation['patient_prenom'] . ' ' . $consultation['patient_nom']);
        } else {
            $patient_fullname = $this->session->userdata('fullname') ?: $this->session->userdata('fu') ?: 'Non défini';
        }

        // Préparer les données pour la vue
        $data = [
            'title'                 => 'Paiement - NUFOTEC',
            'consultation'          => $consultation,
            'consultation_id'       => $consultation['id'],
            'consultation_num'      => $numero_consultation,
            'medecin'               => $medecin,
            'mode_payements'        => $all_payment_methods,
            'taux'                  => $taux,
            'prix_usd'              => $prix_usd,
            'prix_eur'              => $prix_eur,
            'prix_bif'              => $prix_bif,
            'examens_demandes'      => $examens_demandes,
            'ordonnances'           => $ordonnances,
            'patient_name'          => $patient_fullname,
            'patient_email'         => $consultation['patient_email'] ?? $this->session->userdata('email'),
            'patient_age'           => $consultation['age'] ?? 'Non défini',
            'patient_pays'          => $consultation['pays_nom'] ?? 'Non défini',
            'patient_poids'         => $consultation['poids'] ?? '?',
            'patient_taille'        => $consultation['taille'] ?? '?'
        ];

        // Charger la vue
        $this->load->view('Payment_View', $data);
    }

    /**
     * Traitement du paiement (AJAX)
     */
    public function process() {
        // Vérifier si c'est une requête AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // Vérifier si l'utilisateur est connecté
        if (!$this->session->userdata('user_id')) {
            $this->json_response(false, 'Veuillez vous connecter.');
            return;
        }

        // Validation
        $consultation_id = $this->input->post('consultation_id', TRUE);
        $payment_method = $this->input->post('payment_method', TRUE);
        
        if (!$consultation_id || !$payment_method) {
            $this->json_response(false, 'Tous les champs sont obligatoires.');
            return;
        }

        // Vérifier si la consultation existe
        $consultation = $this->Model->getConsultationById($consultation_id);
        if (!$consultation) {
            $this->json_response(false, 'Consultation introuvable.');
            return;
        }

        // Vérifier que le patient est bien le propriétaire
        if ($consultation['patient_id'] != $this->session->userdata('user_id')) {
            $this->json_response(false, 'Vous n\'êtes pas autorisé.');
            return;
        }

        // Vérifier si déjà payé
        if ($consultation['paiement_statut'] == 'paye') {
            $this->json_response(false, 'Cette consultation a déjà été payée.');
            return;
        }

        // Traitement du fichier (preuve de paiement)
        $payment_proof = '';
        if (!empty($_FILES['payment_proof']['name'])) {
            $upload_result = $this->_upload_payment_proof($_FILES['payment_proof']);
            if ($upload_result === false) {
                $this->json_response(false, 'Erreur lors du téléchargement de la preuve de paiement. Format accepté: JPG, PNG, PDF (max 5MB).');
                return;
            }
            $payment_proof = $upload_result;
        }

        // Mettre à jour la consultation
        $update_data = [
            'mode_paiement'   => $payment_method,
            'preuve_paiement' => $payment_proof ?: null,
            'paiement_statut' => 'paye',
            'date_paiement'   => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        $updated = $this->Model->update('consultations', $update_data, ['id' => $consultation_id]);

        if (!$updated) {
            $this->json_response(false, 'Erreur lors de l\'enregistrement du paiement.');
            return;
        }

        // Journaliser l'activité
        $this->Model->create('user_activities', [
            'user_id'     => $this->session->userdata('user_id'),
            'action'      => 'payment',
            'module'      => 'consultations',
            'item_id'     => $consultation_id,
            'description' => 'Paiement effectué pour la consultation N°' . $consultation['numero_consultation'],
            'ip_address'  => $this->input->ip_address(),
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        // Envoyer email de confirmation avec cPanel_email_lib
        $this->_send_payment_confirmation_email($consultation, $payment_method);

        $this->json_response(true, 'Paiement enregistré avec succès !', [
            'redirect' => base_url('Consultations/success/' . $consultation_id)
        ]);
    }

    /**
     * Upload de la preuve de paiement
     * @return string|false Chemin du fichier ou false si erreur
     */
    private function _upload_payment_proof($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($file_type, $allowed_types)) {
            return false;
        }

        $upload_dir = FCPATH . 'attachments/Payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'attachments/Payments/' . $filename;
        }

        return false;
    }

    /**
     * Envoyer l'email de confirmation de paiement avec cPanel_email_lib
     */
    private function _send_payment_confirmation_email($consultation, $payment_method) {
        // Vérifier que la librairie est chargée
        if (!isset($this->cpanel_email_lib) || !is_object($this->cpanel_email_lib)) {
            log_message('error', 'cpanel_email_lib non disponible pour l\'envoi du mail de paiement');
            return;
        }
        
        $patient = $this->Model->getUserById($consultation['patient_id']);
        
        $medecin = null;
        if ($consultation['medecin_id']) {
            $medecin = $this->Model->getDoctorById($consultation['medecin_id']);
        }

        $site_name = $this->Model->get_setting('site_name', 'NUFOTEC');
        $site_logo = $this->Model->get_setting('site_logo');
        $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';

        $subject = 'Confirmation de paiement - Consultation N°' . $consultation['numero_consultation'];
        $patient_name = htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']);
        $doctor_name = htmlspecialchars($medecin['prenom'] ?? '') . ' ' . htmlspecialchars($medecin['nom'] ?? '');
        $consultation_number = htmlspecialchars($consultation['numero_consultation']);
        $amount = number_format($consultation['prix_ht'], 2) . ' ' . htmlspecialchars($consultation['devise']);
        $payment_date = date('d/m/Y H:i');
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmation de paiement - NUFOTEC</title>
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
                .content {
                    padding: 28px;
                }
                .success-icon {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .success-icon span {
                    font-size: 60px;
                }
                .title {
                    font-size: 22px;
                    font-weight: 700;
                    color: #1a2a3a;
                    margin-bottom: 10px;
                    text-align: center;
                }
                .info-box {
                    background: #f7f9fc;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 20px 0;
                    border: 1px solid #e8ecf0;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #eef2f6;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label {
                    font-weight: 600;
                    color: #1a2a3a;
                }
                .info-value {
                    color: #5a6a7a;
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
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    ' . (!empty($logo_url) ? '<img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="header-logo">' : '') . '
                    <h1>✅ Paiement confirmé</h1>
                    <p>' . htmlspecialchars($site_name) . '</p>
                </div>
                <div class="content">
                    <div class="success-icon"><span>✅</span></div>
                    <div class="title">Merci pour votre confiance !</div>
                    
                    <div class="info-box">
                        <div class="info-row">
                            <span class="info-label">Numéro de consultation</span>
                            <span class="info-value">' . $consultation_number . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Médecin</span>
                            <span class="info-value">Dr. ' . $doctor_name . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Montant payé</span>
                            <span class="info-value">' . $amount . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Mode de paiement</span>
                            <span class="info-value">' . htmlspecialchars($payment_method) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date</span>
                            <span class="info-value">' . $payment_date . '</span>
                        </div>
                    </div>
                    
                    <p style="margin: 15px 0; text-align: center;">
                        Votre consultation est maintenant confirmée. Vous recevrez un email du médecin pour planifier le rendez-vous.
                    </p>
                    
                    <div style="text-align: center;">
                        <a href="' . base_url('Consultations/details/' . $consultation['id']) . '" class="btn">Voir les détails</a>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Tous droits réservés</div>
                    <div class="footer-text"><a href="' . base_url() . '" style="color:#9aaab9;">Visitez notre site</a></div>
                </div>
            </div>
        </body>
        </html>';
        
        $result = $this->cpanel_email_lib->send_email($patient['email'], $subject, $message);
        
        if ($result['success']) {
            log_message('info', 'Email de confirmation de paiement envoyé à: ' . $patient['email']);
        } else {
            log_message('error', 'Échec envoi email paiement à ' . $patient['email'] . ': ' . ($result['message'] ?? 'Erreur inconnue'));
        }
    }

    /**
     * Page de succès après paiement
     * @param int $consultation_id ID de la consultation
     */
    public function success($consultation_id = null) {
        if (!$consultation_id) {
            redirect('Dashboard/patient_dashboard');
            return;
        }

        $consultation = $this->Model->getConsultationById($consultation_id);
        
        if (!$consultation) {
            show_404();
            return;
        }

        // Vérifier que le patient est le propriétaire
        if ($consultation['patient_id'] != $this->session->userdata('user_id')) {
            show_404();
            return;
        }

        $data = [
            'title' => 'Paiement réussi - NUFOTEC',
            'consultation' => $consultation,
            'consultation_num' => $consultation['numero_consultation']
        ];

        $this->load->view('Payment_Success_View', $data);
    }

    /**
     * Réponse JSON
     */
    private function json_response($success, $message, $data = []) {
        $response = array_merge(['success' => $success, 'message' => $message], $data);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Télécharger la preuve de paiement
     * @param int $consultation_id ID de la consultation
     */
    public function download_proof($consultation_id = null) {
        if (!$consultation_id) {
            show_404();
            return;
        }

        $consultation = $this->Model->getConsultationById($consultation_id);
        
        if (!$consultation) {
            show_404();
            return;
        }

        // Vérifier que le patient est le propriétaire
        if ($consultation['patient_id'] != $this->session->userdata('user_id')) {
            show_404();
            return;
        }

        if (empty($consultation['preuve_paiement'])) {
            show_404();
            return;
        }

        $file_path = FCPATH . $consultation['preuve_paiement'];
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . basename($consultation['preuve_paiement']) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}