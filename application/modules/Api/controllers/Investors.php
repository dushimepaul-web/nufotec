<?php
defined('BASEPATH') OR exit('Accès direct interdit');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 27/02/2026
 */

class Investisseurs extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
        
        // Charger le modèle si nécessaire
        if (!isset($this->Modele)) {
            $this->load->model('Modele');
        }
        
        // Configuration CORS pour toutes les réponses
        header('Accès-Control-Autoriser-Origine: *');
        header('Accès-Control-Autoriser-Méthodes: POST, GET, OPTIONS');
        header('Accès-Control-Autoriser-En-têtes: Type-Contenu, X-Demandé-Avec');
        
        // Gérer les requêtes OPTIONS (vérification préalable)
        if ($_SERVER['REQUEST_METHODE'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Méthode principale appelée par la route /{lang}/Investisseurs-formulaire
     * GET: Affiche le formulaire
     * POST: Traite la soumission
     */
    public function index()
    {
        // Si c'est une requête POST, traiter l'envoi
        if ($_SERVER['REQUEST_METHODE'] === 'POST') {
            $this->enregistrer();
            return;
        }
        
        // Sinon, afficher le formulaire (GET)
        $this->afficher_formulaire();
    }

    /**
     * Affiche le formulaire d'expression d'intérêt des investisseurs (GET)
     */
    public function afficher_formulaire()
    {   
        // Récupérer les sections hero et texte
        $sections = $this->obtenir_sections('formulaire-investisseurs'); 
        
        // Préparer les données pour la vue
        $donnees = [
            'titre'   => 'Devenir Investisseur Partenaire',
            'hero'    => $sections['hero'] ?? null,
            'textes'  => $sections['textes'] ?? [],
            'page'    => $sections['page'] ?? null,
            'pays'    => $this->Modele->lire('pays', [], 'pays', 'ASC'),
            'langue'  => $this->current_lang ?? 'fr'
        ];
        
        // Charger la vue avec le formulaire
        $this->load->view('Investors_View', $donnees);
    }

    /**
     * Point d'accès API pour enregistrer les données (POST)
     */
    public function enregistrer() {
        // Définir l'en-tête JSON
        header('Type-Contenu: application/json');

        // Récupérer les données (JSON ou POST)
        $donnees_entree = $this->_obtenir_donnees_entree();
        
        log_message('debug', '=== DÉBUT ENREGISTREMENT INVESTISSEUR ===');
        log_message('debug', 'Données entrée : ' . print_r($donnees_entree, true));

        // Vérifier si des données ont été reçues
        if (empty($donnees_entree)) {
            $this->_reponse_json(false, 'Aucune donnée reçue');
            return;
        }

        // ========== VALIDATION ==========
        $erreurs = $this->_valider_donnees($donnees_entree);
        
        if (!empty($erreurs)) {
            $this->_reponse_json(false, 'Erreur de validation', ['erreurs' => $erreurs]);
            return;
        }

        // ========== PRÉPARATION DES DONNÉES ==========
        $donnees_insertion = $this->_preparer_donnees_insertion($donnees_entree);
        
        log_message('debug', 'Données préparées : ' . print_r($donnees_insertion, true));

        // ========== INSERTION ==========
        $insere = $this->db->insert('investisseurs', $donnees_insertion);
        
        if (!$insere) {
            $erreur_bd = $this->db->error();
            log_message('error', 'Erreur BD : ' . print_r($erreur_bd, true));
            $this->_reponse_json(false, 'Erreur base de données : ' . $erreur_bd['message']);
            return;
        }

        $id_insere = $this->db->insert_id();
        log_message('debug', 'Insertion OK, ID : ' . $id_insere);

        // ========== ENVOI DES COURRIELS ==========
        $courriel_envoye = false;
        try {
            $courriel_envoye = $this->_envoyer_courriels_notification($donnees_insertion, $id_insere);
        } catch (Exception $e) {
            log_message('error', 'Exception courriel : ' . $e->getMessage());
            // Continuer même si l'envoi échoue
        }

        // ========== SUCCÈS ==========
        log_message('debug', '=== FIN ENREGISTREMENT INVESTISSEUR SUCCÈS ===');
        
        $this->_reponse_json(true, 'Votre expression d\'intérêt a été enregistrée avec succès', [
            'courriel_envoye' => $courriel_envoye,
            'id' => $id_insere
        ]);
    }

    /**
     * Fonction auxiliaire pour récupérer les données d'entrée
     */
    private function _obtenir_donnees_entree() {
        $donnees_entree = [];
        $type_contenu = isset($_SERVER['TYPE_CONTENU']) ? strtolower($_SERVER['TYPE_CONTENU']) : '';
        
        if (strpos($type_contenu, 'application/json') !== false) {
            $entree_json = file_get_contents('php://input');
            $donnees_entree = json_decode($entree_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON invalide : ' . json_last_error_msg());
                return null;
            }
        } else {
            $donnees_entree = $this->input->post();
        }
        
        return $donnees_entree;
    }

    /**
     * Fonction auxiliaire pour envoyer une réponse JSON et arrêter l'exécution
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
     * Validation des données
     */
    private function _valider_donnees($donnees_entree) {
        $erreurs = [];
        
        // Nom complet
        if (empty($donnees_entree['nom_complet'])) {
            $erreurs['nom_complet'] = 'Le nom complet est requis';
        } elseif (strlen($donnees_entree['nom_complet']) > 150) {
            $erreurs['nom_complet'] = 'Le nom ne doit pas dépasser 150 caractères';
        }

        // Courriel
        if (empty($donnees_entree['courriel'])) {
            $erreurs['courriel'] = 'Le courriel est requis';
        } elseif (!filter_var($donnees_entree['courriel'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['courriel'] = 'Format de courriel invalide';
        } elseif (strlen($donnees_entree['courriel']) > 150) {
            $erreurs['courriel'] = 'Le courriel ne doit pas dépasser 150 caractères';
        } else {
            // Vérifier si le courriel existe déjà
            $this->db->where('courriel', $donnees_entree['courriel']);
            if ($this->db->count_all_results('investisseurs') > 0) {
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

        // Type d'intérêt (au moins un)
        $champs_interet = [
            'interet_capitaux_propres', 'interet_dette', 'interet_financement_mixte',
            'interet_subvention', 'interet_partenariat_strategique',
            'interet_collaboration_technique', 'interet_achat_distribution'
        ];
        
        $a_interet = false;
        foreach ($champs_interet as $champ) {
            if (!empty($donnees_entree[$champ]) && $donnees_entree[$champ] == 1) {
                $a_interet = true;
                break;
            }
        }
        
        $interet_autre_rempli = !empty($donnees_entree['interet_autre']) && trim($donnees_entree['interet_autre']) !== '';
        
        if (!$a_interet && !$interet_autre_rempli) {
            $erreurs['interet'] = 'Veuillez sélectionner au moins un type d\'intérêt ou préciser "Autre"';
        }

        // Conformité
        if (empty($donnees_entree['accepte_etre_contacte']) || $donnees_entree['accepte_etre_contacte'] != 1) {
            $erreurs['accepte_etre_contacte'] = 'Vous devez accepter d\'être contacté';
        }
        
        if (empty($donnees_entree['confirmation_non_contraignante']) || $donnees_entree['confirmation_non_contraignante'] != 1) {
            $erreurs['confirmation_non_contraignante'] = 'Vous devez confirmer que cette expression d\'intérêt est non engageante';
        }

        return $erreurs;
    }

    /**
     * Préparation des données pour insertion
     */
    private function _preparer_donnees_insertion($donnees_entree) {
        return [
            'nom_complet' => $donnees_entree['nom_complet'],
            'organisation' => $donnees_entree['organisation'] ?? null,
            'titre_poste' => $donnees_entree['titre_poste'] ?? null,
            'id_pays' => $donnees_entree['id_pays'],
            'courriel' => $donnees_entree['courriel'],
            'telephone' => $donnees_entree['telephone'] ?? null,

            'interet_capitaux_propres' => !empty($donnees_entree['interet_capitaux_propres']) ? 1 : 0,
            'interet_dette' => !empty($donnees_entree['interet_dette']) ? 1 : 0,
            'interet_financement_mixte' => !empty($donnees_entree['interet_financement_mixte']) ? 1 : 0,
            'interet_subvention' => !empty($donnees_entree['interet_subvention']) ? 1 : 0,
            'interet_partenariat_strategique' => !empty($donnees_entree['interet_partenariat_strategique']) ? 1 : 0,
            'interet_collaboration_technique' => !empty($donnees_entree['interet_collaboration_technique']) ? 1 : 0,
            'interet_achat_distribution' => !empty($donnees_entree['interet_achat_distribution']) ? 1 : 0,
            'interet_autre' => $donnees_entree['interet_autre'] ?? null,

            'fourchette_engagement' => $donnees_entree['fourchette_engagement'] ?? null,

            'focus_laboratoire_recherche' => !empty($donnees_entree['focus_laboratoire_recherche']) ? 1 : 0,
            'focus_installation_gmp' => !empty($donnees_entree['focus_installation_gmp']) ? 1 : 0,
            'focus_plante_medicinale' => !empty($donnees_entree['focus_plante_medicinale']) ? 1 : 0,
            'focus_commercialisation' => !empty($donnees_entree['focus_commercialisation']) ? 1 : 0,
            'focus_plateforme_complete' => !empty($donnees_entree['focus_plateforme_complete']) ? 1 : 0,

            'calendrier' => $donnees_entree['calendrier'] ?? 'Exploratoire',
            'message_strategique' => $donnees_entree['message_strategique'] ?? null,

            'accepte_etre_contacte' => !empty($donnees_entree['accepte_etre_contacte']) ? 1 : 0,
            'confirmation_non_contraignante' => !empty($donnees_entree['confirmation_non_contraignante']) ? 1 : 0,
            
            'cree_le' => date('Y-m-d H:i:s'),
            'modifie_le' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Méthode de test simple
     */
    public function test() {
        header('Type-Contenu: application/json');
        echo json_encode([
            'succes' => true,
            'message' => 'API Investisseurs fonctionne',
            'heure' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Envoi des courriels de notification à l'administrateur et à l'investisseur avec SendGrid
     */
    private function _envoyer_courriels_notification($donnees, $id_insere)
    {
        try {
            // Charger SendGrid
            $this->load->library('Sendgrid_lib');

            // Récupérer les paramètres
            $nom_site   = $this->Modele->obtenir_parametre('site_name', 'AGF Phytomed');
            $courriel_admin = $this->Modele->obtenir_parametre('admin_email', 'partnerships@agf-phytomed.com');
            $whatsapp    = $this->Modele->obtenir_parametre('site_phone', '68863945');

            // Récupérer le nom du pays
            $nom_pays = 'Inconnu';
            if (!empty($donnees['id_pays'])) {
                $pays = $this->db->get_where('pays', ['id' => $donnees['id_pays']])->row();
                $nom_pays = $pays ? ($pays->pays ?? $pays->name ?? 'Inconnu') : 'Inconnu';
            }

            // Fonction auxiliaire pour afficher les booléens
            $formater_bool = function($valeur) {
                return $valeur ? 'Oui' : 'Non';
            };

            // Préparer la liste des types d'intérêt
            $carte_interets = [
                'interet_capitaux_propres'     => 'Capitaux Propres',
                'interet_dette'                 => 'Dette',
                'interet_financement_mixte'     => 'Financement Mixte',
                'interet_subvention'            => 'Subvention',
                'interet_partenariat_strategique' => 'Partenariat Stratégique',
                'interet_collaboration_technique' => 'Collaboration Technique',
                'interet_achat_distribution'    => 'Achat/Distribution'
            ];
            $interets = [];
            foreach ($carte_interets as $champ => $libelle) {
                if (!empty($donnees[$champ])) $interets[] = $libelle;
            }
            if (!empty($donnees['interet_autre'])) {
                $interets[] = 'Autre : ' . $donnees['interet_autre'];
            }
            $liste_interets = implode(', ', $interets) ?: 'Aucun';

            // Préparer la liste des domaines d'attention
            $carte_focus = [
                'focus_laboratoire_recherche'  => 'Laboratoire de Recherche',
                'focus_installation_gmp'        => 'Installation GMP',
                'focus_plante_medicinale'       => 'Plante Médicinale',
                'focus_commercialisation'       => 'Commercialisation',
                'focus_plateforme_complete'     => 'Plateforme Complète'
            ];
            $domaines_focus = [];
            foreach ($carte_focus as $champ => $libelle) {
                if (!empty($donnees[$champ])) $domaines_focus[] = $libelle;
            }
            $liste_focus = implode(', ', $domaines_focus) ?: 'Aucun';

            // ========== 1. COURRIEL À L'ADMINISTRATEUR ==========
            $sujet_admin = 'Nouvelle expression d\'intérêt d\'investisseur #' . $id_insere;
            $message_admin = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
                <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                    <div style='background: #0B4F2E; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin:0;'>Nouvelle candidature d'investisseur #{$id_insere}</h2>
                    </div>
                    <div style='padding: 25px;'>
                        <p><strong>Soumis le :</strong> " . date('Y-m-d H:i') . "</p>
                        <p><strong>Nom :</strong> {$donnees['nom_complet']}<br>
                        <strong>Poste :</strong> " . ($donnees['titre_poste'] ?? 'Non spécifié') . "<br>
                        <strong>Organisation :</strong> " . ($donnees['organisation'] ?? 'Non spécifié') . "<br>
                        <strong>Pays :</strong> $nom_pays<br>
                        <strong>Courriel :</strong> {$donnees['courriel']}<br>
                        <strong>Téléphone :</strong> " . ($donnees['telephone'] ?? 'Non spécifié') . "</p>
                        
                        <p><strong>Types d'intérêt :</strong> $liste_interets</p>
                        <p><strong>Domaines d'attention :</strong> $liste_focus</p>
                        <p><strong>Fourchette d'engagement :</strong> " . ($donnees['fourchette_engagement'] ?? 'Non spécifié') . "<br>
                        <strong>Calendrier :</strong> " . ($donnees['calendrier'] ?? 'Non spécifié') . "</p>
                        
                        <p><strong>Message stratégique :</strong> " . nl2br($donnees['message_strategique'] ?? 'Aucun') . "</p>
                        
                        <p><strong>Conformité :</strong><br>
                        Accepte d'être contacté : {$formater_bool($donnees['accepte_etre_contacte'])}<br>
                        Confirmation non contraignante : {$formater_bool($donnees['confirmation_non_contraignante'])}</p>
                        
                        <p style='text-align: center; margin-top: 30px;'>
                            <a href='" . base_url('Eoi_partenaires') . "' style='background: #0B4F2E; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;'>Voir dans le tableau de bord</a>
                        </p>
                    </div>
                    <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px;'>
                        Notification automatique de $nom_site.
                    </div>
                </div>
            </body>
            </html>";

            $resultat_admin = $this->sendgrid_lib->send_email($courriel_admin, $sujet_admin, $message_admin);
            $admin_envoye = ($resultat_admin['status'] == 202 || $resultat_admin['status'] == 200);
            
            if (!$admin_envoye) {
                log_message('error', 'SendGrid - Courriel admin échoué : ' . json_encode($resultat_admin));
            }

            // ========== 2. COURRIEL DE BIENVENUE À L'INVESTISSEUR ==========
            $sujet_investisseur = 'Merci pour votre intérêt envers AGF Phytomed';
            $message_investisseur = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
                <div style='max-width: 500px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                    <div style='background: #0B4F2E; color: white; padding: 30px; text-align: center;'>
                        <h1 style='margin:0;'>Bonjour {$donnees['nom_complet']} !</h1>
                    </div>
                    <div style='padding: 30px;'>
                        <p>Nous vous remercions de votre approche auprès d'African Green Farmers LTD.</p>
                        <p>Ceci est un accusé de réception automatique confirmant que nous avons bien reçu votre expression d'intérêt (référence #{$id_insere}).</p>
                        <p>Veuillez recevoir notre retour sous <strong>deux (2) jours ouvrables</strong>.</p>
                        <p>Si vous n'avez pas de nos nouvelles dans ce délai, n'hésitez pas à nous appeler ou à nous envoyer un message via notre numéro WhatsApp <strong>$whatsapp</strong> pour une réponse plus rapide.</p>
                        <p>Cordialement,<br>
                        <strong>Responsable Relations Publiques</strong><br>
                        African Green Farmers LTD<br>
                        Muyinga, Burundi<br>
                        $whatsapp<br>
                        Courriel : <a href='mailto:partnerships@agf-phytomed.com'>partnerships@agf-phytomed.com</a></p>
                    </div>
                    <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px;'>
                        © " . date('Y') . " AGF Phytomed. Tous droits réservés.
                    </div>
                </div>
            </body>
            </html>";

            $resultat_investisseur = $this->sendgrid_lib->send_email($donnees['courriel'], $sujet_investisseur, $message_investisseur);
            $investisseur_envoye = ($resultat_investisseur['status'] == 202 || $resultat_investisseur['status'] == 200);
            
            if (!$investisseur_envoye) {
                log_message('error', 'SendGrid - Courriel investisseur échoué : ' . json_encode($resultat_investisseur));
            }

            $les_deux_envoyes = $admin_envoye && $investisseur_envoye;
            log_message('info', 'Courriels SendGrid envoyés : admin=' . ($admin_envoye ? 'OK' : 'ÉCHEC') . ', investisseur=' . ($investisseur_envoye ? 'OK' : 'ÉCHEC'));
            
            return $les_deux_envoyes;

        } catch (Exception $e) {
            log_message('error', 'Exception courriel SendGrid : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération des sections du CMS
     */
    private function obtenir_sections($alias = 'formulaire-investisseurs') {
        $page = $this->Modele->lireUn('pages', [
            'alias' => $alias,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            log_message('debug', 'Page "' . $alias . '" non trouvée');
            return null;
        }

        // Récupérer la section hero
        $hero = $this->Modele->lireUn('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1
        ]);

        if (!empty($hero) && !empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        // Récupérer les sections texte
        $textes = $this->Modele->lire('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'texte',
            'est_active'   => 1
        ], 'ordre', 'ASC');

        // S'assurer que $textes est toujours un tableau
        if (empty($textes)) {
            $textes = [];
        }

        // Analyser les options JSON
        foreach ($textes as &$texte) {
            if (!empty($texte['options_json'])) {
                $texte['options'] = json_decode($texte['options_json'], true);
            } else {
                $texte['options'] = [];
            }
        }

        return [
            'page'   => $page,
            'hero'   => $hero,
            'textes' => $textes
        ];
    }
}