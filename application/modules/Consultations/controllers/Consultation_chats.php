<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consultation_chats extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }

    // ============================================
    // PAGE PRINCIPALE DU CHAT
    // ============================================

    public function index()
    {
        $user_id = $this->session->userdata('id');
        $user_type = $this->session->userdata('type_utilisateur');
        
        // Si c'est un médecin, récupérer son ID dans la table medecins
        if ($user_type === 'medecin') {
            $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user_id]);
            if (!empty($medecin)) {
                $medecin_id = $medecin[0]['id'];
                $data['conversations'] = $this->getPatientsForMedecin($medecin_id, $user_id);
            } else {
                $data['conversations'] = [];
            }
        } 
        elseif ($user_type === 'patient') {
            $data['conversations'] = $this->getMedecinsForPatient($user_id);
        }
        else {
            $data['conversations'] = $this->getAllConversations($user_id);
        }
        
        $this->load->view('Consultation_chats_View', $data);
    }

    /**
     * Récupère les patients d'un médecin
     * @param int $medecin_id - ID dans la table medecins
     * @param int $user_id - ID dans la table users (pour les messages)
     */
    private function getPatientsForMedecin($medecin_id, $user_id)
{
    // Version simplifiée sans sous-requêtes complexes
    return $this->Model->query("
        SELECT DISTINCT
            u.id as user_id,
            u.nom, 
            u.prenom, 
            u.photo, 
            u.email,
            'patient' as type_utilisateur,
            c.id as consultation_id,
            c.numero_consultation,
            c.statut as consultation_statut,
            c.date_souhaitee as prochaine_consultation,
            0 as unread_count,
            NULL as last_message,
            NULL as last_message_time
        FROM consultations c
        INNER JOIN users u ON c.patient_id = u.id
        WHERE c.medecin_id = ?
        ORDER BY c.date_souhaitee DESC
    ", [$medecin_id]);
}
    /**
     * Récupère les médecins d'un patient
     */
    private function getMedecinsForPatient($patient_id)
    {
        return $this->Model->query("
            SELECT DISTINCT
                u.id as user_id,
                u.nom, 
                u.prenom, 
                u.photo, 
                u.email,
                u.type_utilisateur,
                c.id as consultation_id,
                c.numero_consultation,
                c.statut as consultation_statut,
                (SELECT COUNT(*) FROM consultation_chats cc 
                 WHERE cc.receiver_id = ? 
                 AND cc.sender_id = u.id 
                 AND cc.is_read = 0) as unread_count,
                (SELECT cc.message FROM consultation_chats cc 
                 WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                    OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                 ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                (SELECT cc.created_at FROM consultation_chats cc 
                 WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                    OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                 ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
            FROM users u
            INNER JOIN medecins m ON u.id = m.user_id
            INNER JOIN consultations c ON m.id = c.medecin_id
            WHERE c.patient_id = ?
            AND u.type_utilisateur = 'medecin'
            ORDER BY 
                CASE 
                    WHEN c.statut = 'en_cours' THEN 1
                    WHEN c.statut = 'confirmee' THEN 2
                    WHEN c.statut = 'en_attente' THEN 3
                    ELSE 4
                END,
                c.date_souhaitee DESC
        ", [$patient_id, $patient_id, $patient_id, $patient_id, $patient_id, $patient_id]);
    }

    /**
     * Récupère toutes les conversations (pour admin)
     */
    private function getAllConversations($user_id)
    {
        return $this->Model->query("
            SELECT 
                u.id as user_id,
                u.nom, 
                u.prenom, 
                u.photo, 
                u.email,
                u.type_utilisateur,
                (SELECT COUNT(*) FROM consultation_chats cc 
                 WHERE cc.receiver_id = ? 
                 AND cc.sender_id = u.id 
                 AND cc.is_read = 0) as unread_count,
                (SELECT cc.message FROM consultation_chats cc 
                 WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                    OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                 ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                (SELECT cc.created_at FROM consultation_chats cc 
                 WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                    OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                 ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
            FROM users u
            WHERE u.id != ?
            AND EXISTS (
                SELECT 1 FROM consultation_chats cc 
                WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                   OR (cc.sender_id = u.id AND cc.receiver_id = ?)
            )
            ORDER BY last_message_time DESC
        ", [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    }

    // ============================================
    // GESTION DES MESSAGES
    // ============================================

    public function getMessages($receiver_id)
    {
        $sender_id = $this->session->userdata('id');
        
        if (!$this->canCommunicate($sender_id, $receiver_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }
        
        // Marquer comme lus
        $this->Model->update('consultation_chats', 
            ['receiver_id' => $sender_id, 'sender_id' => $receiver_id, 'is_read' => 0], 
            ['is_read' => 1]
        );
        
        $messages = $this->Model->query("
            SELECT cc.*, 
                   u.nom, u.prenom, u.photo,
                   CASE WHEN cc.sender_id = ? THEN 'me' ELSE 'them' END as type
            FROM consultation_chats cc
            LEFT JOIN users u ON cc.sender_id = u.id
            WHERE (cc.sender_id = ? AND cc.receiver_id = ?) 
               OR (cc.sender_id = ? AND cc.receiver_id = ?)
            ORDER BY cc.created_at ASC
        ", [$sender_id, $sender_id, $receiver_id, $receiver_id, $sender_id]);
        
        header('Content-Type: application/json');
        echo json_encode($messages);
        exit;
    }

    /**
     * Vérifie si deux utilisateurs peuvent communiquer
     */
    private function canCommunicate($user1_id, $user2_id)
    {
        $user1 = $this->Model->query("SELECT type_utilisateur FROM users WHERE id = ?", [$user1_id]);
        $user2 = $this->Model->query("SELECT type_utilisateur FROM users WHERE id = ?", [$user2_id]);
        
        $user1_type = $user1[0]['type_utilisateur'] ?? null;
        $user2_type = $user2[0]['type_utilisateur'] ?? null;
        
        // Si l'un est médecin et l'autre patient
        if (($user1_type === 'medecin' && $user2_type === 'patient') ||
            ($user1_type === 'patient' && $user2_type === 'medecin')) {
            
            // Récupérer le medecin_id si user1 est médecin
            if ($user1_type === 'medecin') {
                $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user1_id]);
                $patient_id = $user2_id;
            } else {
                $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user2_id]);
                $patient_id = $user1_id;
            }
            
            if (empty($medecin)) return false;
            
            $medecin_id = $medecin[0]['id'];
            
            $consultation = $this->Model->query("
                SELECT id FROM consultations 
                WHERE medecin_id = ? AND patient_id = ?
                LIMIT 1
            ", [$medecin_id, $patient_id]);
            
            return !empty($consultation);
        }
        
        return true;
    }

    public function sendMessage()
    {
        $receiver_id = $this->input->post('receiver_id');
        $message = $this->input->post('message');
        $sender_id = $this->session->userdata('id');
        
        if (empty($message) || empty($receiver_id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Message ou destinataire vide']);
            exit;
        }

        if (!$this->canCommunicate($sender_id, $receiver_id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            exit;
        }

        $data = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->Model->create('consultation_chats', $data);
        
        if ($id) {
            $message = $this->Model->query("
                SELECT cc.*, u.nom, u.prenom, u.photo, 'me' as type
                FROM consultation_chats cc
                LEFT JOIN users u ON cc.sender_id = u.id
                WHERE cc.id = ?
            ", [$id]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message[0] ?? null]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Erreur d\'envoi']);
        }
        exit;
    }

    public function markAsRead($sender_id)
    {
        $receiver_id = $this->session->userdata('id');
        
        $this->Model->update('consultation_chats', 
            ['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'is_read' => 0], 
            ['is_read' => 1]
        );
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // ============================================
    // GESTION DES FICHIERS
    // ============================================

    public function uploadFile()
    {
        $receiver_id = $this->input->post('receiver_id');
        $sender_id = $this->session->userdata('id');
        
        if (empty($_FILES['file']['name']) || empty($receiver_id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Fichier ou destinataire manquant']);
            exit;
        }

        if (!$this->canCommunicate($sender_id, $receiver_id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            exit;
        }

        $config['upload_path'] = FCPATH . 'attachments/chats/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = TRUE;
        
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, TRUE);
        }
        
        $this->load->library('upload', $config);
        
        if (!$this->upload->do_upload('file')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $this->upload->display_errors()]);
            exit;
        }
        
        $upload_data = $this->upload->data();
        $file_url = base_url('attachments/chats/' . $upload_data['file_name']);
        $message = '[Fichier] ' . $upload_data['orig_name'] . ' : ' . $file_url;
        
        $data = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->Model->create('consultation_chats', $data);
        
        if ($id) {
            $message = $this->Model->query("
                SELECT cc.*, u.nom, u.prenom, u.photo, 'me' as type
                FROM consultation_chats cc
                LEFT JOIN users u ON cc.sender_id = u.id
                WHERE cc.id = ?
            ", [$id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => $message[0] ?? null,
                'file' => [
                    'name' => $upload_data['orig_name'],
                    'url' => $file_url,
                    'type' => $upload_data['file_type']
                ]
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Erreur d\'enregistrement']);
        }
        exit;
    }

    // ============================================
    // INFORMATIONS UTILISATEUR
    // ============================================

    public function getUserInfo($user_id)
    {
        $current_user_id = $this->session->userdata('id');
        
        if (!$this->canCommunicate($current_user_id, $user_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }
        
        $info = $this->Model->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM consultation_chats 
                    WHERE (sender_id = ? AND receiver_id = ?) 
                       OR (sender_id = ? AND receiver_id = ?)) as total_messages
            FROM users u
            WHERE u.id = ?
        ", [$current_user_id, $user_id, $user_id, $current_user_id, $user_id]);
        
        header('Content-Type: application/json');
        echo json_encode($info[0] ?? null);
        exit;
    }

    public function searchUsers()
    {
        $search = $this->input->get('q');
        $user_id = $this->session->userdata('id');
        $user_type = $this->session->userdata('type_utilisateur');
        
        if (empty($search) || strlen($search) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        
        // Pour un médecin, chercher parmi ses patients
        if ($user_type === 'medecin') {
            $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user_id]);
            if (empty($medecin)) {
                echo json_encode([]);
                exit;
            }
            $medecin_id = $medecin[0]['id'];
            
            $users = $this->Model->query("
                SELECT DISTINCT u.id, u.nom, u.prenom, u.photo, u.email, u.type_utilisateur,
                   (SELECT COUNT(*) FROM consultation_chats cc 
                    WHERE cc.receiver_id = ? AND cc.sender_id = u.id AND cc.is_read = 0) as unread_count
                FROM users u
                INNER JOIN consultations c ON u.id = c.patient_id
                WHERE c.medecin_id = ?
                AND u.type_utilisateur = 'patient'
                AND (u.nom LIKE ? OR u.prenom LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
                LIMIT 20
            ", [$user_id, $medecin_id, "%$search%", "%$search%", "%$search%"]);
        }
        // Pour un patient, chercher parmi ses médecins
        elseif ($user_type === 'patient') {
            $users = $this->Model->query("
                SELECT DISTINCT u.id, u.nom, u.prenom, u.photo, u.email, u.type_utilisateur,
                   (SELECT COUNT(*) FROM consultation_chats cc 
                    WHERE cc.receiver_id = ? AND cc.sender_id = u.id AND cc.is_read = 0) as unread_count
                FROM users u
                INNER JOIN medecins m ON u.id = m.user_id
                INNER JOIN consultations c ON m.id = c.medecin_id
                WHERE c.patient_id = ?
                AND u.type_utilisateur = 'medecin'
                AND (u.nom LIKE ? OR u.prenom LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
                LIMIT 20
            ", [$user_id, $user_id, "%$search%", "%$search%", "%$search%"]);
        }
        else {
            $users = $this->Model->query("
                SELECT u.id, u.nom, u.prenom, u.photo, u.email, u.type_utilisateur,
                   (SELECT COUNT(*) FROM consultation_chats cc 
                    WHERE cc.receiver_id = ? AND cc.sender_id = u.id AND cc.is_read = 0) as unread_count
                FROM users u
                WHERE u.id != ?
                AND (u.nom LIKE ? OR u.prenom LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
                LIMIT 20
            ", [$user_id, $user_id, "%$search%", "%$search%", "%$search%"]);
        }
        
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    // ============================================
    // MÉTHODES POUR LE WIDGET DU DASHBOARD
    // ============================================

    public function getUnreadCount()
    {
        $user_id = $this->session->userdata('id');
        
        $count = $this->Model->query("
            SELECT COUNT(*) as total
            FROM consultation_chats cc
            WHERE cc.receiver_id = ? 
            AND cc.is_read = 0
        ", [$user_id]);
        
        header('Content-Type: application/json');
        echo json_encode(['unread' => intval($count[0]['total'] ?? 0)]);
        exit;
    }

    public function getUnreadConversations()
    {
        $user_id = $this->session->userdata('id');
        $user_type = $this->session->userdata('type_utilisateur');
        
        if ($user_type === 'medecin') {
            $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user_id]);
            if (empty($medecin)) {
                echo json_encode([]);
                exit;
            }
            $medecin_id = $medecin[0]['id'];
            
            $conversations = $this->Model->query("
                SELECT 
                    u.id as user_id,
                    u.nom, 
                    u.prenom, 
                    u.photo,
                    u.type_utilisateur,
                    (SELECT COUNT(*) FROM consultation_chats cc 
                     WHERE cc.receiver_id = ? 
                     AND cc.sender_id = u.id 
                     AND cc.is_read = 0) as unread_count,
                    (SELECT cc.message FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                    (SELECT cc.created_at FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
                FROM users u
                INNER JOIN consultations c ON u.id = c.patient_id
                WHERE c.medecin_id = ?
                AND EXISTS (
                    SELECT 1 FROM consultation_chats cc 
                    WHERE cc.receiver_id = ? 
                    AND cc.sender_id = u.id 
                    AND cc.is_read = 0
                )
                ORDER BY last_message_time DESC
                LIMIT 10
            ", [$user_id, $user_id, $user_id, $user_id, $user_id, $medecin_id, $user_id]);
        } else {
            $conversations = $this->Model->query("
                SELECT 
                    u.id as user_id,
                    u.nom, 
                    u.prenom, 
                    u.photo,
                    u.type_utilisateur,
                    (SELECT COUNT(*) FROM consultation_chats cc 
                     WHERE cc.receiver_id = ? 
                     AND cc.sender_id = u.id 
                     AND cc.is_read = 0) as unread_count,
                    (SELECT cc.message FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                    (SELECT cc.created_at FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
                FROM users u
                WHERE u.id != ?
                AND EXISTS (
                    SELECT 1 FROM consultation_chats cc 
                    WHERE cc.receiver_id = ? 
                    AND cc.sender_id = u.id 
                    AND cc.is_read = 0
                )
                ORDER BY last_message_time DESC
                LIMIT 10
            ", [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
        }
        
        $result = [];
        foreach ($conversations as $conv) {
            $result[] = [
                'id' => $conv['user_id'],
                'user_id' => $conv['user_id'],
                'nom' => $conv['nom'],
                'prenom' => $conv['prenom'],
                'photo' => $conv['photo'],
                'type_utilisateur' => $conv['type_utilisateur'],
                'unread_count' => intval($conv['unread_count']),
                'last_message' => $conv['last_message'],
                'last_message_time' => $conv['last_message_time']
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function getRecentConversations($limit = 10)
    {
        $user_id = $this->session->userdata('id');
        $user_type = $this->session->userdata('type_utilisateur');
        
        if ($user_type === 'medecin') {
            $medecin = $this->Model->query("SELECT id FROM medecins WHERE user_id = ?", [$user_id]);
            if (empty($medecin)) {
                echo json_encode([]);
                exit;
            }
            $medecin_id = $medecin[0]['id'];
            
            $conversations = $this->Model->query("
                SELECT DISTINCT
                    u.id as user_id,
                    u.nom, 
                    u.prenom, 
                    u.photo,
                    u.type_utilisateur,
                    u.email,
                    (SELECT COUNT(*) FROM consultation_chats cc 
                     WHERE cc.receiver_id = ? 
                     AND cc.sender_id = u.id 
                     AND cc.is_read = 0) as unread_count,
                    (SELECT cc.message FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                    (SELECT cc.created_at FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
                FROM users u
                INNER JOIN consultations c ON u.id = c.patient_id
                WHERE c.medecin_id = ?
                ORDER BY last_message_time DESC
                LIMIT ?
            ", [$user_id, $user_id, $user_id, $user_id, $user_id, $medecin_id, intval($limit)]);
        } elseif ($user_type === 'patient') {
            $conversations = $this->Model->query("
                SELECT DISTINCT
                    u.id as user_id,
                    u.nom, 
                    u.prenom, 
                    u.photo,
                    u.type_utilisateur,
                    u.email,
                    (SELECT COUNT(*) FROM consultation_chats cc 
                     WHERE cc.receiver_id = ? 
                     AND cc.sender_id = u.id 
                     AND cc.is_read = 0) as unread_count,
                    (SELECT cc.message FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                    (SELECT cc.created_at FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
                FROM users u
                INNER JOIN medecins m ON u.id = m.user_id
                INNER JOIN consultations c ON m.id = c.medecin_id
                WHERE c.patient_id = ?
                ORDER BY last_message_time DESC
                LIMIT ?
            ", [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, intval($limit)]);
        } else {
            $conversations = $this->Model->query("
                SELECT 
                    u.id as user_id,
                    u.nom, 
                    u.prenom, 
                    u.photo,
                    u.type_utilisateur,
                    u.email,
                    (SELECT COUNT(*) FROM consultation_chats cc 
                     WHERE cc.receiver_id = ? 
                     AND cc.sender_id = u.id 
                     AND cc.is_read = 0) as unread_count,
                    (SELECT cc.message FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message,
                    (SELECT cc.created_at FROM consultation_chats cc 
                     WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                        OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                     ORDER BY cc.created_at DESC LIMIT 1) as last_message_time
                FROM users u
                WHERE u.id != ?
                AND EXISTS (
                    SELECT 1 FROM consultation_chats cc 
                    WHERE (cc.sender_id = ? AND cc.receiver_id = u.id) 
                       OR (cc.sender_id = u.id AND cc.receiver_id = ?)
                )
                ORDER BY last_message_time DESC
                LIMIT ?
            ", [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, intval($limit)]);
        }
        
        header('Content-Type: application/json');
        echo json_encode($conversations);
        exit;
    }
}