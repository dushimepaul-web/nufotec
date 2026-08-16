<?php
defined('BASEPATH') OR exit('Accès direct interdit');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 27/02/2026
 */

class Courtiers extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
        $this->load->model('Modele');
        
        // Configuration CORS pour toutes les réponses
        header('Accès-Control-Autoriser-Origine: *');
        header('Accès-Control-Autoriser-Méthodes: POST, GET, OPTIONS');
        header('Accès-Control-Autoriser-En-têtes: Type-Contenu, X-Demandé-Avec');
        header('Type-Contenu: application/json');
        
        // Gérer les requêtes OPTIONS (vérification préalable)
        if ($_SERVER['REQUEST_METHODE'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Méthode appelée par la route /{lang}/Courtiers-formulaire
     * Vérifie si c'est une requête AJAX POST ou une requête normale
     */
    public function index()
    {
        // Si c'est une requête POST (soumission du formulaire)
        if ($_SERVER['REQUEST_METHODE'] === 'POST') {
            $this->enregistrer();
            return;
        }
        
        // Sinon, afficher le formulaire (requête GET)
        $this->afficher_formulaire();
    }

    /**
     * Affiche le formulaire d'inscription des courtiers (GET)
     */
    public function afficher_formulaire()
    {
        $sections = $this->obtenir_sections('formulaire-courtiers'); 
        
        $donnees = [
            'titre'   => 'Devenir Partenaire Courtier',
            'hero'    => $sections['hero'] ?? null,
            'textes'  => $sections['textes'] ?? [],
            'page'    => $sections['page'] ?? null,
            'pays'    => $this->Modele->lire('pays', [], 'pays', 'ASC'),
            'langue'  => $this->input->get('langue') ?? 'fr'
        ];
        
        $this->load->view('Brokers_View', $donnees);
    }

    /**
     * Point d'accès API pour enregistrer les données (POST)
     */
    public function enregistrer() {
        // Récupérer les données
        $donnees_entree = $this->_obtenir_donnees_entree();
        
        if (empty($donnees_entree)) {
            $this->_reponse_json(false, 'Aucune donnée reçue');
            return;
        }

        // Validation
        $erreurs = $this->_valider_donnees_courtier($donnees_entree);
        
        if (!empty($erreurs)) {
            $this->_reponse_json(false, 'Erreur de validation', ['erreurs' => $erreurs]);
            return;
        }

        // Préparation et insertion
        $donnees_insertion = $this->_preparer_donnees_courtier($donnees_entree);
        
        $insere = $this->db->insert('brokers', $donnees_insertion);
        
        if (!$insere) {
            $erreur_bd = $this->db->error();
            log_message('error', 'Erreur insertion courtier : ' . print_r($erreur_bd, true));
            $this->_reponse_json(false, 'Erreur base de données : ' . $erreur_bd['message']);
            return;
        }

        $id_insere = $this->db->insert_id();

        // Envoi des courriels (optionnel)
        $courriel_envoye = false;
        try {
            $courriel_envoye = $this->_envoyer_courriels_notification($donnees_insertion);
        } catch (Exception $e) {
            log_message('error', 'Exception courriel courtier : ' . $e->getMessage());
        }

        // Succès
        $this->_reponse_json(true, 'Votre inscription a été enregistrée avec succès', [
            'courriel_envoye' => $courriel_envoye,
            'id' => $id_insere
        ]);
    }

    /**
     * Fonction auxiliaire : Récupérer les données d'entrée (JSON ou POST)
     */
    private function _obtenir_donnees_entree() {
        $donnees_entree = [];
        $type_contenu = isset($_SERVER['TYPE_CONTENU']) ? strtolower($_SERVER['TYPE_CONTENU']) : '';
        
        if (strpos($type_contenu, 'application/json') !== false) {
            $entree_json = file_get_contents('php://input');
            $donnees_entree = json_decode($entree_json, true);
            
            log_message('debug', 'JSON courtier reçu : ' . $entree_json);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON invalide : ' . json_last_error_msg());
                return null;
            }
        } else {
            $donnees_entree = $this->input->post();
            log_message('debug', 'POST courtier reçu : ' . print_r($donnees_entree, true));
        }
        
        return $donnees_entree;
    }

    /**
     * Fonction auxiliaire : Envoyer une réponse JSON et terminer
     */
    private function _reponse_json($succes, $message, $supplementaire = []) {
        $reponse = array_merge([
            'succes' => $succes,
            'message' => $message
        ], $supplementaire);
        
        echo json_encode($reponse);
        exit();
    }

    /**
     * Validation des données courtier
     */
    private function _valider_donnees_courtier($donnees_entree) {
        $erreurs = [];
        
        // Nom complet
        if (empty($donnees_entree['nom_complet'])) {
            $erreurs['nom_complet'] = 'Le nom complet est requis';
        } elseif (strlen($donnees_entree['nom_complet']) > 150) {
            $erreurs['nom_complet'] = 'Le nom ne doit pas dépasser 150 caractères';
        }

        // Nom de la société
        if (empty($donnees_entree['nom_societe'])) {
            $erreurs['nom_societe'] = 'Le nom de la société est requis';
        } elseif (strlen($donnees_entree['nom_societe']) > 200) {
            $erreurs['nom_societe'] = 'Le nom ne doit pas dépasser 200 caractères';
        }

        // Courriel
        if (empty($donnees_entree['courriel'])) {
            $erreurs['courriel'] = 'Le courriel est requis';
        } elseif (!filter_var($donnees_entree['courriel'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['courriel'] = 'Format de courriel invalide';
        } elseif (strlen($donnees_entree['courriel']) > 150) {
            $erreurs['courriel'] = 'Le courriel ne doit pas dépasser 150 caractères';
        } else {
            $this->db->where('email', $donnees_entree['courriel']);
            if ($this->db->count_all_results('brokers') > 0) {
                $erreurs['courriel'] = 'Ce courriel est déjà enregistré';
            }
        }

        // Pays
        if (empty($donnees_entree['id_pays'])) {
            $erreurs['id_pays'] = 'Le pays est requis';
        } else {
            $this->db->where('id', $donnees_entree['id_pays']);
            if ($this->db->count_all_results('pays') === 0) {
                $erreurs['id_pays'] = 'Pays invalide';
            }
        }

        // Capacité (au moins une)
        $champs_capacite = [
            'capacite_courtier_investissement', 'capacite_agent_placement',
            'capacite_conseiller_finances_entreprise', 'capacite_gestionnaire_fonds',
            'capacite_representant_family_office', 'capacite_conseiller_esg',
            'capacite_introducteur_independant'
        ];
        
        $a_capacite = false;
        foreach ($champs_capacite as $champ) {
            if (!empty($donnees_entree[$champ]) && $donnees_entree[$champ] == 1) {
                $a_capacite = true;
                break;
            }
        }
        
        $capacite_autre_remplie = !empty($donnees_entree['capacite_autre']) && trim($donnees_entree['capacite_autre']) !== '';
        
        if (!$a_capacite && !$capacite_autre_remplie) {
            $erreurs['capacite'] = 'Veuillez sélectionner au moins une capacité ou préciser "Autre"';
        }

        // Conformité (toutes requises)
        $champs_conformite = [
            'confirme_autorise' => 'Vous devez confirmer être autorisé à représenter votre entreprise',
            'confirme_aml_kyc' => 'Vous devez confirmer la conformité AML/KYC',
            'reconnait_non_exclusivite' => 'Vous devez reconnaître le caractère non exclusif',
            'comprend_mandat_formel_requis' => 'Vous devez comprendre qu\'un mandat formel est requis'
        ];
        
        foreach ($champs_conformite as $champ => $message) {
            if (empty($donnees_entree[$champ]) || $donnees_entree[$champ] != 1) {
                $erreurs[$champ] = $message;
            }
        }

        return $erreurs;
    }

    /**
     * Préparation des données pour insertion
     */
    private function _preparer_donnees_courtier($donnees_entree) {
        return [
            'full_name' => $donnees_entree['nom_complet'],
            'firm_name' => $donnees_entree['nom_societe'],
            'jurisdiction_of_incorporation' => $donnees_entree['juridiction_incorporation'] ?? null,
            'registration_number' => $donnees_entree['numero_immatriculation'] ?? null,
            'regulatory_status' => $donnees_entree['statut_reglementaire'] ?? null,
            'regulatory_authority' => $donnees_entree['autorite_reglementation'] ?? null,
            'id_pays' => $donnees_entree['id_pays'],
            'email' => $donnees_entree['courriel'],
            'mobile_phone' => $donnees_entree['telephone_mobile'] ?? null,
            'whatsapp' => $donnees_entree['whatsapp'] ?? null,
            'corporate_website' => $donnees_entree['site_web_societe'] ?? null,
            'capacity_investment_broker' => !empty($donnees_entree['capacite_courtier_investissement']) ? 1 : 0,
            'capacity_placement_agent' => !empty($donnees_entree['capacite_agent_placement']) ? 1 : 0,
            'capacity_corporate_finance_advisor' => !empty($donnees_entree['capacite_conseiller_finances_entreprise']) ? 1 : 0,
            'capacity_fund_manager' => !empty($donnees_entree['capacite_gestionnaire_fonds']) ? 1 : 0,
            'capacity_family_office_rep' => !empty($donnees_entree['capacite_representant_family_office']) ? 1 : 0,
            'capacity_esg_advisor' => !empty($donnees_entree['capacite_conseiller_esg']) ? 1 : 0,
            'capacity_independent_introducer' => !empty($donnees_entree['capacite_introducteur_independant']) ? 1 : 0,
            'capacity_other' => $donnees_entree['capacite_autre'] ?? null,
            'investor_private_equity' => !empty($donnees_entree['investisseur_capital_investissement']) ? 1 : 0,
            'investor_venture_capital' => !empty($donnees_entree['investisseur_capital_risque']) ? 1 : 0,
            'investor_esg_impact' => !empty($donnees_entree['investisseur_esg_impact']) ? 1 : 0,
            'investor_dfi' => !empty($donnees_entree['investisseur_financement_developpement']) ? 1 : 0,
            'investor_institutional' => !empty($donnees_entree['investisseur_institutionnel']) ? 1 : 0,
            'investor_hnwi' => !empty($donnees_entree['investisseur_grande_fortune']) ? 1 : 0,
            'investor_sovereign' => !empty($donnees_entree['investisseur_souverain']) ? 1 : 0,
            'typical_ticket_size' => $donnees_entree['taille_billet_typique'] ?? null,
            'geographic_coverage' => $donnees_entree['couverture_geographique'] ?? null,
            'mandate_equity' => !empty($donnees_entree['mandat_capitaux_propres']) ? 1 : 0,
            'mandate_structured_debt' => !empty($donnees_entree['mandat_dette_structuree']) ? 1 : 0,
            'mandate_blended_finance' => !empty($donnees_entree['mandat_financement_mixte']) ? 1 : 0,
            'mandate_grant' => !empty($donnees_entree['mandat_subvention']) ? 1 : 0,
            'mandate_strategic_partnership' => !empty($donnees_entree['mandat_partenariat_strategique']) ? 1 : 0,
            'mandate_full_program' => !empty($donnees_entree['mandat_programme_complet']) ? 1 : 0,
            'engagement_model' => $donnees_entree['modele_engagement'] ?? null,
            'confirm_authorized' => !empty($donnees_entree['confirme_autorise']) ? 1 : 0,
            'confirm_aml_kyc' => !empty($donnees_entree['confirme_aml_kyc']) ? 1 : 0,
            'acknowledge_no_exclusivity' => !empty($donnees_entree['reconnait_non_exclusivite']) ? 1 : 0,
            'understand_formal_mandate_required' => !empty($donnees_entree['comprend_mandat_formel_requis']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Envoi des courriels de notification
     */
    private function _envoyer_courriels_notification($donnees) {
        // Implémentez votre logique d'envoi de courriel ici
        // Pour l'instant, retourne vrai
        return true;
    }

    /**
     * Récupération des sections du CMS
     */
    private function obtenir_sections($alias = 'formulaire-courtiers') {
        $slug_map = [
            'formulaire-investisseurs' => 'investors-form',
            'formulaire-courtiers'     => 'brokers-form'
        ];
        $slug = $slug_map[$alias] ?? $alias;

        $page = static_pages_one([
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            log_message('debug', 'Page "' . $alias . '" non trouvée');
            return null;
        }

        $hero = static_sections_one([
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1,
            'deleted_at'   => null
        ]);

        if (!empty($hero) && !empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        $textes = static_sections_where([
            'id_page'      => $page['id_page'],
            'type_section' => 'texte',
            'est_active'   => 1,
            'deleted_at'   => null
        ], 'ordre', 'ASC');

        if (empty($textes)) {
            $textes = [];
        }

        foreach ($textes as &$texte) {
            $texte['options'] = !empty($texte['options_json']) 
                ? json_decode($texte['options_json'], true) 
                : [];
        }

        return [
            'page'   => $page,
            'hero'   => $hero,
            'textes' => $textes
        ];
    }
}