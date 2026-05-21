<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_whapi extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('WhatsApp_Whapi');
        $this->load->model(['Group_model', 'Participant_model']);
    }
    
    // URL: /sync_whapi/sync_all_groups_and_participants
    public function sync_all_groups_and_participants() {
        echo "<pre>";
        echo "SYNCHRONISATION COMPLETE\n";
        echo "=======================\n\n";
        
        // Synchro groupes
        echo "1. Synchronisation des groupes...\n";
        $groups = $this->whatsapp_whapi->get_groups();
        
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $group_id = $group['id'] ?? null;
                $group_name = $group['name'] ?? 'Sans nom';
                if ($group_id) {
                    $this->Group_model->upsert_group($group_id, $group_name);
                    echo "   ✅ Groupe: " . $group_name . "\n";
                }
            }
            echo "   Total: " . count($groups) . " groupes synchronisés\n";
        } else {
            echo "   ⚠️ Aucun groupe trouvé\n";
        }
        
        // Synchro participants pour chaque groupe
        echo "\n2. Synchronisation des participants...\n";
        $all_groups = $this->Group_model->get_all_groups();
        $total_participants = 0;
        
        foreach ($all_groups as $group) {
            echo "   Groupe: " . ($group->nom ?? 'Sans nom') . "\n";
            $participants = $this->whatsapp_whapi->get_group_participants($group->groupe_id);
            
            if (!empty($participants)) {
                foreach ($participants as $participant) {
                    $phone = $participant['id'] ?? null;
                    $name = $participant['name'] ?? $participant['pushName'] ?? 'Inconnu';
                    if ($phone) {
                        $phone = preg_replace('/@s\.whatsapp\.net$/', '', $phone);
                        $this->Participant_model->upsert_participant($group->groupe_id, $phone, $name);
                        $total_participants++;
                    }
                }
                echo "      ✅ " . count($participants) . " participants\n";
            } else {
                echo "      ⚠️ Aucun participant\n";
            }
        }
        
        echo "\n3. RÉSUMÉ:\n";
        echo "   - Groupes: " . count($groups) . "\n";
        echo "   - Participants: " . $total_participants . "\n";
        echo "\n✅ SYNCHRONISATION TERMINEE\n";
        echo "</pre>";
    }
    
    // URL: /sync_whapi/sync_groups
    public function sync_groups() {
        echo "<pre>";
        echo "SYNCHRONISATION DES GROUPES\n";
        echo "==========================\n\n";
        
        $groups = $this->whatsapp_whapi->get_groups();
        
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $group_id = $group['id'] ?? null;
                $group_name = $group['name'] ?? 'Sans nom';
                if ($group_id) {
                    $this->Group_model->upsert_group($group_id, $group_name);
                    echo "✅ " . $group_name . " (" . $group_id . ")\n";
                }
            }
            echo "\nTotal: " . count($groups) . " groupes synchronisés\n";
        } else {
            echo "⚠️ Aucun groupe trouvé. Vérifiez que WhatsApp est connecté.\n";
        }
        
        echo "</pre>";
    }
}