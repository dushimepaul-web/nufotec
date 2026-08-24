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
        $lower = mb_strtolower(trim($text));
        
        // Kirundi keywords
        $kirundi_keywords = array(
            'ndarwaye', 'ndicumva', 'indwara', 'umuganga', 'uburwayi',
            'igurwa', 'angahe', 'ibiciro', 'igiciro', 'gutenga', 'umuti',
            'imiti', 'ndego', 'yego', 'ndi', 'oya', 'muraho', 'amakuru',
            'ndashaka', 'nshaka', 'ni', 'uri', 'ufise', 'uburebure',
            'ibiro', 'ubu', 'ubwoko', 'ubukwe', 'gukicara', 'kunaniwa',
            'guvuka', 'kunywa', 'umutima', 'ubwo', 'impa', 'umudeni',
            'isangano', 'intoki', 'urugendo', 'ibintu', 'gucurura',
            'tarie', 'sinde', 'sinshobora', 'ndashobora', 'mbega',
            'vyose', 'vyo', 'vyo', 'kandi', 'cane', 'buri', 'mbere',
            'nyuma', 'hejuru', 'hasi', 'kuri', 'ku', 'mu', 'iyo',
            'uwo', 'icyo', 'aryo', 'ubwo', 'naho', 'kubera', 'kuko',
            'noneko', 'ubwonko', 'umwimpu', 'isabukuru', 'urukundo'
        );
        
        // French keywords
        $french_keywords = array(
            'bonjour', 'salut', 'merci', 'oui', 'non', 's\'il',
            'voila', 'comment', 'pourquoi', 'quand', 'ou', 'quel',
            'quelle', 'combien', 'prix', 'medicament', 'medecin',
            'consultation', 'rendez-vous', 'maladie', 'douleur',
            'symptome', 'traitement', 'pharmacie', 'ordonnance',
            'examen', 'analyse', 'sang', 'radio', 'echographie',
            'payer', 'paiement', 'lumicash', 'western', 'argent',
            'habite', 'poids', 'taille', 'age', 'nom', 'prenom',
            'telephone', 'whatsapp', 'email', 'adresse', 'ville',
            'pays', 'burundi', 'bujumbura', 'gitega'
        );
        
        // English keywords
        $english_keywords = array(
            'hello', 'hi', 'yes', 'no', 'please', 'thank',
            'price', 'medicine', 'doctor', 'consultation',
            'appointment', 'disease', 'pain', 'symptom',
            'treatment', 'pharmacy', 'prescription', 'examination',
            'analysis', 'blood', 'xray', 'ultrasound', 'pay',
            'payment', 'money', 'live', 'weight', 'height',
            'age', 'name', 'phone', 'whatsapp', 'email',
            'address', 'city', 'country', 'burundi'
        );
        
        // Count matches for each language
        $kirundi_count = 0;
        $french_count = 0;
        $english_count = 0;
        
        foreach ($kirundi_keywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) $kirundi_count++;
        }
        
        foreach ($french_keywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) $french_count++;
        }
        
        foreach ($english_keywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) $english_count++;
        }
        
        // Return the language with most matches
        $max = max($kirundi_count, $french_count, $english_count);
        
        if ($max === 0) return 'fr'; // Default to French
        if ($kirundi_count === $max) return 'rn';
        if ($english_count === $max) return 'en';
        return 'fr';
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
            return array(
                'reply'           => "Je vous mets en relation avec un membre de notre equipe. Un agent va prendre le relais dans quelques instants.",
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
                mb_strpos($last_bot_msg, 'INFORMATION IMPORTANTE') !== false) {
                return array(
                    'reply'           => "Je vais vous aider a prendre rendez-vous.\n\nQuel est votre nom complet ?",
                    'should_escalate' => false,
                    'sources'         => array(),
                    'top_score'       => 1,
                    'start_consultation' => true,
                    'consultation_step'  => 1
                );
            }
        }

        // Check if user wants to start a consultation
        if ($this->_matches_consultation_keywords($question)) {
            return array(
                'reply'           => "Je vais vous aider a prendre rendez-vous.\n\nQuel est votre nom complet ?",
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
            "Tu aides les patients a obtenir des soins medicaux. ".
            "IMPORTANT: Quand un utilisateur dit qu'il est malade, souffre, ou a besoin d'un medecin, tu DOIS IMMEDIATEMENT: ".
            "1) Lui proposer de prendre rendez-vous via le formulaire de consultation. ".
            "2) Lui donner le lien direct: http://localhost/nufotec/patient-form?doctor_uuid=00009520-0bc2-f36c-4c37-00000000921c ".
            "3) Lui expliquer comment remplir le formulaire (nom, prenom, email, telephone, symptomes, date souhaitee). ".
            "4) L'encourager a completer tous les champs pour que le medecin puisse le recevoir. ".
            "Ne JAMAIS repondre 'je ne peux pas vous aider' ou 'consultez un professionnel'. Tu ES le service de consultation de Nufotec. ".
            "Tu peux aussi aider avec: horaires, services, contact, commandes, investissement. ".
            "Reponds toujours en francais, de facon claire et concise.";

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
            return array(
                'reply' => "Desole, je rencontre un probleme technique. Souhaitez-vous etre mis en relation avec un agent ?",
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
        if (strlen($value) < 2) {
            return array(
                'valid' => false,
                'error' => "Le nom doit contenir au moins 2 caractères."
            );
        }
        // Check if it looks like a phone number
        if (preg_match('/^[0-9+\s()-]{6,}$/', $value)) {
            return array(
                'valid' => false,
                'error' => "Ceci semble être un numéro de téléphone. Quel est votre nom complet ?"
            );
        }
        // Detect language from data or current message
        $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
        
        return array(
            'valid' => true,
            'value' => $value,
            'success_msg' => $this->_get_translation('thanks_name', $lang) . "\n\n" . $this->_get_translation('ask_whatsapp', $lang)
        );
    }

    protected function validate_phone($value, $data)
    {
        $value = trim($value);
        // Remove common phone formatting
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
        
        // Check if it contains at least 6 digits
        if (!preg_match('/[0-9]{6,}/', $cleaned)) {
            // Check if it looks like a name instead
            if (preg_match('/^[a-zA-ZÀ-ÿ\s]{2,}$/', $value)) {
                $lang = isset($data['_lang']) ? $data['_lang'] : $this->_detect_language($value);
                return array(
                    'valid' => false,
                    'error' => $this->_get_translation('error_phone', $lang)
                );
            }
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
            'success_msg' => $this->_get_translation('perfect', $lang) . "\n\n" . $this->_get_translation('ask_age', $lang)
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
            'success_msg' => $this->_get_translation('ask_country', $lang)
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
            'success_msg' => $this->_get_translation('ask_weight', $lang)
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
        $weight = (float) $matches[1];
        if ($weight < 1 || $weight > 300) {
            return array(
                'valid' => false,
                'error' => "Le poids doit être entre 1 et 300 kg."
            );
        }
        return array(
            'valid' => true,
            'value' => $weight,
            'success_msg' => ""
        );
    }

    protected function validate_height($value, $data)
    {
        $value = trim($value);
        // Extract number from value (handle "160 cm" or "160")
        preg_match('/(\d+(?:\.\d+)?)/', $value, $matches);
        if (empty($matches[1])) {
            return array(
                'valid' => false,
                'error' => "Veuillez entrer votre taille en cm (ex: 160)."
            );
        }
        $height = (float) $matches[1];
        if ($height < 50 || $height > 250) {
            return array(
                'valid' => false,
                'error' => "La taille doit être entre 50 et 250 cm."
            );
        }
        return array(
            'valid' => true,
            'value' => $height,
            'success_msg' => ""
        );
    }

    protected function validate_symptoms($value, $data)
    {
        $value = trim($value);
        if (strlen($value) < 3) {
            return array(
                'valid' => false,
                'error' => "Veuillez décrire vos symptômes en quelques mots."
            );
        }
        // Check if it looks like a duration
        if (preg_match('/^\d+\s*(jour|semaine|mois|an|annee)/i', $value)) {
            return array(
                'valid' => false,
                'error' => "Ceci semble être une durée. Décrivez vos symptômes (douleurs, fièvre, etc.)."
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
        if (strlen($value) < 1) {
            return array(
                'valid' => false,
                'error' => "Depuis combien de temps avez-vous ces symptômes ?"
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
        
        $valid_methods = array('lumicash', 'western', '1', '2');
        $method_map = array(
            '1' => 'Lumicash',
            '2' => 'Western',
            'lumicash' => 'Lumicash',
            'western' => 'Western'
        );

        if (!in_array($lower, $valid_methods)) {
            return array(
                'valid' => false,
                'error' => "Mode de paiement non reconnu.\n\nChoisissez :\n1. Lumicash\n2. Western"
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
        $recap = "📋 *VERIFICATION DES INFORMATIONS*\n\n";
        $recap .= "Nom : " . $data['nom'] . "\n";
        $recap .= "WhatsApp : " . $data['whatsapp'] . "\n";
        $recap .= "Age : " . $data['age'] . " ans\n";
        $recap .= "Pays : " . $data['pays'] . "\n";
        $recap .= "Poids : " . $data['poids'] . " kg\n";
        $recap .= "Taille : " . $data['taille'] . " cm\n";
        $recap .= "Symptomes : " . $data['symptomes'] . "\n";
        $recap .= "Duree : " . $data['duree_symptomes'] . "\n";
        $recap .= "Paiement : " . $data['mode_paiement'] . "\n\n";
        $recap .= " Ces informations sont-elles correctes ?\n";
        $recap .= "Repondez *Oui* pour confirmer ou *Non* pour corriger.";

        return array(
            'reply'           => $recap,
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 10,
            'consultation_data' => array()
        );
    }

    protected function _handle_confirmation($question, $data)
    {
        $lower = mb_strtolower(trim($question));

        if (in_array($lower, array('oui', 'yes', 'yego', 'ndego', 'ok', 'daccord', "d'accord"))) {
            return $this->_generate_final_dossier($data);
        }

        if (in_array($lower, array('non', 'no', 'ndi', 'oya', 'correction'))) {
            $reply = "Quel champ souhaitez-vous corriger ?\n\n";
            $reply .= "1. Nom\n";
            $reply .= "2. WhatsApp\n";
            $reply .= "3. Age\n";
            $reply .= "4. Pays\n";
            $reply .= "5. Poids\n";
            $reply .= "6. Taille\n";
            $reply .= "7. Symptomes\n";
            $reply .= "8. Duree\n";
            $reply .= "9. Paiement";

            return array(
                'reply'           => $reply,
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'consultation_step' => 11,
                'consultation_data' => $data
            );
        }

        // If user enters a number (1-9), correct that field
        if (preg_match('/^[1-9]$/', $question)) {
            return $this->_handle_correction($question, $data);
        }

        return array(
            'reply'           => "Repondez *Oui* pour confirmer ou *Non* pour corriger.",
            'should_escalate' => false,
            'sources'         => array(),
            'top_score'       => 1,
            'consultation_step' => 10,
            'consultation_data' => $data
        );
    }

    protected function _handle_correction($question, $data)
    {
        $field_num = (int) $question;
        $field_map = array(
            1 => array('field' => 'nom', 'question' => 'Quel est votre nom complet ?'),
            2 => array('field' => 'whatsapp', 'question' => 'Quel est votre numéro WhatsApp ?'),
            3 => array('field' => 'age', 'question' => 'Quel est votre âge ?'),
            4 => array('field' => 'pays', 'question' => 'Dans quel pays habitez-vous ?'),
            5 => array('field' => 'poids', 'question' => 'Quel est votre poids en kg ?'),
            6 => array('field' => 'taille', 'question' => 'Et votre taille en cm ?'),
            7 => array('field' => 'symptomes', 'question' => 'Décrivez vos symptômes.'),
            8 => array('field' => 'duree_symptomes', 'question' => 'Depuis combien de temps ?'),
            9 => array('field' => 'mode_paiement', 'question' => 'Mode de paiement :\n1. Lumicash\n2. Western')
        );

        if (!isset($field_map[$field_num])) {
            return array(
                'reply'           => "Numéro invalide. Choisissez entre 1 et 9.",
                'should_escalate' => false,
                'sources'         => array(),
                'top_score'       => 1,
                'consultation_step' => 11,
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
        $email = isset($data['email']) ? $data['email'] : 'Non renseigne';

        $msg = "*DEMANDE DE CONSULTATION - NUFOTEC*\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "*INFORMATIONS PATIENT*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "- Nom : " . $data['nom'] . "\n";
        $msg .= "- WhatsApp : " . $data['whatsapp'] . "\n";
        $msg .= "- Email : " . $email . "\n";
        $msg .= "- Date : " . $now . "\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "*DETAILS DE LA CONSULTATION*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "- N suivi : " . $numero . "\n";
        $msg .= "- Age : " . $data['age'] . " ans\n";
        $msg .= "- Pays : " . $data['pays'] . "\n";
        $msg .= "- Poids : " . $data['poids'] . " kg | Taille : " . $data['taille'] . " cm\n\n";
        $msg .= "- Symptomes : " . $data['symptomes'] . "\n\n";
        $msg .= "- Duree des symptomes : " . $data['duree_symptomes'] . "\n\n";
        $msg .= "*Frais de consultation*\n";
        $msg .= "- Resident du Burundi : 40.000BIF\n";
        $msg .= "- Resident a l'etranger : 50 USD/EUR (Equivalent en BIF : 325.000-375.000 BIF)\n\n";

        // Add medical documents if any
        if (!empty($data['medical_documents'])) {
            $msg .= "*Documents medicaux :*\n";
            foreach ($data['medical_documents'] as $doc) {
                $msg .= "- " . $doc . "\n";
            }
            $msg .= "\n";
        } else {
            $msg .= "*Documents medicaux :* Aucun\n\n";
        }

        $msg .= "*Mode de paiement :* " . $data['mode_paiement'] . "\n";

        // Add payment proof if provided
        if (!empty($data['payment_proof'])) {
            $msg .= "*Epreuve de paiement*\n";
            $msg .= $data['payment_proof'] . "\n";
        } else {
            $msg .= "*Epreuve de paiement :* En attente\n";
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
            CURLOPT_SSL_VERIFYPEER => false
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
            CURLOPT_SSL_VERIFYPEER => false
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
