<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('form_validation');
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

        // Charger la vue avec header et footer
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
            $payment_proof = $this->_upload_payment_proof($_FILES['payment_proof']);
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
            if ($payment_proof && file_exists($payment_proof)) {
                unlink($payment_proof);
            }
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

        // Envoyer email de confirmation
        $this->_send_payment_confirmation_email($consultation, $payment_method);

        $this->json_response(true, 'Paiement enregistré avec succès !', [
            'redirect' => base_url('Consultations/success/' . $consultation_id)
        ]);
    }

    /**
     * Upload de la preuve de paiement
     */
    private function _upload_payment_proof($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            return false;
        }

        $upload_dir = FCPATH . 'uploads/payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/payments/' . $filename;
        }

        return false;
    }

    /**
     * Envoyer l'email de confirmation de paiement
     */
    private function _send_payment_confirmation_email($consultation, $payment_method) {
        $patient = $this->Model->getUserById($consultation['patient_id']);
        
        $medecin = null;
        if ($consultation['medecin_id']) {
            $medecin = $this->Model->getDoctorById($consultation['medecin_id']);
        }

        $subject = 'Confirmation de paiement - Consultation N°' . $consultation['numero_consultation'];

        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0f4c3a; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                    .button { display: inline-block; padding: 10px 20px; background: #0f4c3a; color: white; text-decoration: none; border-radius: 5px; }
                    .info { margin: 15px 0; padding: 10px; background: white; border-left: 4px solid #0f4c3a; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Confirmation de paiement</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour <strong>" . htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) . "</strong>,</p>
                        <p>Nous vous confirmons que votre paiement a bien été reçu pour la consultation suivante :</p>
                        
                        <div class='info'>
                            <p><strong>Numéro de consultation :</strong> " . htmlspecialchars($consultation['numero_consultation']) . "</p>
                            <p><strong>Médecin :</strong> Dr. " . htmlspecialchars($medecin['prenom'] ?? '') . ' ' . htmlspecialchars($medecin['nom'] ?? '') . "</p>
                            <p><strong>Montant payé :</strong> " . number_format($consultation['prix_ht'], 2) . " " . htmlspecialchars($consultation['devise']) . "</p>
                            <p><strong>Mode de paiement :</strong> " . htmlspecialchars($payment_method) . "</p>
                            <p><strong>Date :</strong> " . date('d/m/Y H:i') . "</p>
                        </div>
                        
                        <p>Votre consultation est maintenant confirmée. Vous recevrez un email de la part du médecin pour planifier le rendez-vous.</p>
                        
                        <p style='text-align: center;'>
                            <a href='" . base_url('Consultations/details/' . $consultation['id']) . "' class='button'>Voir les détails</a>
                        </p>
                        
                        <p>Cordialement,<br>L'équipe NUFOTEC</p>
                    </div>
                    <div class='footer'>
                        <p>Cet email est un message automatique, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " NUFOTEC - Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $this->load->library('email');
        $this->email->from('noreply@nufotec.com', 'NUFOTEC');
        $this->email->to($patient['email']);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype('html');
        $this->email->send();
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
}