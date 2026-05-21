<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_whapi extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('WhatsApp_Whapi');
        $this->load->model(['Group_model', 'Participant_model', 'Sync_model']);
    }
    
    // ============================================
    // URL À APPELER DANS LE NAVIGATEUR :
    // https://votre-domaine.com/sync_whapi/sync_all_groups_and_participants
    // ============================================
    
    public function sync_all_groups_and_participants() {
        $start_time = microtime(true);
        $triggered_by = $this->input->get('trigger') ?? 'manual';
        
        // Sécurité : token optionnel pour éviter les appels malveillants
        $token = $this->input->get('token');
        $expected_token = $this->config->item('whapi')['webhook_secret'] ?? '';
        if ($expected_token && $token !== $expected_token) {
            echo "❌ Token invalide. Ajoutez ?token=VOTRE_TOKEN_SECRET à l'URL\n";
            return;
        }
        
        echo "<pre>";
        echo "========================================\n";
        echo "SYNCHRONISATION WHATSAPP BOT\n";
        echo "Début: " . date('Y-m-d H:i:s') . "\n";
        echo "========================================\n\n";
        
        // ÉTAPE 1 : Synchronisation des groupes
        echo "📁 ÉTAPE 1 : Récupération des groupes...\n";
        $groups_result = $this->sync_groups();
        
        // ÉTAPE 2 : Synchronisation des participants pour chaque groupe
        echo "\n👥 ÉTAPE 2 : Récupération des participants...\n";
        $participants_result = $this->sync_all_participants();
        
        // ÉTAPE 3 : Log de la synchronisation
        $duration_ms = round((microtime(true) - $start_time) * 1000);
        $status = ($groups_result['status'] == 'success' && $participants_result['status'] == 'success') ? 'success' : 'partial';
        
        $this->Sync_model->log_sync(
            'full',
            $status,
            $groups_result['found'],
            $groups_result['synced'],
            $participants_result['found'],
            $participants_result['synced'],
            $groups_result['error'] ?? $participants_result['error'] ?? null,
            $triggered_by
        );
        
        echo "\n========================================\n";
        echo "✅ SYNCHRONISATION TERMINÉE\n";
        echo "Durée: " . $duration_ms . " ms\n";
        echo "Statut: " . strtoupper($status) . "\n";
        echo "========================================\n";
        echo "</pre>";
    }
    
    // ============================================
    // SYNCHRONISER UNIQUEMENT LES GROUPES
    // URL: /sync_whapi/sync_groups
    // ============================================
    
    public function sync_groups() {
        echo "<pre>";
        echo "🔄 Synchronisation des groupes...\n";
        
        try {
            $groups = $this->whatsapp_whapi->get_groups();
            
            if (empty($groups)) {
                echo "⚠️ Aucun groupe trouvé. Vérifiez que WhatsApp est bien connecté.\n";
                return ['status' => 'failed', 'found' => 0, 'synced' => 0, 'error' => 'No groups found'];
            }
            
            $synced_count = 0;
            foreach ($groups as $group) {
                $group_id = $group['id'] ?? null;
                $group_name = $group['name'] ?? 'Sans nom';
                $participant_count = $group['participantsCount'] ?? 0;
                
                if ($group_id) {
                    $this->Group_model->upsert_group($group_id, $group_name, null, $participant_count);
                    $this->Group_model->update_last_sync($group_id);
                    $synced_count++;
                    echo "   ✅ Groupe: " . substr($group_name, 0, 40) . " - ID: " . $group_id . " (" . $participant_count . " membres)\n";
                }
            }
            
            echo "\n📊 Synchro terminée: " . $synced_count . "/" . count($groups) . " groupes synchronisés\n";
            
            return ['status' => 'success', 'found' => count($groups), 'synced' => $synced_count];
            
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            return ['status' => 'failed', 'found' => 0, 'synced' => 0, 'error' => $e->getMessage()];
        }
    }
    
    // ============================================
    // SYNCHRONISER LES PARTICIPANTS D'UN GROUPE SPÉCIFIQUE
    // URL: /sync_whapi/sync_participants/ID_DU_GROUPE
    // ============================================
    
    public function sync_participants($groupe_id = null) {
        echo "<pre>";
        
        if (!$groupe_id) {
            echo "❌ ID du groupe requis. Exemple: /sync_whapi/sync_participants/123456789@g.us\n";
            return;
        }
        
        echo "🔄 Synchronisation des participants du groupe: " . $groupe_id . "\n";
        
        try {
            $participants = $this->whatsapp_whapi->get_group_participants($groupe_id);
            
            if (empty($participants)) {
                echo "⚠️ Aucun participant trouvé.\n";
                return ['status' => 'failed', 'found' => 0, 'synced' => 0];
            }
            
            // Supprimer les anciens participants de ce groupe (optionnel)
            // $this->Participant_model->delete_participants_by_group($groupe_id);
            
            $synced_count = 0;
            foreach ($participants as $participant) {
                $phone = $participant['id'] ?? null;
                $name = $participant['name'] ?? $participant['pushName'] ?? 'Inconnu';
                
                if ($phone) {
                    $phone = $this->clean_phone_number($phone);
                    $this->Participant_model->upsert_participant_with_sync($groupe_id, $phone, $name);
                    $synced_count++;
                    echo "   ✅ Participant: " . $name . " - " . $phone . "\n";
                }
            }
            
            // Mettre à jour le compteur dans le groupe
            $this->Group_model->update_participant_count($groupe_id, $synced_count);
            
            echo "\n📊 Synchro terminée: " . $synced_count . "/" . count($participants) . " participants synchronisés\n";
            
            return ['status' => 'success', 'found' => count($participants), 'synced' => $synced_count];
            
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            return ['status' => 'failed', 'found' => 0, 'synced' => 0, 'error' => $e->getMessage()];
        }
    }
    
    // ============================================
    // SYNCHRONISER TOUS LES PARTICIPANTS DE TOUS LES GROUPES
    // URL: /sync_whapi/sync_all_participants
    // ============================================
    
    public function sync_all_participants() {
        echo "🔄 Synchronisation de tous les participants...\n";
        
        try {
            $groups = $this->Group_model->get_all_groups();
            
            if (empty($groups)) {
                echo "⚠️ Aucun groupe trouvé. Synchronisez d'abord les groupes.\n";
                return ['status' => 'failed', 'found' => 0, 'synced' => 0];
            }
            
            $total_participants_found = 0;
            $total_participants_synced = 0;
            
            foreach ($groups as $group) {
                echo "\n📁 Groupe: " . ($group->nom ?? 'Sans nom') . "\n";
                
                $participants = $this->whatsapp_whapi->get_group_participants($group->groupe_id);
                
                if (!empty($participants)) {
                    $total_participants_found += count($participants);
                    
                    foreach ($participants as $participant) {
                        $phone = $participant['id'] ?? null;
                        $name = $participant['name'] ?? $participant['pushName'] ?? 'Inconnu';
                        
                        if ($phone) {
                            $phone = $this->clean_phone_number($phone);
                            $this->Participant_model->upsert_participant_with_sync($group->groupe_id, $phone, $name);
                            $total_participants_synced++;
                        }
                    }
                    
                    // Mettre à jour le compteur
                    $this->Group_model->update_participant_count($group->groupe_id, count($participants));
                    echo "   ✅ " . count($participants) . " participants synchronisés\n";
                } else {
                    echo "   ⚠️ Aucun participant trouvé\n";
                }
                
                sleep(1); // Pause pour éviter le rate limiting
            }
            
            echo "\n📊 TOTAL: " . $total_participants_synced . "/" . $total_participants_found . " participants synchronisés\n";
            
            return ['status' => 'success', 'found' => $total_participants_found, 'synced' => $total_participants_synced];
            
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            return ['status' => 'failed', 'found' => 0, 'synced' => 0, 'error' => $e->getMessage()];
        }
    }
    
    // ============================================
    // VOIR L'HISTORIQUE DES SYNCHRONISATIONS
    // URL: /sync_whapi/history
    // ============================================
    
    public function history() {
        echo "<pre>";
        echo "========================================\n";
        echo "📜 HISTORIQUE DES SYNCHRONISATIONS\n";
        echo "========================================\n\n";
        
        $history = $this->Sync_model->get_sync_history(50);
        
        if (empty($history)) {
            echo "Aucune synchronisation enregistrée.\n";
            return;
        }
        
        foreach ($history as $log) {
            $status_icon = ($log->status == 'success') ? '✅' : (($log->status == 'partial') ? '⚠️' : '❌');
            echo $status_icon . " " . $log->created_at . " - " . strtoupper($log->sync_type);
            echo " | Groupes: " . $log->groups_synced . "/" . $log->groups_found;
            echo " | Participants: " . $log->participants_synced . "/" . $log->participants_found;
            echo " | Déclenché par: " . $log->triggered_by . "\n";
            if ($log->error_message) {
                echo "   Erreur: " . $log->error_message . "\n";
            }
        }
        
        echo "</pre>";
    }
    
    // ============================================
    // NETTOYER LES PARTICIPANTS ORPHELINS
    // URL: /sync_whapi/cleanup_orphans
    // ============================================
    
    public function cleanup_orphans() {
        echo "<pre>";
        echo "🧹 Nettoyage des participants orphelins...\n";
        
        // Supprimer les participants dont le groupe n'existe plus
        $sql = "DELETE wp FROM whatsapp_participants wp 
                LEFT JOIN groupes_whatsapp g ON wp.groupe_id = g.groupe_id 
                WHERE g.groupe_id IS NULL";
        
        $this->db->query($sql);
        $deleted = $this->db->affected_rows();
        
        echo "✅ " . $deleted . " participants orphelins supprimés.\n";
        echo "</pre>";
    }
    
    // ============================================
    // UTILITAIRES
    // ============================================
    
    private function clean_phone_number($phone) {
        // Enlève le suffixe @s.whatsapp.net si présent
        $phone = preg_replace('/@s\.whatsapp\.net$/', '', $phone);
        // Garde uniquement les chiffres et le +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return $phone;
    }
}