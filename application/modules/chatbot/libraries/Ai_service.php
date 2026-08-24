<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ai_service - AI logic: embeddings, RAG search, chat completion
 * Supports OpenAI (primary) and Groq (fallback) providers.
 * Reads configuration from the `configurations` table (admin panel).
 */
class Ai_service
{
    protected $CI;
    protected $api_key;
    protected $chat_model;
    protected $embedding_model;
    protected $groq_api_key;
    protected $groq_chat_model;
    protected $groq_base_url;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Model');
        $this->CI->load->model('chatbot/Kb_model');
        $this->CI->load->model('chatbot/Chatbot_model');
        $this->CI->load->library('chatbot/Language_detector');
        $this->CI->load->library('chatbot/MLService');

        // OpenAI config (primary)
        $this->api_key         = $this->CI->Model->get_setting('chatbot_api_key', '');
        $this->chat_model      = $this->CI->Model->get_setting('chatbot_chat_model', 'gpt-4o-mini');
        $this->embedding_model = $this->CI->Model->get_setting('chatbot_embedding_model', 'text-embedding-3-small');

        // Groq config (fallback)
        $this->groq_api_key    = $this->CI->Model->get_setting('chatbot_groq_api_key', '');
        $this->groq_chat_model = $this->CI->Model->get_setting('chatbot_groq_chat_model', 'groq/compound');
        $this->groq_base_url   = $this->CI->Model->get_setting('chatbot_groq_base_url', 'https://api.groq.com/openai/v1');
    }

    /* =================== LANGUAGE DETECTION =================== */

    protected function _detect_language($text)
    {
        // 1. Essayer le service ML Python (si disponible)
        $ml_result = $this->mlservice->predict($text);

        if ($ml_result['source'] === 'ml_service' && !empty($ml_result['language'])) {
            log_message('info', "[ML] Langue détectée: {$ml_result['language']} (confiance: {$ml_result['language_confidence']}, intention: {$ml_result['intent']})");
            return $ml_result['language'];
        }

        // 2. Fallback: utiliser le détecteur PHP hybride
        return $this->language_detector->get_language_code($text);
    }

    /**
     * Retourne le résultat complet de la détection ML (langue + intention)
     */
    protected function _detect_language_and_intent($text, $previous_language = null)
    {
        // 1. Essayer le service ML Python
        $ml_result = $this->mlservice->predict($text, $previous_language);

        if ($ml_result['source'] === 'ml_service' && !empty($ml_result['language'])) {
            return $ml_result;
        }

        // 2. Fallback PHP: langue seulement, intention inconnue
        $lang = $this->language_detector->get_language_code($text, $previous_language);
        $lang_info = $this->language_detector->detect_with_context($text, $previous_language);

        return array(
            'language'           => $lang,
            'language_confidence' => $lang_info['confidence'] ?? 0.50,
            'intent'             => null,
            'intent_confidence'  => 0,
            'low_confidence'     => ($lang_info['confidence'] ?? 0) < 0.60,
            'source'             => 'php_fallback',
        );
    }

    protected function _get_translation($key, $lang = 'fr')
    {
        $translations = array(
            'ask_name' => array(
                'fr' => 'Quel est votre nom complet ?',
                'rn' => 'Izina ryawe ryose ni irihe?',
                'en' => 'What is your full name?'
            ),
            'ask_whatsapp' => array(
                'fr' => 'Quel est votre numéro WhatsApp ?',
                'rn' => 'Nomero yawe ya WhatsApp ni iyihe?',
                'en' => 'What is your WhatsApp number?'
            ),
            'ask_email' => array(
                'fr' => 'Quelle est votre adresse email ?',
                'rn' => 'Email yawe ni iyihe?',
                'en' => 'What is your email address?'
            ),
            'ask_age' => array(
                'fr' => 'Quel âge avez-vous ?',
                'rn' => 'Ufise imyaka ingahe?',
                'en' => 'How old are you?'
            ),
            'ask_country' => array(
                'fr' => 'Dans quel pays résidez-vous ?',
                'rn' => 'Uba mu gihugu ikihe?',
                'en' => 'Which country do you live in?'
            ),
            'ask_weight' => array(
                'fr' => 'Quel est votre poids en kg ?',
                'rn' => 'Upima ibiro bingahe?',
                'en' => 'What is your weight in kg?'
            ),
            'ask_height' => array(
                'fr' => 'Quelle est votre taille en cm ?',
                'rn' => 'Ufise uburebure bungana gute?',
                'en' => 'What is your height in cm?'
            ),
            'ask_symptoms' => array(
                'fr' => 'Quels sont vos symptômes ou votre problème de santé ?',
                'rn' => "Ufise ibimenyetso canke ikibazo c'amagara ki?",
                'en' => 'What symptoms or health problem are you experiencing?'
            ),
            'ask_duration' => array(
                'fr' => 'Depuis combien de temps présentez-vous ces symptômes ?',
                'rn' => 'Ivyo bimenyetso bimaze igihe kingana gute?',
                'en' => 'How long have you had these symptoms?'
            ),
            'ask_consulted_doctor' => array(
                'fr' => 'Avez-vous déjà consulté un médecin pour ce problème ?',
                'rn' => 'Woba warigeze kwa muganga kubera iki kibazo?',
                'en' => 'Have you already consulted a doctor about this problem?'
            ),
            'ask_used_medication' => array(
                'fr' => 'Avez-vous déjà utilisé un médicament pour ce problème ? Si oui, lequel ?',
                'rn' => 'Woba warigeze gukoresha umuti kubera iki kibazo? Nimba ari vyo, ni uwuhe?',
                'en' => 'Have you already used any medication for this problem? If yes, which one?'
            ),
            'ask_medical_exams' => array(
                'fr' => 'Avez-vous déjà effectué des examens médicaux pour ce problème ? Si oui, lesquels ?',
                'rn' => "Woba warigeze gukorerwa ibipimo vy'ubuvuzi kubera iki kibazo? Nimba ari vyo, ni ibihe?",
                'en' => 'Have you already undergone any medical tests for this problem? If yes, which ones?'
            ),
            'ask_have_results' => array(
                'fr' => 'Avez-vous les résultats de ces examens ?',
                'rn' => 'Woba ufise ivyavuye muri ivyo bipimo?',
                'en' => 'Do you have the results of these medical tests?'
            ),
            'ask_upload_results' => array(
                'fr' => 'Si vous avez les résultats de ces examens, pouvez-vous les téléverser ici ?\n\n→ UPLOAD DES DOCUMENTS MÉDICAUX',
                'rn' => "Nimba ufise ivyavuye muri ivyo bipimo, woshobora kubishira hano?\n\n→ UPLOAD IBIPIMO VY'UBUVUZI",
                'en' => 'If you have the results, can you upload them here?\n\n→ UPLOAD MEDICAL DOCUMENTS'
            ),
            'ask_payment_method' => array(
                'fr' => 'Quel mode de paiement avez-vous utilisé pour payer les frais de consultation ?\n\n- Lumicash\n- Ecocash\n- Bancobu\n- Autre',
                'rn' => "Ni ubuhe buryo wakoresheje mu kuriha amafaranga ya consultation?\n\n- Lumicash\n- Ecocash\n- Bancobu\n- Autre",
                'en' => 'Which payment method did you use to pay the consultation fee?\n\n- Lumicash\n- Ecocash\n- Bancobu\n- Autre'
            ),
            'ask_payment_proof' => array(
                'fr' => "Pouvez-vous téléverser une photo ou une capture d'écran de votre preuve de paiement ?\n\n→ UPLOAD DE LA PREUVE DE PAIEMENT",
                'rn' => "Woshobora gushira hano ifoto canke screenshot yerekana ko wishyuye amafaranga ya consultation?\n\n→ UPLOAD EPREUVE DE PAIEMENT",
                'en' => 'Can you upload a photo or screenshot of your payment proof?\n\n→ UPLOAD PAYMENT PROOF'
            ),
            'confirm_asking' => array(
                'fr' => 'Après avoir vérifié que toutes les informations fournies sont correctes, répondez *Oui* pour soumettre votre demande ou *Non* pour corriger.',
                'rn' => 'Umaze kwemeza ko amakuru yose watanze ari ay\'ukuri, raba niba ari yo maze usubize *Yego* kugira urungike canke *Oya* kugira ukosore.',
                'en' => 'After confirming that all the information provided is correct, reply *Yes* to submit your request or *No* to correct.'
            ),
            'thanks_name' => array(
                'fr' => 'Merci {name} !',
                'rn' => 'Murakoze {name} !',
                'en' => 'Thank you {name}!'
            ),
            'perfect' => array(
                'fr' => 'Parfait !',
                'rn' => 'Neza !',
                'en' => 'Perfect!'
            ),
            'error_name' => array(
                'fr' => 'Le nom doit contenir au moins 2 caractères.',
                'rn' => 'Izina rirafise uburebure budahagije.',
                'en' => 'The name must contain at least 2 characters.'
            ),
            'error_phone' => array(
                'fr' => 'Veuillez entrer un numéro de téléphone valide.',
                'rn' => 'Nyamuneka injiza nomero ya WhatsApp yerekana.',
                'en' => 'Please enter a valid phone number.'
            ),
            'error_age' => array(
                'fr' => 'Veuillez entrer un nombre valide pour votre âge.',
                'rn' => 'Nyamuneka injiza imyaka yawe muibiharuro.',
                'en' => 'Please enter a valid number for your age.'
            ),
            'error_weight' => array(
                'fr' => 'Veuillez entrer votre poids en kg.',
                'rn' => 'Nyamuneka injiza ibiro vyawe.',
                'en' => 'Please enter your weight in kg.'
            ),
            'error_height' => array(
                'fr' => 'Veuillez entrer votre taille en cm.',
                'rn' => 'Nyamuneka injiza uburebure bwawe.',
                'en' => 'Please enter your height in cm.'
            ),
            'error_symptoms' => array(
                'fr' => 'Veuillez décrire vos symptômes ou votre problème.',
                'rn' => "Nyamuneka andika ibimenyetso vyawe canke ikibazo ufise.",
                'en' => 'Please describe your symptoms or problem.'
            ),
            'error_payment' => array(
                'fr' => 'Mode de paiement non reconnu. Choisissez parmi : Lumicash, Ecocash, Bancobu, Autre.',
                'rn' => "Uburyo bwo kuriha ntibwumvikanye. Hitamwo muri: Lumicash, Ecocash, Bancobu, Autre.",
                'en' => 'Payment method not recognized. Choose from: Lumicash, Ecocash, Bancobu, Autre.'
            ),
            'empathy_sick' => array(
                'fr' => "Je suis désolé d'apprendre que vous êtes malade. Je vais vous aider à être pris en charge rapidement.\n\nPour vous connecter avec un médecin, j'ai besoin de recueillir quelques informations.\n\nQuel est votre nom complet ?",
                'rn' => "Narakwiye kwihangana kubera urwayi rwawe. Ndaje kukugira intambwe mu gufasha umuganga.\n\nKugira ngo tuguhe umuganga, ndakeneye gutanga amakuru make.\n\nIzina ryawe ryose ni ryizahe?",
                'en' => "I'm sorry to hear you're sick. I'll help you get taken care of quickly.\n\nTo connect you with a doctor, I need to collect some information.\n\nWhat is your full name?"
            ),
            'start_consultation' => array(
                'fr' => "Je vais vous aider à prendre rendez-vous.\n\nQuel est votre nom complet ?",
                'rn' => "Ndaje kukugira intambwe mu gutega rendezvous.\n\nIzina ryawe ryose ni ryizahe?",
                'en' => "I'll help you make an appointment.\n\nWhat is your full name?"
            ),
            'confirm_yes' => array(
                'fr' => "Répondez *Oui* pour confirmer ou *Non* pour corriger.",
                'rn' => "Subiza *Yego* niba wemera cyangwa *Oya* niba ushaka gukosora.",
                'en' => "Reply *Yes* to confirm or *No* to correct."
            ),
            'correction_prompt' => array(
                'fr' => "Quel champ souhaitez-vous corriger ?",
                'rn' => "Ushaka gukosora iki kibaho?",
                'en' => "Which field do you want to correct?"
            ),
            'invalid_number' => array(
                'fr' => "Numéro invalide. Choisissez entre 1 et 9.",
                'rn' => "Nomero yanjye ntabwo ari yo. Hitamwo rimwe kugeza icumi.",
                'en' => "Invalid number. Choose between 1 and 9."
            ),
            'confirm_info' => array(
                'fr' => "Vérification des informations",
                'rn' => "Kugenzura amakuru",
                'en' => "Information verification"
            ),
            'patient_info' => array(
                'fr' => "Informations patient",
                'rn' => "Amakuru y'umurwayi",
                'en' => "Patient information"
            ),
            'consultation_details' => array(
                'fr' => "Détails de la consultation",
                'rn' => "Ibisobanuro vy'ubuvuzi",
                'en' => "Consultation details"
            ),
            'payment_info' => array(
                'fr' => "Frais de consultation",
                'rn' => "Amafaranga y'ubuvuzi",
                'en' => "Consultation fees"
            ),
            'consultation_saved' => array(
                'fr' => "DOSSIER PATIENT ENREGISTRE",
                'rn' => "AMAKURU Y'UMURWAYI YABIKWE",
                'en' => "PATIENT FILE SAVED"
            ),
            'send_documents' => array(
                'fr' => "Envoie vos documents médicaux si tu en as.",
                'rn' => "Ohereza ibice vyawe vy'ubuvuzi niba ufise.",
                'en' => "Send your medical documents if you have any."
            ),
            'ask_if_has_exams' => array(
                'fr' => "As-tu déjà effectué des examens médicaux ?\n\nRéponds *J'ai des examens* ou *Je n'ai pas d'examen*.",
                'rn' => "Waba warigeze gukorerwa ibipimo vy'ubuvuzi?\n\nSubiza *Ndi neza* cyangwa *Sindi neza*.",
                'en' => "Have you already had medical tests?\n\nReply *I have tests* or *I don't have tests*."
            ),
            'upload_documents' => array(
                'fr' => "Envoie tes documents médicaux (photos ou fichiers). Quand tu as terminé, réponds *Terminé*.",
                'rn' => "Ohereza ibice vyawe vy'ubuvuzi (amafoto canke dosiye). Ugera ho, subiza *Nararangiye*.",
                'en' => "Send your medical documents (photos or files). When done, reply *Done*."
            ),
            'upload_payment' => array(
                'fr' => "Envoie maintenant la preuve de paiement.",
                'rn' => "Ubu, ohereza ibyemezo vy'ubwishyu.",
                'en' => "Now send the payment proof."
            ),
            'processing' => array(
                'fr' => "Dossier complet. Votre demande est en cours de traitement.",
                'rn' => "Dosiye yarangiye. Ibisabwa biri mu gutunganywa.",
                'en' => "File complete. Your request is being processed."
            ),
            'error_name_short' => array(
                'fr' => "Le nom doit contenir au moins 2 caractères.",
                'rn' => "Izina rigomba kuba rirafise ibyiza bibiri.",
                'en' => "The name must contain at least 2 characters."
            ),
            'error_name_phone' => array(
                'fr' => "Ceci semble être un numéro de téléphone. Quel est votre nom complet ?",
                'rn' => "Ibi biraba nomero ya telefone. Izina ryawe ryose ni ryizahe?",
                'en' => "This looks like a phone number. What is your full name?"
            ),
            'error_height_range' => array(
                'fr' => "La taille doit être entre 50 et 250 cm.",
                'rn' => "Uburebure busabwa kuba hagati ya 50 na 250 cm.",
                'en' => "Height must be between 50 and 250 cm."
            ),
            'error_symptoms_desc' => array(
                'fr' => "Veuillez décrire vos symptômes en quelques mots.",
                'rn' => "Nyamuneka andika ibimenyetso vyawe mu magambo make.",
                'en' => "Please describe your symptoms in a few words."
            ),
            'error_symptoms_duration' => array(
                'fr' => "Ceci semble être une durée. Décrivez vos symptômes (douleurs, fièvre, etc.).",
                'rn' => "Ibi biraba igihe. Andika ibimenyetso vyawe (ububabare, umuriro, n'ibindi).",
                'en' => "This looks like a duration. Describe your symptoms (pain, fever, etc.)."
            ),
            'error_duration' => array(
                'fr' => "Depuis combien de temps avez-vous ces symptômes ?",
                'rn' => "Ivyo bimenyetso bimaze igihe kingana gute?",
                'en' => "How long have you had these symptoms?"
            ),
            'error_payment_method' => array(
                'fr' => "Mode de paiement non reconnu.\n\nChoisissez :\n1. Lumicash\n2. Ecocash\n3. Bancobu\n4. Western",
                'rn' => "Uburyo bwo kuriha ntibwumvikanye.\n\nHitamwo:\n1. Lumicash\n2. Ecocash\n3. Bancobu\n4. Western",
                'en' => "Payment method not recognized.\n\nChoose:\n1. Lumicash\n2. Ecocash\n3. Bancobu\n4. Western"
            ),
            'error_invalid_number' => array(
                'fr' => "Numéro invalide. Choisissez entre 1 et 9.",
                'rn' => "Nomero yanjye ntabwo ari yo. Hitamwo rimwe kugeza icumi.",
                'en' => "Invalid number. Choose between 1 and 9."
            ),
            'confirm_correct' => array(
                'fr' => "Ces informations sont-elles correctes ?\nRépondez *Oui* pour confirmer ou *Non* pour corriger.",
                'rn' => "Amakuru ari yo? Subiza *Yego* niba wemera cyangwa *Oya* niba ushaka gukosora.",
                'en' => "Is this information correct?\nReply *Yes* to confirm or *No* to correct."
            ),
            'correction_which' => array(
                'fr' => "Quel champ souhaitez-vous corriger ?",
                'rn' => "Ushaka gukosora iki kibaho?",
                'en' => "Which field do you want to correct?"
            ),
            'doc_no_exams' => array(
                'fr' => "Je n'ai pas d'examen",
                'rn' => "Sindi neza",
                'en' => "I don't have tests"
            ),
            'doc_has_exams' => array(
                'fr' => "J'ai des examens",
                'rn' => "Ndi neza",
                'en' => "I have tests"
            ),
            'doc_ask_exams' => array(
                'fr' => "As-tu déjà effectué des examens médicaux ?\n\nRéponds *J'ai des examens* ou *Je n'ai pas d'examen*.",
                'rn' => "Waba warigeze gukorerwa ibipimo vy'ubuvuzi?\n\nSubiza *Ndi neza* cyangwa *Sindi neza*.",
                'en' => "Have you already had medical tests?\n\nReply *I have tests* or *I don't have tests*."
            ),
            'doc_send_medical' => array(
                'fr' => "Parfait ! Envoyez vos documents médicaux (photos ou fichiers).\n\nVous pouvez envoyer plusieurs documents successivement.\n\nQuand vous avez terminé, répondez *Terminé*.",
                'rn' => "Neza! Ohereza ibice vyawe vy'ubuvuzi (amafoto canke dosiye).\n\nUshobora kohereza ibice vyinshi.\n\nUgera ho, subiza *Nararangiye*.",
                'en' => "Perfect! Send your medical documents (photos or files).\n\nYou can send multiple documents.\n\nWhen done, reply *Done*."
            ),
            'doc_no_medical' => array(
                'fr' => "Compris. Aucun document médical n'est nécessaire.\n\nIl vous reste uniquement à envoyer votre preuve de paiement.\n\n📌 *Envoyez une photo ou capture d'écran de votre preuve de paiement.*",
                'rn' => "Ndategeje. Nta bwigunge vy'ubuvuzi bisabwa.\n\nUbusa washira ho ibyemezo vy'ubwishyu.\n\n📌 *Ohereza ifoto canke screenshot y'ibyemezo vy'ubwishyu.*",
                'en' => "Understood. No medical document is needed.\n\nYou only need to send your payment proof.\n\n📌 *Send a photo or screenshot of your payment proof.*"
            ),
            'doc_send_more' => array(
                'fr' => "Document reçu.\n\nVous pouvez envoyer les autres résultats médicaux disponibles.\n\nQuand vous avez terminé, répondez *Terminé*.",
                'rn' => "Igice cyakiriye.\n\nUshobora kohereza ibindi bice vy'ubuvuzi.\n\nUgera ho, subiza *Nararangiye*.",
                'en' => "Document received.\n\nYou can send other medical results.\n\nWhen done, reply *Done*."
            ),
            'doc_done_medical' => array(
                'fr' => "Documents médicaux enregistrés.\n\n📌 *Envoyez maintenant votre preuve de paiement.*\n\nUne photo ou capture d'écran du reçu de paiement.",
                'rn' => "Ibice vy'ubuvuzi vyabikwe.\n\n📌 *Ubu, ohereza ibyemezo vy'ubwishyu.*\n\nIfoto canke screenshot y'ishyumuro.",
                'en' => "Medical documents saved.\n\n📌 *Now send your payment proof.*\n\nA photo or screenshot of the payment receipt."
            ),
            'doc_send_reminder' => array(
                'fr' => "Envoyez vos documents médicaux (photos ou fichiers).\n\nQuand vous avez terminé, répondez *Terminé*.",
                'rn' => "Ohereza ibice vyawe vy'ubuvuzi (amafoto canke dosiye).\n\nUgera ho, subiza *Nararangiye*.",
                'en' => "Send your medical documents (photos or files).\n\nWhen done, reply *Done*."
            ),
            'doc_payment_ask' => array(
                'fr' => "Merci. Veuillez envoyer la photo ou la capture d'écran de votre preuve de paiement afin que nous puissions vérifier le paiement.",
                'rn' => "Murakoze. Nyamuneka ohereza ifoto canke screenshot y'ibyemezo vy'ubwishyu kugira ngo tuyobore ubwishyu.",
                'en' => "Thank you. Please send the photo or screenshot of your payment proof so we can verify the payment."
            ),
            'doc_payment_reminder' => array(
                'fr' => "Envoyez une photo ou capture d'écran de votre preuve de paiement.",
                'rn' => "Ohereza ifoto canke screenshot y'ibyemezo vy'ubwishyu.",
                'en' => "Send a photo or screenshot of your payment proof."
            ),
            'doc_payment_received' => array(
                'fr' => "Preuve de paiement reçue.\n\nVotre dossier est maintenant en cours de vérification.\n\nMerci de patienter pendant que votre demande est transmise pour consultation.",
                'rn' => "Ibyemezo vy'ubwishyu vyakiriwe.\n\nDosiye yawe iri mu gukemurwa.\n\nNyamuneka utegereze.',
                'en' => "Payment proof received.\n\nYour file is now being verified.\n\nPlease wait while your request is processed."
            ),
            'doc_complete' => array(
                'fr' => "✅ *DOSSIER COMPLET*\n\n📋 *Résumé :*\nNom : {name}\nWhatsApp : {whatsapp}\nDocuments médicaux : {medical}\nPreuve de paiement : Reçue\n\nVotre dossier sera transmis au médecin pour évaluation.\nVous serez contacté via WhatsApp pour la suite.\n\n📞 *Contact :*\nWhatsApp : (+257) 79 666 439\nEmail : nufotecburundi2026@gmail.com",
                'rn' => "✅ *DOSIYE YARARANGIYE*\n\n📋 *Incamake:*\nIzina: {name}\nWhatsApp: {whatsapp}\nIbice vy'ubuvuzi: {medical}\nIbyemezo vy'ubwishyu: Vyakiriwe\n\nDosiye yawe izoherecwa ku muganga.\nUzoshobora kwifatanya na muganga wa WhatsApp.\n\n📞 *Ivugurura:*\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com",
                'en' => "✅ *FILE COMPLETE*\n\n📋 *Summary:*\nName: {name}\nWhatsApp: {whatsapp}\nMedical documents: {medical}\nPayment proof: Received\n\nYour file will be sent to the doctor for evaluation.\nYou will be contacted via WhatsApp.\n\n📞 *Contact:*\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com"
            ),
            'tech_error' => array(
                'fr' => "Désolé, je rencontre un problème technique. Souhaitez-vous être mis en relation avec un agent ?",
                'rn' => "Mfise ikibazo. Ushaka kuvugana n'umuntu?",
                'en' => "Sorry, I'm having a technical issue. Would you like to speak with an agent?"
            ),
            'escalation_reply' => array(
                'fr' => "Je vous mets en relation avec un membre de notre équipe. Un agent va prendre le relais dans quelques instants.",
                'rn' => "Ndi mu guhuza na umwe mu bushikizi bwacu. Umuntu azoshobora kukugira mu gihe gito.",
                'en' => "I'm connecting you with a member of our team. An agent will take over shortly."
            ),
            'consultation_start' => array(
                'fr' => "Je vais vous aider à prendre rendez-vous.\n\nQuel est votre nom complet ?",
                'rn' => "Ndaje kukugira intambwe mu gutega rendezvous.\n\nIzina ryawe ryose ni ryizahe?",
                'en' => "I'll help you make an appointment.\n\nWhat is your full name?"
            ),
            'price_info' => array(
                'fr' => "Pour les informations de prix, contactez-nous directement :\nWhatsApp : (+257) 79 666 439\nEmail : nufotecburundi2026@gmail.com\n\nLe médecin pourra vous donner le prix exact après évaluation de votre cas.",
                'rn' => "Kubera igiciro, tuvugishe:\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com\n\nU muganga azokwigisha igiciro nyacyo nyuma yo kugaragaza ikibazo.",
                'en' => "For pricing information, contact us directly:\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com\n\nThe doctor will give you the exact price after evaluating your case."
            ),
            'hours_info' => array(
                'fr' => "Nos services sont disponibles du Dimanche au Vendredi, de 8h00 à 17h00.",
                'rn' => "Ivyubukira vyacu biraboneka ku wa mbere kugeza ku wa gatandatu, kuva saa mbiri zo mugoroba kugeza saa icumi n'imwe zo mugoroba.",
                'en' => "Our services are available from Sunday to Friday, 8:00 AM to 5:00 PM."
            ),
            'contact_info' => array(
                'fr' => "Contactez-nous :\nWhatsApp : (+257) 79 666 439\nEmail : nufotecburundi2026@gmail.com",
                'rn' => "Tuavugishe:\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com",
                'en' => "Contact us:\nWhatsApp: (+257) 79 666 439\nEmail: nufotecburundi2026@gmail.com"
            ),
            'reformulate' => array(
                'fr' => "Je vais répondre à votre question. Pourriez-vous reformuler pour que je puisse mieux vous aider ?",
                'rn' => "Nzokwigira ikibazo. Ushobora guhindura ku buryo mbonye mfashe neza?",
                'en' => "I'll answer your question. Could you rephrase so I can help you better?"
            ),
            'consultation_after_question' => array(
                'fr' => "Maintenant, ",
                'rn' => "Ubu, ",
                'en' => "Now, "
            )
        );
        
        if (isset($translations[$key][$lang])) {
            return $translations[$key][$lang];
        }
        return isset($translations[$key]['fr']) ? $translations[$key]['fr'] : $key;
    }

    /* ===================== EMBEDDINGS ===================== */

    public function get_embedding($text)
    {
        $payload = array(
            'model' => $this->embedding_model,
            'input' => $text
        );

        $response = $this->_call_openai('embeddings', $payload);

        if ($response === false || !isset($response['data'][0]['embedding'])) {
            log_message('error', 'Ai_service::get_embedding failed');
            return false;
        }

        return $response['data'][0]['embedding'];
    }

    public function cosine_similarity(array $a, array $b)
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0;
        }

        $dot = 0; $normA = 0; $normB = 0;
        $n = count($a);
        for ($i = 0; $i < $n; $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0 || $normB == 0) return 0;

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /* =================== RAG SEARCH =================== */

    public function search_kb($question)
    {
        $query_embedding = $this->get_embedding($question);
        $top_n    = (int) $this->CI->Model->get_setting('chatbot_kb_top_n', 3);
        $min_score = (float) $this->CI->Model->get_setting('chatbot_kb_min_score', 0.72);

        $articles = $this->CI->Kb_model->get_active_with_embeddings();

        if ($query_embedding === false) {
            $fallback = $this->CI->Kb_model->fulltext_search($question, $top_n);
            return array('items' => $fallback, 'top_score' => !empty($fallback) ? 1 : 0, 'degraded' => true);
        }

        $scored = array();
        foreach ($articles as $article) {
            if (empty($article['embedding'])) continue;
            $vec = json_decode($article['embedding'], true);
            if (!is_array($vec)) continue;

            $score = $this->cosine_similarity($query_embedding, $vec);
            $scored[] = array(
                'id'       => $article['id'],
                'title'    => $article['title'],
                'content'  => $article['content'],
                'category' => $article['category'],
                'score'    => $score
            );
        }

        usort($scored, function ($a, $b) { return $b['score'] <=> $a['score']; });

        $top = array_slice($scored, 0, $top_n);
        $top_score = !empty($top) ? $top[0]['score'] : 0;

        $relevant = array_filter($top, function ($item) use ($min_score) {
            return $item['score'] >= $min_score;
        });

        return array('items' => array_values($relevant), 'top_score' => $top_score, 'degraded' => false);
    }

    /* =================== CHAT COMPLETION =================== */

    public function generate_reply($question, array $history = array(), $consultation_state = null)
    {
        if ($this->_matches_escalation_keywords($question)) {
            $user_lang = $this->_detect_language($question);
            return array(
                'reply'           => $this->_get_translation('escalation_reply', $user_lang),
                'should_escalate' => true,
                'sources'         => array(),
                'top_score'       => 0
            );
        }

        // If we are in consultation collection mode, handle it
        if ($consultation_state !== null) {
            $step = isset($consultation_state['step']) ? (int) $consultation_state['step'] : 0;
            
            // Handle document collection steps (13-16)
            if ($step >= 13 && $step <= 16) {
                return $this->_handle_document_step($question, $consultation_state);
            }

            // Check if user is asking a question (not answering the current step)
            if ($this->_is_user_asking_question($question)) {
                // Answer the question first, then remind the current step
                return $this->_handle_question_during_consultation($question, $consultation_state);
            }
            
            return $this->_handle_consultation_step($question, $consultation_state);
        }

        // Check if user asks about price (igurwa angahe, combien, etc.)
        if ($this->_matches_price_keywords($question)) {
            return $this->_handle_product_request();
        }

        // Check if user wants to see products
        if ($this->_matches_product_keywords($question)) {
            return $this->_handle_product_request();
        }

        // Check if user confirms consultation after seeing products
        $lower = mb_strtolower(trim($question));
        if (in_array($lower, array('oui', 'yes', 'ndego', 'yego', 'ok', 'daccord', 'd\'accord'))) {
            // Check if last bot message was about consultation or products
            $last_bot_msg = '';
            foreach (array_reverse($history) as $h) {
                if ($h['sender'] === 'bot') {
                    $last_bot_msg = $h['message'];
                    break;
                }
            }
            if (mb_strpos($last_bot_msg, 'consultation') !== false ||
                mb_strpos($last_bot_msg, 'Voulez-vous') !== false ||
                mb_strpos($last_bot_msg, 'INFORMATION IMPORTANTE') !== false ||
                mb_strpos($last_bot_msg, 'Ushaka') !== false) {
                $user_lang = $this->_detect_language($question);
                return array(
                    'reply'           => $this->_get_translation('consultation_start', $user_lang),
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'start_consultation' => true,
                    'consultation_step'  => 1
                );
            }
        }

        // Detect language early (used in multiple places below)
        $user_lang = $this->_detect_language($question);

        // Check if user wants to start a consultation
        if ($this->_matches_consultation_keywords($question)) {
            // Check if this is a first-time medical complaint (not already in flow)
            $last_bot_msg = '';
            foreach (array_reverse($history) as $h) {
                if ($h['sender'] === 'bot') {
                    $last_bot_msg = $h['message'];
                    break;
                }
            }
            
            // If bot hasn't already started the consultation, give empathetic intro
            if (mb_strpos($last_bot_msg, 'nom complet') === false && 
                mb_strpos($last_bot_msg, 'prendre rendez') === false &&
                mb_strpos($last_bot_msg, 'recueillir') === false) {
                return array(
                    'reply'           => $this->_get_translation('empathy_sick', $user_lang),
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'start_consultation' => true,
                    'consultation_step'  => 1
                );
            }
            
            return array(
                'reply'           => $this->_get_translation('start_consultation', $user_lang),
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'start_consultation' => true,
                'consultation_step'  => 1
            );
        }

        $kb = $this->search_kb($question);
        $context_text = '';
        foreach ($kb['items'] as $item) {
            $context_text .= "### {$item['title']}\n{$item['content']}\n\n";
        }

        $escalate_threshold = (float) $this->CI->Model->get_setting('chatbot_escalate_score', 0.55);
        $no_context_found = empty($kb['items']);

        $system_prompt = "Tu es l'assistant virtuel officiel de Nufotec Burundi. ".
            "Tu es un assistant medical bienveillant, empathique et intelligent. ".
            "Tu aides les patients a obtenir des soins medicaux de qualite.\n\n".

            "REGLES IMPORTANTES:\n".
            "1) QUAND UN UTILISATEUR DIT QU'IL EST MALADE: ".
            "Montre de l'empathie d'abord (ex: 'Je suis desole d'apprendre que vous etes malade.'). ".
            "Ensuite, explique brievement le processus: 'Pour etre pris en charge rapidement, je vais recueillir quelques informations afin de vous connecter avec un medecin.' ".
            "Puis demande les informations une par une.\n".

            "2) REPONDS AUX QUESTIONS: Si l'utilisateur pose une question (prix, horaires, contact, produits, etc.), reponds-y TOUJOURS d'abord, meme si tu es en plein flux de collecte de donnees. ".
            "Apres la reponse, reprends la collecte la ou elle en etait.\n".

            "3) NE JAMAIS: dire 'je ne peux pas vous aider', 'consultez un professionnel', ou refuser d'aider. Tu ES le service de consultation de Nufotec.\n".

            "4) SOIS NATUREL: Utilise un ton chaleureux et professionnel. Evite les reponses trop mecaniques.\n\n".

            "CAPACITES:\n".
            "- Repondre sur: horaires, services, contact, commandes, investissement, produits, prix\n".
            "- Collecter les informations patient pour une consultation medicale\n".
            "- Proposer un rendez-vous via WhatsApp\n\n".

            "STYLE: Francais clair, concis, bienveillant. Maximum 3-4 phrases par reponse sauf si l'utilisateur demande plus de details.";

        // Detect user language and force AI to respond in the same language
        // $user_lang is already detected above (before consultation check)
        $lang_names = array('fr' => 'francais', 'rn' => 'kirundi', 'en' => 'anglais');
        $lang_name = isset($lang_names[$user_lang]) ? $lang_names[$user_lang] : 'francais';
        
        $system_prompt .= "\n\nREGLE DE LANGUE CRITIQUE: L'utilisateur ecrit en {$lang_name}. ".
            "Tu DOIS repondre EXCLUSIVEMENT en {$lang_name}. ".
            "Ne JAMAIS melanger les langues. Toute ta reponse doit etre dans la meme langue que l'utilisateur.\n";

        $system_prompt .= "\n\nCONTEXTE:\n" . ($context_text !== '' ? $context_text : "(aucun article pertinent trouve)");

        $messages = array(array('role' => 'system', 'content' => $system_prompt));

        $recent_history = array_slice($history, -10);
        foreach ($recent_history as $h) {
            $role = ($h['sender'] === 'user') ? 'user' : 'assistant';
            $messages[] = array('role' => $role, 'content' => $h['message']);
        }
        $messages[] = array('role' => 'user', 'content' => $question);

        $payload = array(
            'model'       => $this->chat_model,
            'messages'    => $messages,
            'temperature' => 0.3,
            'max_tokens'  => 500
        );

        // Try OpenAI first, then Groq as fallback
        $response = $this->_call_openai('chat/completions', $payload);

        // If OpenAI fails, try Groq
        if ($response === false || !isset($response['choices'][0]['message']['content'])) {
            log_message('info', 'Ai_service: OpenAI failed, trying Groq fallback');
            $response = $this->_call_groq('chat/completions', $payload);
        }

        if ($response === false || !isset($response['choices'][0]['message']['content'])) {
            $user_lang = $this->_detect_language($question);
            return array(
                'reply' => $this->_get_translation('tech_error', $user_lang),
                'should_escalate' => true,
                'sources' => array(),
                'top_score' => 0
            );
        }

        $reply = trim($response['choices'][0]['message']['content']);

        // Clean up any thinking tags that might leak through
        $reply = $this->_clean_reply($reply);

        $should_escalate = $no_context_found && $kb['top_score'] < $escalate_threshold;

        return array(
            'reply'           => $reply,
            'should_escalate' => $should_escalate,
            'sources'         => array_map(function ($i) { return $i['title']; }, $kb['items']),
            'top_score'       => $kb['top_score']
        );
    }

    protected function _matches_escalation_keywords($text)
    {
        $keywords = array(
            'agent', 'humain', 'conseiller', 'parler a quelqu\'un',
            'service client', 'assistance humaine', 'appeler', 'appelez-moi'
        );
        $text_lower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (mb_strpos($text_lower, mb_strtolower($kw)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function _matches_consultation_keywords($text)
    {
        $keywords = array(
            // Francais
            'malade', 'souffre', 'douleur', 'medecin', 'consultation',
            'rendez-vous', 'rdv', 'consultation medicale', 'prendre rendez',
            'besoin d\'un medecin', 'voir un medecin', 'tumeur', 'fievre',
            'maux de tete', 'maux de ventre', 'probleme de sante',
            // Kirundi
            'ndarwaye', 'ndicumva', 'indwara', 'ugukicara', 'umuganga',
            'kwigicarwa', 'kunaniwa', 'uburwayi', 'guvuka', 'kunywa',
            'umutima', 'ubwo', 'impa', 'umudeni', 'isangano'
        );
        $text_lower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (mb_strpos($text_lower, mb_strtolower($kw)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function _matches_product_keywords($text)
    {
        $keywords = array(
            // Francais
            'produit', 'acheter', 'achat', 'medicament', 'medicaments',
            'produits', 'prix', 'commander', 'commande', 'stock',
            'disponible', 'liste des produits', 'catalogue', 'combien',
            'cout', 'tarif', 'payer', 'achete'
        );
        $text_lower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (mb_strpos($text_lower, mb_strtolower($kw)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function _matches_price_keywords($text)
    {
        $keywords = array(
            // Kirundi
            'igurwa', 'angahe', 'ibiciro', 'igiciro', 'kugura',
            'gutenga', 'kugurwa', 'ari angahe', 'niangahe',
            // Francais
            'combien', 'prix', 'cout', 'tarif', 'coute', 'paye'
        );
        $text_lower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (mb_strpos($text_lower, mb_strtolower($kw)) !== false) {
                return true;
            }
        }
        return false;
    }

    /* =================== QUESTION DETECTION DURING CONSULTATION =================== */

    protected function _is_user_asking_question($text)
    {
        $lower = mb_strtolower(trim($text));
        
        // Question patterns
        $question_patterns = array(
            'combien', 'prix', 'cout', 'tarif', 'coute', 'paye',
            'horaires', 'heure', 'ouverture', 'fermeture',
            'ou se trouve', 'adresse', 'localisation', 'situé',
            'comment faire', 'comment ca marche', 'comment proceder',
            'quels sont', 'quelle est', 'quel est',
            'est-ce que', 'est-ce possible', 'pouvez-vous',
            'vous avez', 'disponible', 'disponibilité',
            'igu', 'angahe', 'ibiciro', 'igiciro',
            'ni ikihe gihe', 'ni ryari', 'a hehe'
        );
        
        foreach ($question_patterns as $pattern) {
            if (mb_strpos($lower, $pattern) !== false) {
                return true;
            }
        }
        
        // Ends with ?
        if (substr($lower, -1) === '?') {
            return true;
        }
        
        return false;
    }

    protected function _handle_question_during_consultation($question, array $state)
    {
        $step = isset($state['step']) ? (int) $state['step'] : 0;
        $data = isset($state['data']) ? $state['data'] : array();
        $lower = mb_strtolower(trim($question));

        // Build the answer using KB search
        $kb = $this->search_kb($question);
        $answer = '';
        
        if (!empty($kb['items'])) {
            $top = $kb['items'][0];
            $answer = $top['content'];
        } else {
            $lang = $this->_detect_language($question);
            // Fallback answers for common questions
            if (mb_strpos($lower, 'prix') !== false || mb_strpos($lower, 'combien') !== false || 
                mb_strpos($lower, 'cout') !== false || mb_strpos($lower, 'tarif') !== false ||
                mb_strpos($lower, 'igu') !== false || mb_strpos($lower, 'angahe') !== false ||
                mb_strpos($lower, 'igiciro') !== false || mb_strpos($lower, 'ibiciro') !== false) {
                $answer = $this->_get_translation('price_info', $lang);
            } elseif (mb_strpos($lower, 'horaire') !== false || mb_strpos($lower, 'heure') !== false ||
                      mb_strpos($lower, 'ouverture') !== false || mb_strpos($lower, 'fermeture') !== false ||
                      mb_strpos($lower, 'gihe') !== false || mb_strpos($lower, 'saa') !== false) {
                $answer = $this->_get_translation('hours_info', $lang);
            } elseif (mb_strpos($lower, 'contact') !== false || mb_strpos($lower, 'telephone') !== false ||
                      mb_strpos($lower, 'whatsapp') !== false || mb_strpos($lower, 'email') !== false) {
                $answer = $this->_get_translation('contact_info', $lang);
            } else {
                $answer = $this->_get_translation('reformulate', $lang);
            }
        }

        // Now remind the current step
        $lang = $this->_detect_language($question);
        $steps_reminder = array(
            1 => $this->_get_translation('ask_name', $lang),
            2 => $this->_get_translation('ask_whatsapp', $lang),
            3 => $this->_get_translation('ask_email', $lang),
            4 => $this->_get_translation('ask_age', $lang),
            5 => $this->_get_translation('ask_country', $lang),
            6 => $this->_get_translation('ask_weight', $lang),
            7 => $this->_get_translation('ask_height', $lang),
            8 => $this->_get_translation('ask_symptoms', $lang),
            9 => $this->_get_translation('ask_duration', $lang),
            10 => $this->_get_translation('ask_consulted_doctor', $lang),
            11 => $this->_get_translation('ask_used_medication', $lang),
            12 => $this->_get_translation('ask_medical_exams', $lang),
            13 => $this->_get_translation('ask_payment_method', $lang)
        );

        $reminder = isset($steps_reminder[$step]) ? $steps_reminder[$step] : '';
        
        $reply = $answer . "\n\n---\n\n";
        $reply .= $this->_get_translation('consultation_after_question', $lang) . $reminder;

        return array(
            'reply'           => $reply,
            'should_escalate' => false,
            'sources'         => array_map(function ($i) { return $i['title']; }, $kb['items']),
            'top_score'       => $kb['top_score'],
            'consultation_step' => $step,
            'consultation_data' => $data
        );
    }

    protected function _handle_product_request()
    {
        $reply = "⚠️ *INFORMATION IMPORTANTE*\n\n";
        $reply .= "Pour vous proposer le bon produit, le medecin doit d'abord evaluer votre cas medical.\n\n";
        $reply .= "C'est lui qui determinera le produit adapte a votre situation.\n\n";
        $reply .= "📞 *NOUS CONTACTER* :\n";
        $reply .= "WhatsApp : (+257) 79 666 439\n";
        $reply .= "Email : nufotecburundi2026@gmail.com\n\n";
        $reply .= "Voulez-vous demarrer la consultation ?\n";
        $reply .= "Repondez *Oui* pour commencer.";

        return array(
            'reply'           => $reply,
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1
        );
    }

    protected function _handle_consultation_step($question, array $state)
    {
        $step = isset($state['step']) ? (int) $state['step'] : 0;
        $data = isset($state['data']) ? $state['data'] : array();
        $question = trim($question);

        // Step definitions with validation rules
        // Detect language from user message
        $lang = $this->_detect_language($question);
        
        $steps = array(
            1 => array(
                'field' => 'nom',
                'question' => $this->_get_translation('ask_name', $lang),
                'next' => 2,
                'validate' => 'validate_name'
            ),
            2 => array(
                'field' => 'whatsapp',
                'question' => $this->_get_translation('ask_whatsapp', $lang),
                'next' => 3,
                'validate' => 'validate_phone'
            ),
            3 => array(
                'field' => 'email',
                'question' => $this->_get_translation('ask_email', $lang),
                'next' => 4,
                'validate' => 'validate_text'
            ),
            4 => array(
                'field' => 'age',
                'question' => $this->_get_translation('ask_age', $lang),
                'next' => 5,
                'validate' => 'validate_age'
            ),
            5 => array(
                'field' => 'pays',
                'question' => $this->_get_translation('ask_country', $lang),
                'next' => 6,
                'validate' => 'validate_country'
            ),
            6 => array(
                'field' => 'poids',
                'question' => $this->_get_translation('ask_weight', $lang),
                'next' => 7,
                'validate' => 'validate_weight'
            ),
            7 => array(
                'field' => 'taille',
                'question' => $this->_get_translation('ask_height', $lang),
                'next' => 8,
                'validate' => 'validate_height'
            ),
            8 => array(
                'field' => 'symptomes',
                'question' => $this->_get_translation('ask_symptoms', $lang),
                'next' => 9,
                'validate' => 'validate_symptoms'
            ),
            9 => array(
                'field' => 'duree_symptomes',
                'question' => $this->_get_translation('ask_duration', $lang),
                'next' => 10,
                'validate' => 'validate_duration'
            ),
            10 => array(
                'field' => 'consulte_medecin',
                'question' => $this->_get_translation('ask_consulted_doctor', $lang),
                'next' => 11,
                'validate' => 'validate_text'
            ),
            11 => array(
                'field' => 'medicaments_utilises',
                'question' => $this->_get_translation('ask_used_medication', $lang),
                'next' => 12,
                'validate' => 'validate_text'
            ),
            12 => array(
                'field' => 'examens_medicaux',
                'question' => $this->_get_translation('ask_medical_exams', $lang),
                'next' => 13,
                'validate' => 'validate_text'
            ),
            13 => array(
                'field' => 'mode_paiement',
                'question' => $this->_get_translation('ask_payment_method', $lang),
                'next' => 14,
                'validate' => 'validate_payment'
            ),
            14 => array(
                'field' => 'confirmation',
                'question' => '',
                'next' => 15,
                'validate' => 'validate_confirmation'
            )
        );

        // Handle confirmation step (step 14)
        if ($step === 14) {
            return $this->_handle_confirmation($question, $data);
        }

        // Get current step config
        if (!isset($steps[$step])) {
            return $this->_error_response();
        }

        $step_config = $steps[$step];
        $field = $step_config['field'];
        $validation_method = $step_config['validate'];

        // Validate the answer
        $validation = $this->$validation_method($question, $data);

        if ($validation['valid'] === false) {
            // Return error message and stay on same step
            return array(
                'reply'           => $validation['error'] . "\n\n" . $step_config['question'],
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'consultation_step' => $step,
                'consultation_data' => array()
            );
        }

        // Check for out-of-context answers
        $context_check = $this->_check_out_of_context($question, $step, $data);
        if ($context_check !== null) {
            return $context_check;
        }

        // Save the validated data
        $save_data = array($field => $validation['value']);

        // If this is the last field (payment), show confirmation (step 13)
        if ($step === 13) {
            $data[$field] = $validation['value'];
            return $this->_show_confirmation($data);
        }

        // Move to next step
        $next_step = $step_config['next'];
        $next_question = $steps[$next_step]['question'];

        return array(
            'reply'           => $validation['success_msg'] . "\n\n" . $next_question,
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => $next_step,
            'consultation_data' => $save_data
        );
    }

    /* =================== VALIDATION METHODS =================== */

    protected function validate_name($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        if (strlen($value) < 2) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_name_short', $lang)
            );
        }
        // Check if it looks like a phone number
        if (preg_match('/^[0-9+\s()-]{6,}$/', $value)) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_name_phone', $lang)
            );
        }
        
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => $this->_get_translation('thanks_name', $lang)
        );
    }

    protected function validate_phone($value, $data)
    {
        $value = trim($value);
        // Remove common phone formatting
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
        
        // Check if it contains at least 6 digits
        if (!preg_match('/[0-9]{6,}/', $cleaned)) {
            $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_phone', $lang)
            );
        }
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => $this->_get_translation('perfect', $lang)
        );
    }

    protected function validate_age($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        // Check if it's a number
        if (!is_numeric($value)) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_age', $lang)
            );
        }
        $age = (int) $value;
        if ($age < 1 || $age > 120) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_age', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $age,
            'success_msg' => $this->_get_translation('perfect', $lang)
        );
    }

    protected function validate_country($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        if (strlen($value) < 2) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_name', $lang)
            );
        }
        // Check if it looks like a number (weight or height)
        if (is_numeric($value)) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_name', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => $this->_get_translation('perfect', $lang)
        );
    }

    protected function validate_weight($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        // Extract number from value (handle "65 kg" or "65")
        preg_match('/(\d+(?:\.\d+)?)/', $value, $matches);
        if (empty($matches[1])) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_weight', $lang)
            );
        }
        $weight = (float) $matches[1];
        if ($weight < 1 || $weight > 300) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_weight', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $weight,
            'success_msg' => $this->_get_translation('perfect', $lang) . "\n\n" . $this->_get_translation('ask_height', $lang)
        );
    }

    protected function validate_height($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        // Extract number from value (handle "160 cm" or "160")
        preg_match('/(\d+(?:\.\d+)?)/', $value, $matches);
        if (empty($matches[1])) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_height', $lang)
            );
        }
        $height = (float) $matches[1];
        if ($height < 50 || $height > 250) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_height_range', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $height,
            'success_msg' => $this->_get_translation('perfect', $lang) . "\n\n" . $this->_get_translation('ask_symptoms', $lang)
        );
    }

    protected function validate_symptoms($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        if (strlen($value) < 3) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_symptoms_desc', $lang)
            );
        }
        // Check if it looks like a duration
        if (preg_match('/^\d+\s*(jour|semaine|mois|an|annee)/i', $value)) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_symptoms_duration', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => ""
        );
    }

    protected function validate_duration($value, $data)
    {
        $value = trim($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        if (strlen($value) < 1) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_duration', $lang)
            );
        }
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => ""
        );
    }

    protected function validate_payment($value, $data)
    {
        $value = trim($value);
        $lower = mb_strtolower($value);
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        $valid_methods = array('lumicash', 'ecocash', 'bancobu', 'western', '1', '2', '3', '4');
        $method_map = array(
            '1' => 'Lumicash',
            '2' => 'Ecocash',
            '3' => 'Bancobu',
            '4' => 'Western',
            'lumicash' => 'Lumicash',
            'ecocash' => 'Ecocash',
            'bancobu' => 'Bancobu',
            'western' => 'Western'
        );

        if (!in_array($lower, $valid_methods)) {
            return array(
                'valid' => false,
                'error' => $this->_get_translation('error_payment_method', $lang)
            );
        }

        return array(
            'valid' => true,
            'value' => $method_map[$lower],
            'success_msg' => ""
        );
    }

    /* =================== OUT-OF-CONTEXT DETECTION =================== */

    protected function _check_out_of_context($question, $current_step, $data)
    {
        $lower = mb_strtolower($question);

        // If user provides name when asked for something else
        if ($current_step !== 1 && preg_match('/^[a-zA-ZÀ-ÿ\s]{3,}$/', $question)) {
            // Check if it might be a name
            if ($current_step === 2) {
                return array(
                    'reply'           => "Merci. " . $question . " semble être votre nom.\n\nQuel est votre numéro WhatsApp ?",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => $current_step,
                    'consultation_data' => array('nom' => $question)
                );
            }
        }

        // If user provides phone when asked for something else
        if ($current_step !== 2 && preg_match('/^[0-9+\s()-]{6,}$/', $question)) {
            if ($current_step === 3) {
                return array(
                    'reply'           => "Ce numéro semble être votre WhatsApp.\n\nQuel est votre âge ?",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => $current_step,
                    'consultation_data' => array('whatsapp' => $question)
                );
            }
        }

        return null;
    }

    /* =================== CONFIRMATION HANDLING =================== */

    protected function _show_confirmation($data)
    {
        $lang = isset($data['_lang']) ? $data['_lang'] : 'fr';
        $recap = "📋 *" . $this->_get_translation('confirm_info', $lang) . "*\n\n";
        $recap .= $this->_get_translation('patient_info', $lang) . " :\n";
        $recap .= "• Nom : " . $data['nom'] . "\n";
        $recap .= "• WhatsApp : " . $data['whatsapp'] . "\n";
        $recap .= "• Age : " . $data['age'] . " ans\n";
        $recap .= "• Pays : " . $data['pays'] . "\n";
        $recap .= "• Poids : " . $data['poids'] . " kg\n";
        $recap .= "• Taille : " . $data['taille'] . " cm\n";
        $recap .= "Symptomes : " . $data['symptomes'] . "\n";
        $recap .= "Duree : " . $data['duree_symptomes'] . "\n";
        $recap .= "Paiement : " . $data['mode_paiement'] . "\n\n";
        $recap .= $this->_get_translation('confirm_correct', $lang);

        return array(
            'reply'           => $recap,
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 14,
            'consultation_data' => $data
        );
    }

    protected function _handle_confirmation($question, $data)
    {
        $lower = mb_strtolower(trim($question));
        $lang = isset($data['_lang']) ? $data['_lang'] : 'fr';

        if (in_array($lower, array('oui', 'yes', 'yego', 'ndego', 'ok', 'daccord', "d'accord"))) {
            return $this->_generate_final_dossier($data);
        }

        if (in_array($lower, array('non', 'no', 'ndi', 'oya', 'correction'))) {
            $reply = $this->_get_translation('correction_which', $lang) . "\n\n";
            $reply .= "1. " . $this->_get_translation('ask_name', $lang) . "\n";
            $reply .= "2. " . $this->_get_translation('ask_whatsapp', $lang) . "\n";
            $reply .= "3. " . $this->_get_translation('ask_age', $lang) . "\n";
            $reply .= "4. " . $this->_get_translation('ask_country', $lang) . "\n";
            $reply .= "5. " . $this->_get_translation('ask_weight', $lang) . "\n";
            $reply .= "6. " . $this->_get_translation('ask_height', $lang) . "\n";
            $reply .= "7. " . $this->_get_translation('ask_symptoms', $lang) . "\n";
            $reply .= "8. " . $this->_get_translation('ask_duration', $lang) . "\n";
            $reply .= "9. " . $this->_get_translation('ask_payment_method', $lang);

            return array(
                'reply'           => $reply,
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'consultation_step' => 14,
                'consultation_data' => $data
            );
        }

        // If user enters a number (1-9), correct that field
        if (preg_match('/^[1-9]$/', $question)) {
            return $this->_handle_correction($question, $data);
        }

        return array(
            'reply'           => $this->_get_translation('confirm_yes', $lang),
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 14,
            'consultation_data' => $data
        );
    }

    protected function _handle_correction($question, $data)
    {
        $field_num = (int) $question;
        $lang = isset($data['_lang']) ? $data['_lang'] : 'fr';
        
        $field_map = array(
            1 => array('field' => 'nom', 'question' => $this->_get_translation('ask_name', $lang)),
            2 => array('field' => 'whatsapp', 'question' => $this->_get_translation('ask_whatsapp', $lang)),
            3 => array('field' => 'age', 'question' => $this->_get_translation('ask_age', $lang)),
            4 => array('field' => 'pays', 'question' => $this->_get_translation('ask_country', $lang)),
            5 => array('field' => 'poids', 'question' => $this->_get_translation('ask_weight', $lang)),
            6 => array('field' => 'taille', 'question' => $this->_get_translation('ask_height', $lang)),
            7 => array('field' => 'symptomes', 'question' => $this->_get_translation('ask_symptoms', $lang)),
            8 => array('field' => 'duree_symptomes', 'question' => $this->_get_translation('ask_duration', $lang)),
            9 => array('field' => 'mode_paiement', 'question' => $this->_get_translation('ask_payment_method', $lang))
        );

        if (!isset($field_map[$field_num])) {
            return array(
                'reply'           => $this->_get_translation('error_invalid_number', $lang),
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'consultation_step' => 14,
                'consultation_data' => $data
            );
        }

        $field_info = $field_map[$field_num];
        $step_map = array(
            'nom' => 1, 'whatsapp' => 2, 'age' => 3, 'pays' => 4,
            'poids' => 5, 'taille' => 6, 'symptomes' => 7,
            'duree_symptomes' => 8, 'mode_paiement' => 9
        );

        return array(
            'reply'           => $field_info['question'],
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => $step_map[$field_info['field']],
            'consultation_data' => $data
        );
    }

    protected function _generate_final_dossier($data)
    {
        $recap = "✅ *DOSSIER PATIENT ENREGISTRE*\n\n";
        $recap .= "👤 *INFORMATIONS PATIENT*\n";
        $recap .= "Nom : " . $data['nom'] . "\n";
        $recap .= "WhatsApp : " . $data['whatsapp'] . "\n";
        $recap .= "Age : " . $data['age'] . " ans\n";
        $recap .= "Pays : " . $data['pays'] . "\n";
        $recap .= "Poids : " . $data['poids'] . " kg\n";
        $recap .= "Taille : " . $data['taille'] . " cm\n\n";
        $recap .= "🩺 *SYMPTOMES*\n";
        $recap .= $data['symptomes'] . "\n";
        $recap .= "Duree : " . $data['duree_symptomes'] . "\n\n";
        $recap .= "💰 *PAIEMENT*\n";
        $recap .= "Mode : " . $data['mode_paiement'] . "\n";
        $recap .= "Frais consultation : 40.000 BIF\n\n";
        $recap .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $recap .= "📋 *DOCUMENTS NCESSAIRES*\n\n";
        $recap .= "Pour permettre au medecin de mieux evaluer votre situation, veuillez envoyer vos documents medicaux disponibles.\n\n";
        $recap .= "🩺 *Si vous avez deja effectue des examens medicaux :*\n\n";
        $recap .= "Veuillez envoyer les photos ou fichiers de TOUS vos resultats disponibles, par exemple :\n";
        $recap .= "- Analyses de laboratoire\n";
        $recap .= "- Resultats de prise de sang\n";
        $recap .= "- Echographie\n";
        $recap .= "- Radiographie\n";
        $recap .= "- Scanner\n";
        $recap .= "- IRM\n";
        $recap .= "- Ordonnances\n";
        $recap .= "- Comptes rendus medicaux\n";
        $recap .= "- Autres examens ou documents medicaux pertinents\n\n";
        $recap .= "📌 Envoyez des photos bien lisibles et completes.\n\n";
        $recap .= "❗ *Si vous n'avez effectue aucun examen medical :*\n\n";
        $recap .= "Vous n'avez aucun document medical a envoyer.\n\n";
        $recap .= "💳 *PREUVE DE PAIEMENT OBLIGATOIRE*\n\n";
        $recap .= "Dans tous les cas, vous devez obligatoirement envoyer une photo ou une capture d'ecran de votre preuve de paiement.\n\n";
        $recap .= "⚠️ La preuve de paiement est obligatoire pour poursuivre la procedure de consultation.\n\n";
        $recap .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $recap .= "📤 *A envoyer :*\n\n";
        $recap .= "1️⃣ Vos documents medicaux disponibles, si vous en avez.\n";
        $recap .= "2️⃣ La preuve de paiement.\n\n";
        $recap .= "Merci d'envoyer des documents lisibles.\n";
        $recap .= "Repondez *J'ai des examens* ou *Je n'ai pas d'examen* pour continuer.";

        $doctor_uuid = '00009520-0bc2-f36c-4c37-00000000921c';
        $whatsapp_number = '25779666439';
        $whatsapp_msg = $this->_build_whatsapp_message($data, $doctor_uuid);
        $whatsapp_link = "https://wa.me/{$whatsapp_number}?text=" . urlencode($whatsapp_msg);

        return array(
            'reply'           => $recap,
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 13,
            'consultation_data' => $data,
            'completed' => true,
            'whatsapp_link' => $whatsapp_link
        );
    }

    protected function _error_response()
    {
        return array(
            'reply'           => "Une erreur s'est produite. Commencons a nouveau.\n\nQuel est votre nom complet ?",
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 1,
            'consultation_data' => array()
        );
    }

    /* =================== DOCUMENT COLLECTION STEPS =================== */

    protected function _handle_document_step($question, array $state)
    {
        $step = isset($state['step']) ? (int) $state['step'] : 0;
        $data = isset($state['data']) ? $state['data'] : array();
        $lower = mb_strtolower(trim($question));

        switch ($step) {
            case 13: // Ask if patient has exams
                if (in_array($lower, array('oui', 'j\'ai des examens', 'j\'ai des examens', 'des examens', 'examen', 'examen medical'))) {
                    // Patient has exams
                    return array(
                        'reply'           => "Parfait ! Envoyez vos documents medicaux (photos ou fichiers).\n\nVous pouvez envoyer plusieurs documents successivement.\n\nQuand vous avez termine, repondez *Termine*.",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 14,
                        'consultation_data' => $data
                    );
                }
                
                if (in_array($lower, array('non', 'je n\'ai pas d\'examen', 'aucun', 'pas d\'examen', 'je n\'ai aucun resultat', 'pas d\'examens'))) {
                    // Patient has no exams
                    return array(
                        'reply'           => "Compris.\n\nAucun document medical n'est necessaire si vous n'avez effectue aucun examen.\n\nIl vous reste uniquement a envoyer votre preuve de paiement.\n\n📌 *Envoyez une photo ou capture d'ecran de votre preuve de paiement.*",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 15,
                        'consultation_data' => array_merge($data, array('has_medical_docs' => false))
                    );
                }
                
                // Invalid response
                return array(
                    'reply'           => "Repondez *J'ai des examens* ou *Je n'ai pas d'examen* pour continuer.",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => 13,
                    'consultation_data' => $data
                );

            case 14: // Collecting medical documents
                if (in_array($lower, array('termine', 'terminer', 'fini', 'c\'est tout', 'voila'))) {
                    // Move to payment proof
                    return array(
                        'reply'           => "Documents medicaux enregistres.\n\n📌 *Envoyez maintenant votre preuve de paiement.*\n\n une photo ou capture d'ecran du recu de paiement.",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 15,
                        'consultation_data' => array_merge($data, array('has_medical_docs' => true))
                    );
                }
                
                // Check if it's an image (file upload)
                if (isset($_FILES['document']) && !empty($_FILES['document']['name'])) {
                    return array(
                        'reply'           => "Document recu.\n\nVous pouvez envoyer les autres resultats medicaux disponibles.\n\nQuand vous avez termine, repondez *Termine*.",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 14,
                        'consultation_data' => $data,
                        'document_uploaded' => true,
                        'document_type' => 'medical'
                    );
                }
                
                // Text response that's not "termine"
                return array(
                    'reply'           => "Envoyez vos documents medicaux (photos ou fichiers).\n\nQuand vous avez termine, repondez *Termine*.",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => 14,
                    'consultation_data' => $data
                );

            case 15: // Collecting payment proof
                // Check if it's an image (file upload)
                if (isset($_FILES['document']) && !empty($_FILES['document']['name'])) {
                    return array(
                        'reply'           => "Preuve de paiement recue.\n\nVotre dossier est maintenant en cours de verification.\n\nMerci de patienter pendant que votre demande est transmise pour consultation.",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 16,
                        'consultation_data' => array_merge($data, array('has_payment_proof' => true)),
                        'document_uploaded' => true,
                        'document_type' => 'payment_proof'
                    );
                }
                
                // Text response that's not an image
                if (!preg_match('/^[0-9+\s()-]{6,}$/', $question) && !in_array($lower, array('photo', 'image', 'capture'))) {
                    return array(
                        'reply'           => "Merci. Veuillez envoyer la photo ou la capture d'ecran de votre preuve de paiement afin que nous puissions verifier le paiement.",
                        'should_escalate' => false,
                        'sources'         => array(),
                        'top_score'       => 1,
                        'consultation_step' => 15,
                        'consultation_data' => $data
                    );
                }
                
                return array(
                    'reply'           => "Envoyez une photo ou capture d'ecran de votre preuve de paiement.",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => 15,
                    'consultation_data' => $data
                );

            case 16: // Verification complete
                $recap = "✅ *DOSSIER COMPLET*\n\n";
                $recap .= "📋 *Resume :*\n";
                $recap .= "Nom : " . $data['nom'] . "\n";
                $recap .= "WhatsApp : " . $data['whatsapp'] . "\n";
                $recap .= "Documents medicaux : " . (isset($data['has_medical_docs']) && $data['has_medical_docs'] ? 'Envoyes' : 'Aucun') . "\n";
                $recap .= "Preuve de paiement : Recue\n\n";
                $recap .= "Votre dossier sera transmis au medecin pour evaluation.\n";
                $recap .= "Vous serez contacte via WhatsApp pour la suite.\n\n";
                $recap .= "📞 *Contact :*\n";
                $recap .= "WhatsApp : (+257) 79 666 439\n";
                $recap .= "Email : nufotecburundi2026@gmail.com";

                return array(
                    'reply'           => $recap,
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'consultation_step' => 17,
                    'consultation_data' => $data,
                    'completed' => true
                );

            default:
                return array(
                    'reply'           => "Comment puis-je vous aider ?",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1
                );
        }
    }

    protected function _build_whatsapp_message(array $data, $doctor_uuid)
    {
        $now = date('d/m/Y H:i');
        $numero = 'NUF-' . date('Ymd-His');
        $email = isset($data['email']) ? $data['email'] : 'Non renseigné';

        $msg = "*DEMANDE DE CONSULTATION - NUFOTEC*\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 *INFORMATIONS PATIENT*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "• Nom : " . $data['nom'] . "\n";
        $msg .= "• WhatsApp : " . $data['whatsapp'] . "\n";
        $msg .= "• Email : " . $email . "\n";
        $msg .= "• Date : " . $now . "\n\n";

        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🏥 *DÉTAILS DE LA CONSULTATION*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "• N° suivi : " . $numero . "\n";
        $msg .= "• Âge : " . $data['age'] . " ans\n";
        $msg .= "• Pays : " . $data['pays'] . "\n";
        $msg .= "• Poids : " . $data['poids'] . " kg | Taille : " . $data['taille'] . " cm\n\n";
        $msg .= "• Symptômes : " . $data['symptomes'] . "\n\n";
        $msg .= "• Durée des symptômes : " . $data['duree_symptomes'] . "\n\n";

        $msg .= "*Frais de consultation*\n";
        $msg .= "• Résidant du Burundi : 40.000 BIF\n";
        $msg .= "• Résidant à l'étranger : 50 USD/EUR (Équivalent en BIF : 325.000-375.000 BIF)\n\n";

        // Add medical documents if any
        if (!empty($data['medical_documents'])) {
            $msg .= "*Documents joints (disponibles en ligne) :*\n";
            foreach ($data['medical_documents'] as $doc) {
                $msg .= "• " . $doc . "\n";
            }
            $msg .= "\n";
        }

        $msg .= "*Mode de paiement :* " . $data['mode_paiement'] . "\n";

        // Add payment proof if provided
        if (!empty($data['payment_proof'])) {
            $msg .= "*Épreuve de paiement*\n";
            $msg .= $data['payment_proof'] . "\n";
        } else {
            $msg .= "*Épreuve de paiement :* En attente\n";
        }

        return $msg;
    }

    /**
     * Clean up AI reply - remove thinking tags and trim
     */
    protected function _clean_reply($text)
    {
        // Remove <think>...</think> blocks
        $text = preg_replace('/<think>.*?<\/think>/s', '', $text);
        // Remove any remaining think tags
        $text = preg_replace('/<\/?think>/s', '', $text);
        return trim($text);
    }

    /* =================== OPENAI API CALL =================== */

    protected function _call_openai($endpoint, array $payload)
    {
        if (empty($this->api_key)) {
            log_message('error', 'Ai_service: OpenAI API key not configured.');
            return false;
        }

        $ch = curl_init("https://api.openai.com/v1/{$endpoint}");
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ));

        $raw = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curl_error) {
            log_message('error', 'Ai_service OpenAI cURL error: ' . $curl_error);
            return false;
        }

        $decoded = json_decode($raw, true);

        if ($http_code >= 400) {
            log_message('error', 'Ai_service OpenAI error (' . $http_code . '): ' . $raw);
            return false;
        }

        return $decoded;
    }

    /* =================== GROQ API CALL (FALLBACK) =================== */

    protected function _call_groq($endpoint, array $payload)
    {
        if (empty($this->groq_api_key)) {
            log_message('error', 'Ai_service: Groq API key not configured.');
            return false;
        }

        // Use Groq model instead of OpenAI model
        $payload['model'] = $this->groq_chat_model;

        $base_url = rtrim($this->groq_base_url, '/');
        $ch = curl_init("{$base_url}/{$endpoint}");
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->groq_api_key
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ));

        $raw = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curl_error) {
            log_message('error', 'Ai_service Groq cURL error: ' . $curl_error);
            return false;
        }

        $decoded = json_decode($raw, true);

        if ($http_code >= 400) {
            log_message('error', 'Ai_service Groq error (' . $http_code . '): ' . $raw);
            return false;
        }

        log_message('info', 'Ai_service: Groq fallback successful');
        return $decoded;
    }
}
